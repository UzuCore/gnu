function initTinymce(selectorId) {
    if (window.tinymce) {
        tinymce.init({
            selector: '#' + selectorId,
            height: 500,
            menubar: 'file edit view insert format tools table help',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: [
                'undo redo | formatselect | fontselect fontsizeselect | bold italic underline strikethrough forecolor backcolor',
                'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table',
                'charmap insertdatetime | visualblocks code fullscreen | removeformat | help'
            ],
            font_family_formats: '맑은 고딕=Malgun Gothic;굴림=Gulim;돋움=Dotum;바탕=Batang;궁서=Gungsuh;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier,monospace;Georgia=georgia,palatino;',
            fontsize_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt',
            content_style: 'body { font-family:Malgun Gothic,Arial,sans-serif; font-size:14px }',
            branding: false,
            elementpath: false,
            resize: true,
            convert_urls: false,
            cache_suffix: '?v=1',
            language: 'ko_KR',
            language_url: '/plugin/editor/tinymce791/tinymce/langs/ko_KR.js',
            promotion: false,

            // ✅ 이미지 업로드 핸들러 + 진행률 표시
            images_upload_handler: function (blobInfo, success, failure, progress) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/plugin/editor/tinymce791/upload.php');

                // 진행률 표시
                xhr.upload.onprogress = function (e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progress(percent);
                    }
                };

                xhr.onload = function () {
                    if (xhr.status !== 200) return failure('서버 오류: ' + xhr.status);

                    const res = JSON.parse(xhr.responseText);
                    if (res.error) return failure(res.error);

                    success(res.location);
                };

                xhr.onerror = function () {
                    failure('업로드 실패');
                };

                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());

                xhr.send(formData);
            }
        });
    }
}
