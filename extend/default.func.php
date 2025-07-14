<?php
if (!defined('_GNUBOARD_') || !defined('G5_THEME_PATH')) return;

function get_list_nothumb($bo_table, $wr_id)
{
    global $g5, $config;
    
    $empty_array = array('src'=>'', 'ori'=>'', 'alt'=>'');
    
    // 캐시/권한 체크 우회하고 직접 DB 조회
    $write_table = $g5['write_prefix'].$bo_table;
    $write = sql_fetch("SELECT * FROM {$write_table} WHERE wr_id = '{$wr_id}'");
    
    if (!$write) return $empty_array;
    
    // 첨부파일 직접 조회 (권한 무시)
    $file_sql = "SELECT bf_file, bf_content FROM {$g5['board_file_table']} 
                 WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' 
                 AND bf_type IN (1, 2, 3, 18) AND bf_file != ''
                 ORDER BY bf_no LIMIT 1";
    $row = sql_fetch($file_sql);
    
    if ($row && $row['bf_file']) {
        $filename = $row['bf_file'];
        $alt = get_text($row['bf_content']);
        $ori = G5_DATA_URL.'/file/'.$bo_table.'/'.$filename;
        $src = $ori;
        return array("src"=>$src, "ori"=>$ori, "alt"=>$alt);
    }
    
    // 에디터 이미지 확인 (비밀글이어도 처리)
    if ($matches = get_editor_image($write['wr_content'], false)) {
        for($i=0; $i<count($matches[1]); $i++) {
            $p = parse_url($matches[1][$i]);
            if(strpos($p['path'], '/'.G5_DATA_DIR.'/') != 0)
                $data_path = preg_replace('/^\/.*\/'.G5_DATA_DIR.'/', '/'.G5_DATA_DIR, $p['path']);
            else
                $data_path = $p['path'];
                
            $srcfile = G5_PATH.$data_path;
            if(preg_match("/\.({$config['cf_image_extension']})$/i", $srcfile) && is_file($srcfile)) {
                $size = @getimagesize($srcfile);
                if(empty($size)) continue;
                
                preg_match("/alt=[\"\']?([^\"\']*)[\"\']?/", $matches[0][$i], $malt);
                $alt = isset($malt[1]) ? get_text($malt[1]) : '';
                
                $ori = G5_URL.$data_path;
                $src = $ori;
                return array("src"=>$src, "ori"=>$ori, "alt"=>$alt);
            }
        }
    }
    
    return $empty_array;
}