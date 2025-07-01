<?php
// ==========================
//    공통 검색 함수 모듈
// ==========================

// (1) 게시판 목록 캐시 반환 (폼용)
function get_board_list() {
    static $cached_boards = null;
    if ($cached_boards !== null) return $cached_boards;
    global $g5;
    $sql = "SELECT bo_table, bo_subject FROM {$g5['board_table']} ORDER BY bo_table";
    $result = sql_query($sql);
    $boards = [];
    while ($row = sql_fetch_array($result)) {
        $boards[$row['bo_table']] = $row['bo_subject'];
    }
    $cached_boards = $boards;
    return $boards;
}

// (2) 배열형 SQL IN 절 escape
function escape_list_for_sql($conn, $items) {
    return array_map(function($b) use ($conn) {
        return mysqli_real_escape_string($conn, $b);
    }, $items);
}

// (3) 키워드 하이라이트 마크업
function get_highlighted_text($text, $stx) {
    if (!$stx || !$text) return htmlspecialchars($text);
    return preg_replace_callback(
        "/" . preg_quote($stx, '/') . "/iu",
        function($m) { return "<mark>{$m[0]}</mark>"; },
        htmlspecialchars($text)
    );
}

// (4) AJAX 페이지네이션 생성
function render_pagination($total, $page, $rows, $jump, $base_url, $page_display_count = 10) {
    if ($rows <= 0) $rows = 10;
    $pages = max(1, ceil($total / $rows));
    $html = '<div class="pagination">';
    $current_block = (int)(($page - 1) / $page_display_count);
    $start = $current_block * $page_display_count + 1;
    $end = min($pages, ($current_block + 1) * $page_display_count);

    // 맨 앞 블록이면 « ‹를 아예 출력하지 않음
    if ($start > 1) {
        $html .= "<a href='#' class='page-link' data-page='1' title='처음'>&laquo;</a>";
        $prev_block = $start - $page_display_count;
        $html .= "<a href='#' class='page-link' data-page='{$prev_block}' title='이전'>&lsaquo;</a>";
    }

    // 페이지 숫자
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            $html .= "<span class='current'>{$i}</span>";
        } else {
            $html .= "<a href='#' class='page-link' data-page='{$i}'>{$i}</a>";
        }
    }

    // 맨 끝 블록이면 › »를 아예 출력하지 않음
    if ($end < $pages) {
        $next_block = $end + 1;
        $html .= "<a href='#' class='page-link' data-page='{$next_block}' title='다음'>&rsaquo;</a>";
        $html .= "<a href='#' class='page-link' data-page='{$pages}' title='끝'>&raquo;</a>";
    }

    $html .= '</div>';
    return $html;
}


