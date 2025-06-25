<?php
include_once("../../../common.php");
header('Content-Type: application/json; charset=utf-8');

// --- 공통 에러 응답 함수 ---
function print_json_error($msg, $for='ckeditor') {
    $err = [
        'ckeditor' => ['uploaded' => 0, 'error' => ['message' => $msg]],
        'tinymce'  => ['error' => $msg],
        'simple'   => ['success' => false, 'message' => $msg]
    ];
    echo json_encode($err[$for]);
    exit;
}

// --- 파일 사이즈 제한 계산 ---
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

// --- 업로드 변수명 자동 감지 (CKEditor, TinyMCE 등 대응) ---
$upFile = $_FILES['upload'] ?? $_FILES['file'] ?? null;
if (empty($upFile) || empty($upFile['tmp_name'])) {
    print_json_error("파일이 존재하지 않습니다.", 'tinymce');
}
if ($upFile['size'] > $limit) {
    print_json_error("최대 ".round($limit / 1024 / 1024, 1)."MB 이하만 업로드 가능합니다.", 'tinymce');
}

// --- 확장자 및 MIME 검증 ---
$ext = strtolower(pathinfo($upFile['name'], PATHINFO_EXTENSION));
if (!preg_match('/^(jpe?g|png|gif|webp)$/i', $ext)) {
    print_json_error("jpg / png / gif / webp 파일만 가능합니다.", 'tinymce');
}
if ($ext == 'jpeg') $ext = 'jpg';

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $upFile['tmp_name']);
finfo_close($finfo);
$allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mime_type, $allowed_mime)) {
    print_json_error("이미지 MIME 타입이 올바르지 않습니다.", 'tinymce');
}

// --- 실제 이미지 검증 ---
if (!@getimagesize($upFile['tmp_name'])) {
    print_json_error("유효한 이미지 파일이 아닙니다.", 'tinymce');
}

// --- 업로드 경로 생성 ---
$ym = date('ym', G5_SERVER_TIME ?? time());
$data_dir = G5_DATA_PATH . '/editor/' . $ym;
$data_url = G5_DATA_URL . '/editor/' . $ym;
@mkdir($data_dir, G5_DIR_PERMISSION, true);
@chmod($data_dir, G5_DIR_PERMISSION);

// --- 랜덤 파일명 생성 (IP + microtime) ---
if (!function_exists('get_microtime')) {
    function get_microtime() {
        $mt = explode(' ', microtime());
        return $mt[1] . substr(str_replace('.', '', $mt[0]), 0, 6);
    }
}
$file_name = sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])) . '_' . get_microtime() . '.' . $ext;
$save_path = $data_dir . '/' . $file_name;
$save_url  = $data_url . '/' . $file_name;

// --- 저장 & 응답 ---
if (move_uploaded_file($upFile['tmp_name'], $save_path)) {
    // TinyMCE(및 Summernote): { location: ... }
    echo json_encode([
        'location' => $save_url
    ]);
    // 필요시 아래 주석처럼 CKEditor 4 공식 포맷 응답 추가
    // echo json_encode([
    //     'uploaded' => 1,
    //     'fileName' => $file_name,
    //     'url' => $save_url
    // ]);
    exit;
} else {
    print_json_error("업로드 실패", 'tinymce');
}