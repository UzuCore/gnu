<?php
if (!defined('_GNUBOARD_')) exit;

function editor_html($id, $content, $is_dhtml_editor = true)
{
    global $g5, $config, $is_mobile, $w, $board, $write;
    static $js = true;

    if (
        $is_dhtml_editor && $content &&
        (
            (!$w && isset($board['bo_insert_content']) && !empty($board['bo_insert_content'])) ||
            ($w == 'u' && isset($write['wr_option']) && strpos($write['wr_option'], 'html') === false)
        )
    ) {
        if (preg_match('/\r|\n/', $content) && $content === strip_tags($content, '<a><strong><b>')) {
            $content = nl2br($content);
        }
    }

    $editor_url = G5_EDITOR_URL . '/' . $config['cf_editor'];
    $html = "";

    $html .= "<span class=\"sound_only\">웹에디터 시작</span>";
    if (!$is_mobile && $is_dhtml_editor) {
        $html .= '<script>document.write("<div class=\'cke_sc\'></div>");</script>';
    }

    if ($is_dhtml_editor && $js) {
        switch ($id) {
            case "wr_content":
                $editor_height = 350;
                break;
            default:
                $editor_height = 200;
                break;
        }

        $html .= "\n<script src=\"{$editor_url}/build/ckeditor.js?v=210624\"></script>";
        $html .= "\n<script>var g5_editor_url = '{$editor_url}';</script>";
        $html .= "\n<script src=\"{$editor_url}/build/config.js?v=210624\"></script>";
        $html .= "\n<script>
        var editor_id = '{$id}';
        var editor_height = {$editor_height};
        var editor_chk_upload = true;
        var editor_uri = '" . urlencode($_SERVER['REQUEST_URI']) . "';

        $(function(){
            $(document).on('click', '.btn_cke_sc_close', function() {
                $(this).parent('div.cke_sc_def').remove();
            });
        });
        </script>";
        $js = false;
    }

    if ($is_dhtml_editor) {
        $editor_taDisplay = "border:none;";
        $html .= "
            <style>
            .editor_loading {
                position: absolute;
                left: 50%;
                top: 50%;
                z-index: 1;
                margin: -25px 0 0 -25px;
                border: 5px solid #f3f3f3;
                border-radius: 50%;
                border-top: 5px solid #3498db;
                width: 30px;
                height: 30px;
                animation: spin 2s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            </style>
        ";
        $html .= "<div class=\"editor_loading\"></div>\n";
        $html .= "
            <script>
            CKEDITOR.on('instanceLoaded', function () {
                $(\"div.editor_loading\").hide();
            });
            CKEDITOR.on('instanceReady', function(evt) {
                window.{$id}_editor = evt.editor;
                window.editor_id = '{$id}';
            });
            </script>
        ";
    }

    $ckeditor_class = $is_dhtml_editor ? "ckeditor" : "";
    $html .= "\n<textarea id=\"{$id}\" name=\"{$id}\" class=\"{$ckeditor_class}\" maxlength=\"65536\" style=\"height:{$editor_height}px; {$editor_taDisplay}\">{$content}</textarea>";
    $html .= "\n<span class=\"sound_only\">웹 에디터 끝</span>";
    $html .= "\n<script>var editor_form_name = document.getElementById('{$id}').form.name;</script>";

    return $html;
}

function get_editor_js($id, $is_dhtml_editor = true)
{
    $js = "";
    if ($is_dhtml_editor) {
        $js .= "var {$id}_editor_data = CKEDITOR.instances.{$id}.getData();\n";
    } else {
        $js .= "var {$id}_editor = document.getElementById('{$id}');\n";
    }
    return $js;
}

function chk_editor_js($id, $is_dhtml_editor = true)
{
    $js = "";
    if ($is_dhtml_editor) {
        $js .= "if (!{$id}_editor_data) { alert(\"내용을 입력해 주십시오.\"); CKEDITOR.instances.{$id}.focus(); return false; }\n";
        $js .= "if (typeof(f.{$id}) != \"undefined\") f.{$id}.value = {$id}_editor_data;\n";
        $js .= "var temp_data = {$id}_editor_data.replace(/thumb\-([_\\d\\.]+)_\\d+x\\d+/gim, function(_, res2) { return res2; });\n";
        $js .= "CKEDITOR.instances.{$id}.setData(temp_data);\n";
    } else {
        $js .= "if (!{$id}_editor.value) { alert(\"내용을 입력해 주십시오.\"); {$id}_editor.focus(); return false; }\n";
    }
    $js .= "if(typeof(editor_chk_upload) != \"undefined\" && !editor_chk_upload) { alert(\"이미지가 업로드 중 입니다.\"); return false; }\n";
    return $js;
}
?>
