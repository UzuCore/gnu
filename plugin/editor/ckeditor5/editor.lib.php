<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function editor_html($id, $content, $is_dhtml_editor=true)
{
    global $g5, $config, $w, $board, $write;
    static $js = true;

    if( 
        $is_dhtml_editor && $content && 
        (
        (!$w && (isset($board['bo_insert_content']) && !empty($board['bo_insert_content'])))
        || ($w == 'u' && isset($write['wr_option']) && strpos($write['wr_option'], 'html') === false )
        )
    ){       //글쓰기 기본 내용 처리
        if( preg_match('/\r|\n/', $content) && $content === strip_tags($content, '<a><strong><b>') ) {  //textarea로 작성되고, html 내용이 없다면
            $content = nl2br($content);
        }
    }

    $editor_url = G5_EDITOR_URL.'/'.$config['cf_editor'];

    $html = '';
    $html .= '<span class="sound_only">웹에디터 시작</span>';

    if ($is_dhtml_editor && $js) {
        $html .= '<script src="'.G5_EDITOR_URL.'/'.$config['cf_editor'].'/build/ckeditor.js"></script>';
        $html .= '<script src="'.G5_EDITOR_URL.'/'.$config['cf_editor'].'/upload.editor.js"></script>';
        $js = false;
    }

    $ckeditor_class = $is_dhtml_editor ? 'ckeditor' : '';
    $html .= '<textarea id="'.$id.'" name="'.$id.'" class="'.$ckeditor_class.'" maxlength="65536">'.$content.'</textarea>';
    $html .= '<span class="sound_only">웹 에디터 끝</span>';
    $html .= '
        <script>
            ClassicEditor.create( document.querySelector("#'.$id.'"), {
                language: "ko", 
                toolbar: { items: ["bold", "italic", "strikethrough", "underline", "|", "fontColor", "fontBackgroundColor", "|", "bulletedList", "numberedList", "|", "imageUpload", "mediaEmbed", "link"] }, 
                mediaEmbed: { previewsInData: true, removeProviders: ["instagram", "twitter", "googleMaps", "flickr", "facebook"] },
                extraPlugins: [CKEditorUploadAdapterPlugin] 
            }).then(function(newEditor){
                window.'.$id.'_editor = newEditor;
                
                // 이미지 개수 제한 (최대 10장)
                const MAX_IMAGES = 10;
                newEditor.model.document.on("change:data", () => {
                    const images = Array.from(newEditor.model.document.getRoot().getChildren())
                        .filter(child => child.is("element", "image"));
                    if (images.length > MAX_IMAGES) {
                        alert("이미지는 최대 " + MAX_IMAGES + "장까지 등록 가능합니다.");
                        const last = images.pop();
                        newEditor.model.change(writer => writer.remove(last));
                    }
                });

            }).catch(function(error){console.error(error);});
        </script>
    ';

    return $html;
}


// textarea 로 값을 넘긴다. javascript 반드시 필요
function get_editor_js($id, $is_dhtml_editor=true)
{
    if ($is_dhtml_editor) {
        return ' var '.$id.'_editor_data = '.$id.'_editor.getData(); ';
    } else {
        return ' var '.$id.'_editor = document.getElementById("'.$id.'"); ';
    }
}


//  textarea 의 값이 비어 있는지 검사
function chk_editor_js($id, $is_dhtml_editor=true)
{
    if ($is_dhtml_editor) {
        return ' if (!'.$id.'_editor_data) { alert("내용을 입력해 주십시오."); '.$id.'_editor.editing.view.focus(); return false; } if (typeof(f.'.$id.')!="undefined") f.'.$id.'.value = '.$id.'_editor_data; ';
    } else {
        return ' if (!'.$id.'_editor.value) { alert("내용을 입력해 주십시오."); '.$id.'_editor.focus(); return false; } ';
    }
}
