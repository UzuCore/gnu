<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once('../common.php');
include_once(G5_PATH.'/_head.php');

// 위험 태그 제거 함수
function clean_dangerous_tags($table, $key_field = 'wr_id') {
    $result = sql_query("SELECT {$key_field}, wr_content FROM {$table} WHERE wr_content LIKE '%<%'");
    $cleaned_count = 0;

    while ($row = sql_fetch_array($result)) {
        $wr_id = $row[$key_field];
        $content = $row['wr_content'];

        // 위험 태그 제거 (iframe, script, object, embed, style)
        $cleaned = preg_replace([
            '#<script[^>]*?>.*?</script>#is',
            '#<iframe[^>]*?>.*?</iframe>#is',
            '#<object[^>]*?>.*?</object>#is',
            '#<embed[^>]*?>.*?</embed>#is',
            '#<style[^>]*?>.*?</style>#is',
        ], '', $content);

        if ($cleaned !== $content) {
            $update = "UPDATE {$table} SET wr_content = '" . sql_real_escape_string($cleaned) . "' WHERE {$key_field} = '{$wr_id}'";
            sql_query($update);
            echo "✅ {$table} → {$key_field}={$wr_id} 정리됨<br>";
            $cleaned_count++;
        }
    }

    return $cleaned_count;
}

// g5_write_% 테이블 목록 수집
$tables = [];
$result = sql_query("SHOW TABLES LIKE 'g5_write_%'");
while ($row = mysqli_fetch_array($result)) {
    $tables[] = $row[0];
}

$total = 0;
echo "<h2>🧹 위험 태그 정리 시작</h2>";

foreach ($tables as $tbl) {
    // wr_content 컬럼 존재 여부 확인
    $col_check = sql_fetch("SHOW COLUMNS FROM {$tbl} LIKE 'wr_content'");
    if (!$col_check) continue;

    echo "<strong>▶ {$tbl}</strong><br>";
    $count = clean_dangerous_tags($tbl);
    echo "총 {$count}건 정리됨<br><hr>";
    $total += $count;
}

echo "<h3>🎉 전체 완료: {$total}건 위험 태그 제거</h3>";

include_once(G5_PATH.'/_tail.php');

