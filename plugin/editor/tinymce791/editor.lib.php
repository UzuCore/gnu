<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function editor_html($id, $content, $is_dhtml_editor = true)
{
    global $g5, $config, $w, $board, $write;
    static $js = true;

    // TinyMCE 줄바꿈 처리 (CKEditor와 동일 로직)
    if (
        $is_dhtml_editor && $content &&
        (
            (!$w && isset($board['bo_insert_content']) && !empty($board['bo_insert_content'])) ||
            ($w === 'u' && isset($write['wr_option']) && strpos($write['wr_option'], 'html') === false)
        )
    ) {
        if (preg_match('/\r|\n/', $content) && $content === strip_tags($content, '<a><strong><b>')) {
            $content = nl2br($content);
        }
    }

    $editor_url = G5_EDITOR_URL . '/' . $config['cf_editor'];

    $html = '<span class="sound_only">웹에디터 시작</span>';

    if ($is_dhtml_editor && $js) {
        // ✅ 언어팩 경로 자동화를 위한 전역변수 전달
        $html .= '<script>window.G5_EDITOR_URL = "' . $editor_url . '";</script>';
        $html .= '<script src="' . $editor_url . '/tinymce/tinymce.min.js"></script>';
        $html .= '<script src="' . $editor_url . '/editor.init.js"></script>';
        $js = false;
    }

    $editor_class = $is_dhtml_editor ? 'tinymce' : '';
    $html .= '<textarea id="' . $id . '" name="' . $id . '" class="' . $editor_class . '">' . $content . '</textarea>';
    $html .= '<span class="sound_only">웹 에디터 끝</span>';

    if ($is_dhtml_editor) {
        $html .= '<script>document.addEventListener("DOMContentLoaded", function(){ initTinymce("' . $id . '"); });</script>';
    }

    return $html;
}

function get_editor_js($id, $is_dhtml_editor = true)
{
    if ($is_dhtml_editor) {
        return "var {$id}_editor_data = tinymce.get('{$id}')?.getContent();";
    } else {
        return "var {$id}_editor = document.getElementById('{$id}');";
    }
}

function chk_editor_js($id, $is_dhtml_editor = true)
{
    if ($is_dhtml_editor) {
        return "if (!{$id}_editor_data) { alert('내용을 입력해 주십시오.'); tinymce.get('{$id}').focus(); return false; } if (typeof(f.{$id}) != 'undefined') f.{$id}.value = {$id}_editor_data;";
    } else {
        return "if (!{$id}_editor.value) { alert('내용을 입력해 주십시오.'); {$id}_editor.focus(); return false; }";
    }
}
?>
