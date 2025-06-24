function get_editor_wr_content() {
    const raw = window.wr_content_editor?.getData?.() || '';
    return raw.replace(/<img[^>]*>/gi, '');  // 이미지 태그 제거
}

function put_editor_wr_content(content) {
    if (window.wr_content_editor?.setData) {
        window.wr_content_editor.setData(content);
    }
}
