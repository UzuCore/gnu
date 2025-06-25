<?php
include_once("../../../common.php");
header('Content-Type: application/json; charset=utf-8');

// 오류 응답 출력
function print_json_error($msg) {
    echo json_encode([
        'uploaded' => 0,
        'error' => ['message' => $msg]
    ]);
    exit;
}

// 업로드 용량 제한 계산
function ini_get_bytes($key) {
    $val = trim(ini_get($key));
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}
$limit = min(ini_get_bytes('upload_max_filesize'), ini_get_bytes('post_max_size'));

// 업로드 경로 생성
$ym = date('ym', G5_SERVER_TIME);
$data_dir = G5_DATA_PATH . '/editor/' . $ym;
$data_url = G5_DATA_URL . '/editor/' . $ym;
@mkdir($data_dir, G5_DIR_PERMISSION);
@chmod($data_dir, G5_DIR_PERMISSION);

// 파일 업로드 체크
$upFile = $_FILES['upload'];
if (empty($upFile['tmp_name'])) {
    print_json_error("파일이 존재하지 않습니다.");
}
if ($upFile['size'] > $limit) {
    print_json_error("최대 " . round($limit / 1024 / 1024, 1) . "MB 이하만 업로드 가능합니다.");
}

// 확장자 검사
$ext = strtolower(pathinfo($upFile['name'], PATHINFO_EXTENSION));
if (!preg_match('/^(jpe?g|png|gif|webp)$/i', $ext)) {
    print_json_error("jpg / png / gif / webp 파일만 가능합니다.");
}
if ($ext == 'jpeg') $ext = 'jpg';

// MIME 검사
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $upFile['tmp_name']);
finfo_close($finfo);
$allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mime_type, $allowed_mime)) {
    print_json_error("이미지 MIME 타입이 올바르지 않습니다.");
}

// 실제 이미지인지 확인
if (!@getimagesize($upFile['tmp_name'])) {
    print_json_error("유효한 이미지 파일이 아닙니다.");
}

// 파일명 생성
$file_name = sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])) . '_' . get_microtime() . '.' . $ext;
$save_path = $data_dir . '/' . $file_name;
$save_url  = $data_url . '/' . $file_name;

// 저장
if (move_uploaded_file($upFile['tmp_name'], $save_path)) {
    echo json_encode([
        'uploaded' => 1,
        'fileName' => $file_name,
        'url' => $save_url
    ]);
} else {
    print_json_error("업로드 실패");
}
exit;
