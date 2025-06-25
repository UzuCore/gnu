<?php
include_once('./_common.php');

// 1. 관리자 권한 체크 (최고관리자만)
if (!$is_admin || $member['mb_id'] != $config['cf_admin']) {
    alert('최고관리자만 실행할 수 있습니다.');
    exit;
}

// 2. 실행 요약 변수
$result_summary = array();

// 3. 전체 게시판 순회
$sql = "SELECT * FROM {$g5['board_table']}";
$res_board = sql_query($sql);
while ($board = sql_fetch_array($res_board)) {
    $bo_table = $board['bo_table'];
    $table = "{$g5['write_prefix']}{$bo_table}";
    $summary = array(
        'bo_table' => $bo_table,
        'write_cnt' => 0,
        'comment_cnt' => 0,
        'notice_ori' => $board['bo_notice'],
        'notice_new' => '',
        'wr_comment_update' => 0
    );

    // 글수
    $row = sql_fetch("SELECT COUNT(*) as cnt FROM {$table} WHERE wr_is_comment = 0");
    $bo_count_write = $row['cnt'];
    $summary['write_cnt'] = $bo_count_write;

    // 코멘트수
    $row = sql_fetch("SELECT COUNT(*) as cnt FROM {$table} WHERE wr_is_comment = 1");
    $bo_count_comment = $row['cnt'];
    $summary['comment_cnt'] = $bo_count_comment;

    // 글별 wr_comment 조정
    $wr_comment_updated = 0;
    if (isset($_POST['proc_count'])) {
        $sql2 = "SELECT a.wr_id, (COUNT(b.wr_parent) - 1) AS cnt
                 FROM {$table} a, {$table} b
                 WHERE a.wr_id = b.wr_parent AND a.wr_is_comment = 0
                 GROUP BY a.wr_id";
        $result2 = sql_query($sql2);
        while ($row2 = sql_fetch_array($result2)) {
            sql_query("UPDATE {$table} SET wr_comment = '{$row2['cnt']}' WHERE wr_id = '{$row2['wr_id']}'");
            $wr_comment_updated++;
        }
    }
    $summary['wr_comment_update'] = $wr_comment_updated;

    // 공지사항 정리
    $bo_notice = "";
    $lf = "";
    if ($board['bo_notice']) {
        $tmp_array = explode(",", $board['bo_notice']);
        foreach ($tmp_array as $tmp_wr_id) {
            $tmp_wr_id = trim($tmp_wr_id);
            $row = sql_fetch("SELECT COUNT(*) as cnt FROM {$table} WHERE wr_id = '{$tmp_wr_id}'");
            if ($row['cnt']) {
                $bo_notice .= $lf . $tmp_wr_id;
                $lf = ",";
            }
        }
    }
    $summary['notice_new'] = $bo_notice;

    // 게시판 정보 업데이트
    $sql_common = ""; // 필요시 추가
    $sql = "UPDATE {$g5['board_table']}
               SET bo_notice = '{$bo_notice}',
                   bo_count_write = '{$bo_count_write}',
                   bo_count_comment = '{$bo_count_comment}'
                   {$sql_common}
             WHERE bo_table = '{$bo_table}'";
    sql_query($sql);

    $result_summary[] = $summary;
}

// 4. 실행 요약 출력
echo "<h2>게시판별 실행결과 요약</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse:collapse'>";
echo "<tr>
        <th>게시판</th>
        <th>글수</th>
        <th>코멘트수</th>
        <th>공지 (기존)</th>
        <th>공지 (정리후)</th>
        <th>wr_comment 수정건수</th>
      </tr>";
foreach ($result_summary as $row) {
    echo "<tr>
            <td>{$row['bo_table']}</td>
            <td align='right'>{$row['write_cnt']}</td>
            <td align='right'>{$row['comment_cnt']}</td>
            <td>{$row['notice_ori']}</td>
            <td>{$row['notice_new']}</td>
            <td align='right'>{$row['wr_comment_update']}</td>
          </tr>";
}
echo "</table>";
?>

