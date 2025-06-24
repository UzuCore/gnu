<?php
include_once('../../../common.php');
header('Content-Type: application/json; charset=utf-8');

// JSON 에러 응답 함수
function json_error($message, $http_code = 400) {
    http_response_code($http_code);
    echo json_encode([
        'uploaded' => 0,
        'error' => [ 'message' => $message ]
    ]);
    exit;
}

// 파일 업로드 확인
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    json_error('업로드된 파일이 없거나 오류가 발생했습니다.');
}

// 업로드 파일 정보
$tmp_name  = $_FILES['file']['tmp_name'];
$orig_name = basename($_FILES['file']['name']);
$ext       = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
$mime_type = mime_content_type($tmp_name);

// 허용된 확장자 및 MIME
$allowed_exts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// 확장자 및 MIME 체크
if (!in_array($ext, $allowed_exts)) {
    json_error('허용되지 않는 파일 확장자입니다.');
}
if (!in_array($mime_type, $allowed_mimes)) {
    json_error('허용되지 않는 MIME 타입입니다.');
}

// 이미지 유효성 검사
if (@getimagesize($tmp_name) === false) {
    json_error('이미지 파일이 아니거나 손상되었습니다.');
}

// 업로드 디렉토리 생성
$ymd         = date('Ym');
$upload_dir  = G5_PATH . "/data/file/editor/{$ymd}";
$upload_url  = G5_URL  . "/data/file/editor/{$ymd}";

if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, G5_DIR_PERMISSION, true);
    @chmod($upload_dir, G5_DIR_PERMISSION);
}

// 파일 저장 (중복 방지용 이름)
$uniq_name = md5(uniqid('', true)) . '.' . $ext;
$dest_path = "{$upload_dir}/{$uniq_name}";

if (!move_uploaded_file($tmp_name, $dest_path)) {
    json_error('서버에 파일을 저장하지 못했습니다.', 500);
}

// ✅ CKEditor 5 업로드 성공 응답
echo json_encode([
    'uploaded' => 1,
    'fileName' => $orig_name,
    'url'      => "{$upload_url}/{$uniq_name}"
]);
