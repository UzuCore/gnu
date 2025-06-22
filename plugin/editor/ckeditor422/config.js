// CKEditor 경고 무시
if (window.CKEDITOR && CKEDITOR.warn) {
    const originalWarn = CKEDITOR.warn;
    CKEDITOR.warn = function(msg) {
        if (msg === 'clipboard-image-handling-disabled') return;
        originalWarn.apply(this, arguments);
    };
}

if (typeof(g5_is_mobile) === "undefined") {
    g5_is_mobile = false;
}

CKEDITOR.editorConfig = function(config) {
    config.language = 'ko';
    config.allowedContent = true;
    config.extraPlugins = 'font,uploadimage,clipboard,justify,colorbutton';
    config.removePlugins = 'exportpdf,copyformatting,preview,print,save,templates,about';

    config.removeButtons = 'Source,Cut,Copy,Paste,PasteText,Scayt,PasteFromWord,Subscript,Superscript,RemoveFormat,BulletedList,NumberedList,Outdent,Indent,Blockquote,Unlink,Anchor,Table,HorizontalRule,SpecialChar,Styles,Format,Italic,About';

    config.font_names = '맑은 고딕/Malgun Gothic;굴림/Gulim;돋움/Dotum;바탕/Batang;궁서/Gungsuh;' +
                        'Arial;Comic Sans MS;Courier New;Georgia;Lucida Sans Unicode;Tahoma;Times New Roman;Trebuchet MS;Verdana';

    config.fontSize_sizes = '8pt;9pt;10pt;11pt;12pt;14pt;16pt;18pt;20pt;24pt';

    config.height = g5_is_mobile ? '220px' : '330px';

    // 업로드 URL 설정
    let upload_url = "/plugin/editor/ckeditor422/upload.php?type=Images";
    if (typeof(editor_id) !== "undefined") upload_url += "&editor_id=" + editor_id;
    if (typeof(editor_uri) !== "undefined") upload_url += "&editor_uri=" + editor_uri;
    if (typeof(editor_form_name) !== "undefined") upload_url += "&editor_form_name=" + editor_form_name;

    config.filebrowserImageUploadUrl = upload_url;
    config.clipboard_uploadUrl = upload_url + '&responseType=json';

    // 툴바 구성
    if (g5_is_mobile) {
        config.toolbar = window.innerWidth > 363 ? [
            { name: 'clipboard', items: ['Undo', 'Redo'] },
            { name: 'basicstyles', items: ['Bold', 'Underline', 'Strike'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'links', items: ['Link'] },
            { name: 'insert', items: ['Image'] },
            { name: 'tools', items: ['Maximize'] }
        ] : [
            { name: 'clipboard', items: ['Undo', 'Redo'] },
            { name: 'basicstyles', items: ['Bold', 'Underline', 'Strike'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'links', items: ['Link'] },
            '/',
            { name: 'insert', items: ['Image'] },
            { name: 'tools', items: ['Maximize'] }
        ];
    } else {
        config.toolbar = [
            { name: 'clipboard', items: ['Undo', 'Redo'] },
            { name: 'styles', items: ['Font', 'FontSize'] },
            { name: 'basicstyles', items: ['Bold', 'Underline', 'Strike'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
            { name: 'links', items: ['Link'] },
            { name: 'insert', items: ['Image'] },
            { name: 'tools', items: ['Maximize'] }
        ];
    }

    // 이미지 대화상자 정리
    CKEDITOR.on('dialogDefinition', function(ev) {
        if (ev.data.name === 'image') {
            const info = ev.data.definition.getContents('info');
            ev.data.definition.removeContents('advanced');
            ev.data.definition.removeContents('Link');
            info.remove('txtHSpace');
            info.remove('txtVSpace');
            info.remove('htmlPreview');
        }
    });

    // 이미지 붙여넣기 지원
    CKEDITOR.on('pluginsLoaded', function() {
        if (CKEDITOR.plugins.clipboard) {
            CKEDITOR.plugins.clipboard.isImagePasteSupported = function() {
                return true;
            };
        }
    });

    // 에디터 준비되면 이미지 제한 + 자동 업로드 구성
    CKEDITOR.on('instanceReady', function(ev) {
        const editor = ev.editor;
        const MAX_IMAGES = 10;

        // 이미지 개수 제한 검사
        function checkImageLimit(evt) {
            const html = editor.getData();
            const matches = html.match(/<img\b[^>]*>/gi);
            const count = matches ? matches.length : 0;
            if (count >= MAX_IMAGES) {
                editor.showNotification('이미지는 최대 10장까지만 등록 가능합니다.', 'warning');
                if (evt) evt.cancel();
                return false;
            }
            return true;
        }

        editor.on('fileUploadRequest', checkImageLimit);
        editor.on('paste', checkImageLimit);
        editor.on('drop', checkImageLimit);

        // 이미지 스타일 적용
        editor.on('insertElement', function(evt) {
            const el = evt.data;
            if (el.getName() === 'img') {
                el.setStyle('max-width', '100%');
                el.setStyle('height', 'auto');
            }
        });

        // 팝업 없이 직접 업로드 구현
        editor.addCommand('image', {
            exec: function(editor) {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.style.display = 'none';
                document.body.appendChild(input);

                input.addEventListener('change', function() {
                    const file = input.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('upload', file);

					editor.showNotification('업로드를 시작합니다', 'info');

                    fetch(upload_url + '&responseType=json', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.uploaded && data.url) {
                            editor.insertHtml('<img src="' + data.url + '" style="max-width:100%;height:auto;">');
                            editor.showNotification('업로드 완료', 'success');
                        } else {
                            editor.showNotification(data?.error?.message || '업로드 실패', 'warning');
                        }
                    })
                    .catch(() => {
                        editor.showNotification('업로드 중 오류 발생', 'warning');
                    });

                    document.body.removeChild(input);
                });

                input.click();
            }
        });
    });
};
