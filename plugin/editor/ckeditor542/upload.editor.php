<?php
include_once('../../../common.php');

//$token = get_session('ss_write_'.$bo_table.'_token');
//if(!$token || !$_REQUEST['token'] || $token != $_REQUEST['token']) exit;

$data_dir = G5_DATA_PATH.'/'.G5_EDITOR_DIR.'/'.date('ym', G5_SERVER_TIME);
$data_url = G5_DATA_URL.'/'.G5_EDITOR_DIR.'/'.date('ym', G5_SERVER_TIME);

@mkdir($data_dir, G5_DIR_PERMISSION, true);

$tempfile = $_FILES['file']['tmp_name'];
$filename = $_FILES['file']['name'];

if(!$ext = get_filetype($tempfile)) exit;
$filename = sha1($_SERVER['REMOTE_ADDR'].$filename.time()).substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwx'), 1, 4).'.'.$ext;

$savefile = $data_dir.'/'.$filename;

move_uploaded_file($tempfile, $data_dir.'/'.$filename);

try {
    if(defined('G5_FILE_PERMISSION')) chmod($data_dir.'/'.$filename, G5_FILE_PERMISSION);
} catch (Exception $e) {
}

echo json_encode(array('url' => $data_url.'/'.$filename, 'tmp' => $msg));

//----------------------------------------------------------------------------

function get_filetype($filename)
{
	$types = array(
		'jpg'	=>	array(0, "\xFF\xD8\xFF"),
		'png'	=>	array(0, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"),
		'bmp'	=>	array(0, "\x42\x4D"),
		'gif'	=>	array(0, "\x47\x49\x46\x38"),
		'webp'	=>	array(8, "\x57\x45\x42\x50")
	);

	$rst = false;

	if(is_file($filename) && $hnd = fopen($filename, 'rb'))
	{
		$buf = fread($hnd, 16);

		foreach($types as $type => $patt)
		{
			if(strcmp($patt[1], substr($buf, $patt[0], strlen($patt[1]))) == 0)
			{
				$rst = $type;
				break;
			}
		}

		fclose($hnd);
	}

	return $rst;
}
?>