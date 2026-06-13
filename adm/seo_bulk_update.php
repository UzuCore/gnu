<?php
// SEO 제목 전체 재생성 스크립트 (드라이런 포함)
// URL: /admin/seo_bulk_update.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once("../common.php");

// 관리자 권한 확인
if (!$is_admin) { 
    alert('관리자만 접근 가능합니다.');
    exit;
}

// 처리할 게시판 설정
$bo_table = 'roms'; // 필요시 변경
$write_table = $g5['write_prefix'] . $bo_table;

// 실행 모드 확인
$dry_run = !isset($_GET['execute']);
$limit = (int)($_GET['limit'] ?? 50); // 드라이런에서 표시할 개수

if ($dry_run) {
    echo "<h2>🔍 SEO 제목 재생성 미리보기 - {$bo_table}</h2>";
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>⚠️ 드라이런 모드</h4>";
    echo "<p>실제 변경하지 않고 어떻게 변경될지 미리 확인합니다.</p>";
    echo "<p>최대 {$limit}개 게시물만 표시됩니다.</p>";
    echo "</div>";
} else {
    echo "<h2>🚀 SEO 제목 전체 재생성 실행 - {$bo_table}</h2>";
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>⚠️ 실제 실행 모드</h4>";
    echo "<p>데이터베이스가 실제로 변경됩니다!</p>";
    echo "</div>";
}

echo "<p>시작 시간: " . date('Y-m-d H:i:s') . "</p>";

// 쿼리 설정
if ($dry_run) {
    // 드라이런: 제한된 개수만 조회
    $sql = "SELECT wr_id, wr_subject, wr_seo_title 
            FROM {$write_table} 
            WHERE wr_is_comment = 0 
            ORDER BY wr_id ASC 
            LIMIT {$limit}";
} else {
    // 실제 실행: 모든 게시물 조회
    $sql = "SELECT wr_id, wr_subject, wr_seo_title 
            FROM {$write_table} 
            WHERE wr_is_comment = 0 
            ORDER BY wr_id ASC";
}

$result = sql_query($sql);
$total_count = sql_num_rows($result);

if ($dry_run) {
    // 전체 개수 확인
    $count_sql = "SELECT COUNT(*) as cnt FROM {$write_table} WHERE wr_is_comment = 0";
    $count_result = sql_fetch($count_sql);
    $actual_total = $count_result['cnt'];
    
    echo "<p>전체 게시물: {$actual_total}개</p>";
    echo "<p>미리보기 대상: {$total_count}개</p>";
} else {
    echo "<p>처리 대상: {$total_count}개 (기존 SEO 제목 모두 재생성)</p>";
}

echo "<hr>";

if ($total_count > 0) {
    $success_count = 0;
    $error_count = 0;
    $unchanged_count = 0;
    $will_change_count = 0;
    $current = 0;
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>순번</th><th>ID</th><th>제목</th><th>기존 SEO</th><th>새 SEO</th><th>상태</th>";
    echo "</tr>";
    
    while ($row = sql_fetch_array($result)) {
        $current++;
        $wr_id = $row['wr_id'];
        $wr_subject = $row['wr_subject'];
        $old_seo_title = $row['wr_seo_title'];
        
        // 새 SEO 제목 생성
        $new_seo_title = exist_seo_title_recursive('bbs', generate_seo_title($wr_subject), $write_table, $wr_id);
        
        echo "<tr>";
        echo "<td>{$current}</td>";
        echo "<td>{$wr_id}</td>";
        echo "<td>" . htmlspecialchars($wr_subject) . "</td>";
        echo "<td>" . htmlspecialchars($old_seo_title) . "</td>";
        echo "<td>" . htmlspecialchars($new_seo_title) . "</td>";
        
        // 기존과 동일한지 확인
        if ($old_seo_title === $new_seo_title) {
            $unchanged_count++;
            echo "<td style='color: #6c757d;'>변경없음</td>";
        } else {
            if ($dry_run) {
                $will_change_count++;
                echo "<td style='color: #007bff; font-weight: bold;'>변경예정</td>";
            } else {
                // 실제 업데이트 실행
                $update_sql = "UPDATE {$write_table} 
                               SET wr_seo_title = '" . sql_real_escape_string($new_seo_title) . "' 
                               WHERE wr_id = {$wr_id}";
                
                if (sql_query($update_sql)) {
                    $success_count++;
                    echo "<td style='color: #28a745; font-weight: bold;'>✓ 업데이트</td>";
                } else {
                    $error_count++;
                    echo "<td style='color: #dc3545; font-weight: bold;'>✗ 실패</td>";
                }
            }
        }
        echo "</tr>";
        
        // 출력 플러시
        if (ob_get_level()) ob_flush();
        flush();
        
        // 서버 부하 방지 (100개마다 잠시 대기)
        if (!$dry_run && $current % 100 === 0) {
            usleep(100000); // 0.1초 대기
        }
    }
    
    echo "</table>";
    echo "<hr>";
    
    if ($dry_run) {
        echo "<h3>📊 미리보기 결과</h3>";
        echo "<p>표시된 {$total_count}개 중:</p>";
        echo "<p><strong>변경예정:</strong> {$will_change_count}개</p>";
        echo "<p><strong>변경없음:</strong> {$unchanged_count}개</p>";
        
        echo "<div style='text-align: center; margin: 20px 0;'>";
        echo "<a href='?execute=1' style='background: #dc3545; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>🚀 실제 실행하기</a>";
        echo " ";
        echo "<a href='?limit=" . ($limit + 50) . "' style='background: #17a2b8; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px;'>더 많이 미리보기 (" . ($limit + 50) . "개)</a>";
        echo "</div>";
        
    } else {
        echo "<h3>✅ 처리 완료!</h3>";
        echo "<p>총 처리: {$total_count}개</p>";
        echo "<p><strong>업데이트:</strong> {$success_count}개</p>";
        echo "<p><strong>변경없음:</strong> {$unchanged_count}개</p>";
        echo "<p><strong>실패:</strong> {$error_count}개</p>";
        echo "<p>완료 시간: " . date('Y-m-d H:i:s') . "</p>";
        
        echo "<div style='text-align: center; margin: 20px 0;'>";
        echo "<a href='?' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px;'>🔍 다시 미리보기</a>";
        echo "</div>";
    }
    
} else {
    echo "<p>처리할 게시물이 없습니다.</p>";
}
?>