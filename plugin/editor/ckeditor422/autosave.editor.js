function get_editor_wr_content() {
    const raw = CKEDITOR.instances.wr_content?.getData() || '';
    return raw.replace(/<img[^>]*>/gi, ''); // 이미지 태그 제거
}

function put_editor_wr_content(content) {
    if (CKEDITOR.instances.wr_content?.setData) {
        CKEDITOR.instances.wr_content.setData(content);
    }
}
