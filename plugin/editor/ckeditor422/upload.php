<?php
include_once("../../../common.php");

// 오류 출력 함수
function print_error($type, $msg) {
    if(strtolower($type) == "json") {
        $res = array();
        $res['uploaded'] = 0;
        $res['error']['message'] = $msg;
        echo json_encode($res);
    } else {
        echo "<script> alert('{$msg}'); </script>";
    }
    exit;
}

// 업로드 용량 제한 계산 (php.ini 값 사용)
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
$max_upload = ini_get_bytes('upload_max_filesize');
$max_post   = ini_get_bytes('post_max_size');
$limit      = min($max_upload, $max_post); // 바이트 단위

// 업로드 경로 세팅
$ym = date('ym', G5_SERVER_TIME);
$data_dir = G5_DATA_PATH.'/editor/'.$ym;
$data_url = G5_DATA_URL.'/editor/'.$ym;
@mkdir($data_dir, G5_DIR_PERMISSION);
@chmod($data_dir, G5_DIR_PERMISSION);

// CKEditorFuncNum GET 파라미터 안전하게 받기
$funcNum = isset($_GET['CKEditorFuncNum']) ? $_GET['CKEditorFuncNum'] : '';
$responseType = isset($_REQUEST['responseType']) ? $_REQUEST['responseType'] : '';

// 업로드 파일
$upFile = $_FILES['upload'];
if(empty($upFile['tmp_name'])) {
    print_error($responseType, "파일이 존재하지 않습니다.");
}

// 용량 제한 체크
if ($upFile['size'] > $limit) {
    $msg = "최대 " . round($limit / 1024 / 1024, 1) . "MB 이하 파일만 업로드 가능합니다.";
    print_error($responseType, $msg);
}

// 확장자 체크
$fileInfo = pathinfo($upFile['name']);
$extension = strtolower($fileInfo['extension']);
if (!preg_match("/^(jpe?g|gif|png|webp)$/i", $extension)) {
    print_error($responseType, "jpg / gif / png / webp 파일만 가능합니다.");
}
if($extension == 'jpeg') $extension = 'jpg';

// MIME-TYPE 체크
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $upFile['tmp_name']);
finfo_close($finfo);
$allowed_mime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
if (!in_array($mime_type, $allowed_mime)) {
    print_error($responseType, "이미지 파일만 업로드 가능합니다 (MIME 불일치)");
}

// 실제 이미지인지 체크
if (!@getimagesize($upFile['tmp_name'])) {
    print_error($responseType, "정상 이미지 파일이 아닙니다.");
}

// 파일명 생성(원 소스 규칙)
if(!function_exists('get_microtime')) {
    function get_microtime() {
        list($usec, $sec) = explode(' ', microtime());
        return $sec.substr($usec,2,6);
    }
}
$file_name = sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])).'_'.get_microtime().".".$extension;
$save_dir = sprintf('%s/%s', $data_dir, $file_name);

// 파일 이동
if (move_uploaded_file($upFile["tmp_name"], $save_dir)) {
    $save_url = sprintf('%s/%s', $data_url, $file_name);

    if(strtolower($responseType) == "json") {
        $res = array();
        $res['fileName'] = $file_name;
        $res['url'] = $save_url;
        $res['uploaded'] = 1;
        echo json_encode($res);
    } else {
        echo "<script>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$save_url}', '');</script>";
    }
    exit;
}

print_error($responseType, "업로드 실패");
?>
