<?php
// ===== 설정 =====
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once("../../../../../common.php");

// 관리자 권한 확인
if (!$is_admin) { 
    alert('관리자만 접근 가능합니다.');
    exit;
}

// 이미지 좌/우 90도 회전 스크립트

$baseDir = $_SERVER['DOCUMENT_ROOT'] . '/api/screenshots/';
$filename = $_GET['img'] ?? '';
$dir = $_GET['dir'] ?? 'right'; // left, right

// 파일명 검증
if (empty($filename) || strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/') . "?error=invalid_file");
    exit;
}

$filePath = $baseDir . $filename;

// 파일 존재 및 이미지 형식 확인
if (!file_exists($filePath) || !in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/') . "?error=file_not_found");
    exit;
}

try {
    // 이미지 로드 및 회전
    $image = imagecreatefromstring(file_get_contents($filePath));
    $angle = ($dir === 'left') ? 90 : -90; // 좌: 90도, 우: -90도
    $rotated = imagerotate($image, $angle, 0);
    
    // 회전 후 이미지 크기 확인
    $width = imagesx($rotated);
    $height = imagesy($rotated);
    $ratio = $width / $height;
    
    // 비율 체크 및 리사이즈 (오차범위 0.15)
    $target_ratio_43 = 4/3; // 1.333...
    $target_ratio_34 = 3/4; // 0.75
    $tolerance = 0.15; // 오차범위
    
    $resized = null;
    if (abs($ratio - $target_ratio_43) > $tolerance && abs($ratio - $target_ratio_34) > $tolerance) {
        // 현재 비율이 4:3이나 3:4와 차이가 클 때만 리사이즈
        if ($ratio > 1) {
            // 가로가 더 긴 경우 → 4:3으로 조정
            $new_width = $width;
            $new_height = round($width * 3 / 4);
        } else {
            // 세로가 더 긴 경우 → 3:4로 조정
            $new_height = $height;
            $new_width = round($height * 3 / 4);
        }
        
        // 리사이즈된 이미지 생성
        $resized = imagecreatetruecolor($new_width, $new_height);
        
        // PNG 투명도 처리
        if ($ext === 'png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefill($resized, 0, 0, $transparent);
        }
        
        // 이미지 리샘플링
        imagecopyresampled($resized, $rotated, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        
        // 메모리 정리
        imagedestroy($rotated);
        $rotated = $resized;
    }
    
    // 파일 저장
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'png': imagepng($rotated, $filePath, 0); break;
        case 'gif': imagegif($rotated, $filePath); break;
        case 'webp': imagewebp($rotated, $filePath, 100); break;
        case 'bmp': imagebmp($rotated, $filePath); break;
        default: imagejpeg($rotated, $filePath, 100); break;
    }
    
    // 메모리 정리
    imagedestroy($image);
    imagedestroy($rotated);
    
    // 성공 리다이렉트
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/') . "?success=" . $dir);
    
} catch (Exception $e) {
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/') . "?error=rotate_failed");
}
exit;
?>