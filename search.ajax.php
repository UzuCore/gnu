<?php
// ===========================
//   AJAX 검색 결과 전용 파일
// ===========================
include_once('./_common.php');
include_once(G5_PATH . '/search.lib.php');
include_once(G5_PATH . '/search.config.php');

// 파라미터 입력 받기
$options = [
    'stx' => $_GET['stx'] ?? '',
    'bo_table' => $_GET['bo_table'] ?? '',
    'page' => max(1, (int)($_GET['page'] ?? 1)),
    'sort' => $_GET['sort'] ?? 'date_desc',
    'search_type' => $_GET['search_type'] ?? 'subject_content',
    'rows' => 15,
    'page_jump' => 5,
    'page_display_count' => 5,
    'excluded_bo_tables' => $excluded_bo_tables,
    'excluded_ids' => $excluded_ids,
    'excluded_names' => $excluded_names,
    'exclude_words' => $exclude_words,
    'use_external' => $use_external,
    'external_db' => $external_db,
];
$options['offset'] = ($options['page'] - 1) * $options['rows'];

// 공통 검색 함수 호출
$res = get_search_results($options);

// --- 결과값 정리
$total_count = $res['count'];
$show_count = $total_count >= $max_result ? number_format($max_result).'+' : number_format($total_count);
$notice = '';
if ($total_count >= $max_result) {
    $notice = "검색 결과가 많아, 최대 {$max_result}개까지만 표시됩니다. 더 정확한 검색어로 검색해 주세요.";
}

// 페이징 생성
parse_str($_SERVER['QUERY_STRING'], $params);
unset($params['page']);
$base_url = '?' . http_build_query($params) . '&';
$pagination = render_pagination($total_count, $options['page'], $options['rows'], $options['page_jump'], $base_url, $options['page_display_count']);

// 리스트 데이터
$items_arr = [];
foreach($res['items'] as $row){
    $items_arr[] = [
        'url' => G5_BBS_URL.'/board.php?bo_table='.urlencode($row['bo_table']).'&wr_id='.urlencode($row['wr_id']),
        'subject' => get_highlighted_text($row['wr_subject'] ?: '[댓글]', $options['stx']),
        'preview' => $row['image_icons'] . $row['wr_content_preview'],
        'name' => htmlspecialchars($row['wr_name']),
        'date' => substr($row['wr_datetime'],0,10)
    ];
}

// --- 최종 AJAX JSON 반환
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'items' => $items_arr,
    'pagination' => $pagination,
    'show_count' => $show_count,
    'notice' => $notice,
    'count' => $total_count,
]);
