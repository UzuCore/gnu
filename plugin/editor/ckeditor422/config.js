// CKEditor 워닝 중 clipboard-image-handling-disabled 메시지는 무시
if (window.CKEDITOR && CKEDITOR.warn) {
    const originalWarn = CKEDITOR.warn;
    CKEDITOR.warn = function(msg) {
        if (msg === 'clipboard-image-handling-disabled') return;
        originalWarn.apply(this, arguments);
    };
}

if(typeof(g5_is_mobile) == "undefined") {
	g5_is_mobile = false;
}

CKEDITOR.editorConfig = function(config) {
    // 기본 설정
    config.language = 'ko';

    // 글꼴 옵션
    config.font_names =
        '맑은 고딕/Malgun Gothic;굴림/Gulim;돋움/Dotum;바탕/Batang;궁서/Gungsuh;' +
        'Arial;Comic Sans MS;Courier New;Georgia;Lucida Sans Unicode;Tahoma;Times New Roman;Trebuchet MS;Verdana';

    // 글자 크기 옵션
    config.fontSize_sizes = '8pt;9pt;10pt;11pt;12pt;14pt;16pt;18pt;20pt;24pt';

	if (g5_is_mobile) {
		if (window.innerWidth > 363) {
			config.toolbar = [
				{ name: 'clipboard', items: [ 'Undo', 'Redo' ] },
				{ name: 'basicstyles', items: [ 'Bold', 'Underline', 'Strike' ] },
				{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
				{ name: 'links', items: [ 'Link' ] },
				{ name: 'insert', items: [ 'Image' ] },
				{ name: 'tools', items: [ 'Maximize' ] },
			];
		} else {
			config.toolbar = [
				{ name: 'clipboard', items: [ 'Undo', 'Redo' ] },
				{ name: 'basicstyles', items: [ 'Bold', 'Underline', 'Strike' ] },
				{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
				{ name: 'links', items: [ 'Link' ] },
				'/',
				{ name: 'insert', items: [ 'Image' ] },
				{ name: 'tools', items: [ 'Maximize' ] },
			];
		}
	} else {
		config.toolbar = [
			{ name: 'clipboard', items: [ 'Undo', 'Redo' ] },
			{ name: 'styles', items: [ 'Font', 'FontSize' ] },
			{ name: 'basicstyles', items: [ 'Bold', 'Underline', 'Strike' ] },
			{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
			{ name: 'paragraph', items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
			{ name: 'links', items: [ 'Link' ] },
			{ name: 'insert', items: [ 'Image' ] },
			{ name: 'tools', items: [ 'Maximize' ] },
    ];}
	
	config.removeButtons = 'Source,Cut,Copy,Paste,PasteText,Scayt,PasteFromWord,Subscript,Superscript,RemoveFormat,BulletedList,NumberedList,Outdent,Indent,Blockquote,Unlink,Anchor,Table,HorizontalRule,SpecialChar,Styles,Format,Italic,About';
    config.extraPlugins = 'font,uploadimage,clipboard,justify,colorbutton';
    config.allowedContent = true;

	// 에디터 높이 설정
	config.height = g5_is_mobile ? "220px" : "330px";

    // 업로드 URL 구성
    let upload_url = "/plugin/editor/ckeditor4/upload.php?type=Images";
    if (typeof(editor_id) != "undefined" && editor_id) upload_url += "&editor_id=" + editor_id;
    if (typeof(editor_uri) != "undefined" && editor_uri) upload_url += "&editor_uri=" + editor_uri;
    if (typeof(editor_form_name) != "undefined" && editor_form_name) upload_url += "&editor_form_name=" + editor_form_name;

    config.filebrowserImageUploadUrl = upload_url;
    config.clipboard_uploadUrl = upload_url + '&responseType=json';

    // 이미지 대화상자 탭 제거
    CKEDITOR.on('dialogDefinition', function(ev) {
        if (ev.data.name === 'image') {
            ev.data.definition.removeContents('advanced');
            ev.data.definition.removeContents('Link');
            var infoTab = ev.data.definition.getContents('info');
            infoTab.remove('txtHSpace');
            infoTab.remove('txtVSpace');
            infoTab.remove('htmlPreview');
        }
    });

    // 이미지 붙여넣기 지원 (경고 제거)
    CKEDITOR.on('pluginsLoaded', function() {
        if (CKEDITOR.plugins.clipboard) {
            CKEDITOR.plugins.clipboard.isImagePasteSupported = function() {
                return true;
            };
        }
    });

    // 인스턴스 준비 시 커스터마이징
    CKEDITOR.on('instanceReady', function(ev) {
        var editor = ev.editor;
        const MAX_IMAGES = 10;

        // 이미지 개수 제한
        function checkImageLimit(evt) {
            const html = editor.getData();
            const matches = html.match(/<img\b[^>]*>/gi);
            const count = matches ? matches.length : 0;
            if (count >= MAX_IMAGES) {
                alert('이미지는 최대 ' + MAX_IMAGES + '장까지만 등록 가능합니다.');
                if (evt) evt.cancel();
                return false;
            }
            return true;
        }
        editor.on('fileUploadRequest', checkImageLimit);
        editor.on('paste', checkImageLimit);
        editor.on('drop', checkImageLimit);

        // 이미지 삽입 후 스타일 적용 + 줄바꿈 + 커서 이동
        editor.on('insertElement', function(evt) {
            var el = evt.data;
            if (el.getName() === 'img') {
                el.setStyle('max-width', '100%');
                el.setStyle('height', 'auto');
            }
        });

        // 이미지 버튼 → 팝업 없이 직접 업로드 실행
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

                    fetch(upload_url + '&responseType=json', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.uploaded && data.url) {
                            editor.insertHtml('<img src="' + data.url + '" style="max-width:100%;height:auto;">');
                        } else {
                            alert(data?.error?.message || '업로드 실패');
                        }
                    })
                    .catch(() => alert('업로드 중 오류 발생'));

                    document.body.removeChild(input);
                });

                input.click();
            }
        });
    });
};
