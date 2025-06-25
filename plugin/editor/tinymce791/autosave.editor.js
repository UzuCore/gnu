function get_editor_wr_content() {
    // TinyMCE 에디터에서 값 읽기 (img 태그 제거)
    const editor = tinymce.get('wr_content');
    const raw = editor ? editor.getContent() : '';
    return raw.replace(/<img[^>]*>/gi, ''); // 이미지 태그 제거
}

function put_editor_wr_content(content) {
    // TinyMCE 에디터에 값 넣기
    const editor = tinymce.get('wr_content');
    if (editor) {
        editor.setContent(content);
    }
}
