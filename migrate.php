<?php
include_once('./_common.php');

// 관리자 권한 체크
if (!$is_admin) {
    alert('관리자만 접근할 수 있습니다.');
    exit;
}

// g5_write_* 테이블 전체 wr_comment 카운터 일괄 보정
$tables = array();
$result = sql_query("SHOW TABLES LIKE 'g5_write_%'");
while ($row = sql_fetch_array($result)) {
    $tables[] = array_values($row)[0];
}

foreach ($tables as $table) {
    $sql = "
        SELECT a.wr_id, (COUNT(b.wr_parent) - 1) AS cnt
        FROM {$table} a
        JOIN {$table} b ON a.wr_id = b.wr_parent
        WHERE a.wr_is_comment = 0
        GROUP BY a.wr_id
    ";
    $r = sql_query($sql);

    while ($row = sql_fetch_array($r)) {
        sql_query("UPDATE {$table} SET wr_comment = '{$row['cnt']}' WHERE wr_id = '{$row['wr_id']}' ");
    }

    // wr_comment = 0으로 보정 (댓글 없는 원글)
    sql_query("UPDATE {$table} SET wr_comment = 0 WHERE wr_is_comment = 0 AND wr_id NOT IN (SELECT DISTINCT wr_parent FROM {$table} WHERE wr_is_comment=1)");
}

echo "<h2>모든 게시판 wr_comment 카운터 일괄 보정 완료!</h2>";