// (5) 실질적 AJAX/메인 검색 결과 추출
function get_search_results($opts) {
    global $g5, $max_result;
    extract($opts);

    $allowed_search_types = ['subject_content','subject_only','content_only','mb_id','wr_name'];
    $allowed_sorts = ['date_desc','date_asc','relevance'];
    if (!in_array($search_type, $allowed_search_types)) $search_type = 'subject_content';
    if (!in_array($sort, $allowed_sorts)) $sort = 'date_desc';

    // 2글자 미만 차단(게시물/제목/내용)
    if (mb_strlen($stx) < 2 && in_array($search_type, ['subject_content', 'subject_only', 'content_only']))
        return ['count' => 0, 'items' => []];

    // 내부 DB or 외부 DB 접속
    $conn = $use_external ? mysqli_connect(
        $external_db['host'], $external_db['user'], $external_db['pass'], $external_db['name']
    ) : $g5['connect_db'];
    if (!$conn) die('DB 연결 실패: ' . mysqli_connect_error());
    mysqli_set_charset($conn, 'utf8mb4');

    // 검색어에서 공백 제거 후 escape (인덱스 성능↑)
    $stx = mysqli_real_escape_string($conn, preg_replace('/\s+/u', '', $stx));
    $bo_table = mysqli_real_escape_string($conn, $bo_table);

    // 기본 WHERE 조건
    $where = "1";
    if (!empty($stx)) {
        switch ($search_type) {
            case 'mb_id': $where .= " AND mb_id = '{$stx}'"; break;
            case 'wr_name': $where .= " AND wr_name = '{$stx}'"; break;
            case 'subject_only': $where .= " AND MATCH(wr_subject) AGAINST('{$stx}')"; break;
            case 'content_only': $where .= " AND MATCH(wr_content) AGAINST('{$stx}')"; break;
            case 'subject_content':
            default: $where .= " AND MATCH(wr_subject, wr_content) AGAINST('{$stx}')"; break;
        }
    }
    if ($bo_table) $where .= " AND bo_table = '{$bo_table}'";
    if (!empty($excluded_bo_tables)) {
        $escaped = escape_list_for_sql($conn, $excluded_bo_tables);
        $where .= " AND bo_table NOT IN ('" . implode("','", $escaped) . "')";
    }
    if ($search_type !== 'mb_id' && !empty($excluded_ids)) {
        $escaped = escape_list_for_sql($conn, $excluded_ids);
        $where .= " AND mb_id NOT IN ('" . implode("','", $escaped) . "')";
    }
    if ($search_type !== 'wr_name' && !empty($excluded_names)) {
        $escaped = escape_list_for_sql($conn, $excluded_names);
        $where .= " AND wr_name NOT IN ('" . implode("','", $escaped) . "')";
    }
    // 제외어 필터
    if (!empty($exclude_words)) {
        foreach ($exclude_words as $w) {
            $w_esc = mysqli_real_escape_string($conn, $w);
            $where .= " AND wr_subject NOT LIKE '%{$w_esc}%' AND wr_content NOT LIKE '%{$w_esc}%' AND wr_name NOT LIKE '%{$w_esc}%' AND mb_id NOT LIKE '%{$w_esc}%'";
        }
    }

    // 정렬 (정확도/날짜)
    $score_expr = "";
    if ($sort === 'relevance') {
        switch ($search_type) {
            case 'subject_only': $score_expr = ", MATCH(wr_subject) AGAINST('{$stx}') AS score"; break;
            case 'content_only': $score_expr = ", MATCH(wr_content) AGAINST('{$stx}') AS score"; break;
            default: $score_expr = ", MATCH(wr_subject, wr_content) AGAINST('{$stx}') AS score"; break;
        }
    }
    $order_by = ($sort === 'relevance') ? "ORDER BY score DESC" :
        (($sort === 'date_asc') ? "ORDER BY wr_datetime ASC" : "ORDER BY wr_datetime DESC");

    // 본문 쿼리 (LIMIT 주의: rows만큼)
    $sql = "SELECT wr_id, wr_parent, bo_table, mb_id, wr_name, wr_datetime, wr_subject, wr_content, wr_option{$score_expr}
            FROM evape_posts
            WHERE {$where}
            {$order_by}
            LIMIT {$offset}, {$rows}";
    $res = mysqli_query($conn, $sql);
    if (!$res) return ['count' => 0, 'items' => []];

    // 결과 가공
    $items = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $is_comment = $row['wr_id'] !== $row['wr_parent'];
        $row['is_comment'] = $is_comment;
        $content_head = mb_substr($row['wr_content'], 0, 500);
        $decoded = html_entity_decode($content_head, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $stripped = strip_tags($decoded);
        $cleaned = preg_replace('/[\p{C}\p{Z}]+/u', ' ', $stripped);
        $trimmed = trim($cleaned);

        $image_count = preg_match_all('/<img[^>]*src=["\'][^"\']+["\'][^>]*>/i', $row['wr_content'], $dummy);
        $is_secret = isset($row['wr_option']) && strpos($row['wr_option'], 'secret') !== false;
        $row['image_icons'] = str_repeat('🖼️', $image_count);

        if ($is_secret)
            $row['wr_content_preview'] = '<span class="no-content">&lt;잠겨있음&gt;</span>';
        elseif ($trimmed === '')
            $row['wr_content_preview'] = '<span class="no-content">&lt;내용없음&gt;</span>';
        else
            $row['wr_content_preview'] = get_highlighted_text($trimmed, $stx);

        $items[] = $row;
    }

    // 전체 카운트 (상한 적용)
    $sql_total = "SELECT COUNT(*) AS cnt FROM evape_posts WHERE {$where}";
    $res_total = mysqli_query($conn, $sql_total);
    $total = ($res_total && ($row_total = mysqli_fetch_assoc($res_total))) ? (int)$row_total['cnt'] : 0;
    if ($total > $max_result) $total = $max_result;

    if ($use_external && $conn) mysqli_close($conn);
    return ['count' => $total, 'items' => $items];
}
