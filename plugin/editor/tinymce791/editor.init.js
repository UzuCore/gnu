function initTinymce(selectorId) {
	const theme = (localStorage.getItem('theme') || 'light').toLowerCase();
    const isDark = theme === 'dark';
	
	if (window.tinymce) {
        tinymce.init({
            selector: '#' + selectorId,
			skin: isDark ? 'tinymce-5-dark' : 'tinymce-5',
            content_css: isDark ? 'dark' : 'default',
            height: 400,
			mobile: {
				menubar: false,
				height: 200,
				toolbar: [
					'bold | image media charmap emoticons | fullscreen'
				]
			},
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'media', 'charmap',
                'fullscreen', 'help', 'emoticons'
            ],
            toolbar: [
                'undo redo | fontselect fontsizeselect bold underline strikethrough forecolor backcolor | link image media charmap emoticons | fullscreen '
            ],
            content_style: `
                body { font-family:Malgun Gothic,Arial,sans-serif; font-size:14px; }
				img { max-width:100%; height:auto; display:block; }
            `,
            branding: false,
            elementpath: false,
            resize: true,
            convert_urls: false,
            cache_suffix: '?v=1',
            language: 'ko_KR',
            language_url: G5_EDITOR_URL + '/tinymce/langs/ko_KR.js',
            promotion: false,
            paste_data_images: false,

            // ---- 커스텀 fontselect / fontsizeselect 메뉴 구현 ----
            setup: function(editor) {
				editor.on('init', function() {
					// 최초 로딩시, 기본 폰트와 폰트사이즈를 에디터 selection/caret에 적용
					editor.execCommand('FontName', false, 'Malgun Gothic,Arial,sans-serif');
					editor.execCommand('FontSize', false, '14pt');
				}),
				
                // fontselect (글꼴)
                editor.ui.registry.addMenuButton('fontselect', {
                    text: '글꼴',
                    fetch: function(callback) {
                        callback([
                            { type: 'menuitem', text: '맑은 고딕', onAction: () => editor.execCommand('FontName', false, 'Malgun Gothic,Arial,sans-serif') },
                            { type: 'menuitem', text: '굴림', onAction: () => editor.execCommand('FontName', false, 'Gulim') },
                            { type: 'menuitem', text: '돋움', onAction: () => editor.execCommand('FontName', false, 'Dotum') },
                            { type: 'menuitem', text: '바탕', onAction: () => editor.execCommand('FontName', false, 'Batang') },
                            { type: 'menuitem', text: 'Arial', onAction: () => editor.execCommand('FontName', false, 'Arial,Helvetica,sans-serif') },
                            { type: 'menuitem', text: 'Times New Roman', onAction: () => editor.execCommand('FontName', false, 'Times New Roman,Times,serif') },
                            { type: 'menuitem', text: 'Courier New', onAction: () => editor.execCommand('FontName', false, 'Courier New,Courier,monospace') }
                        ]);
                    }
                });

                // fontsizeselect (글자 크기)
                editor.ui.registry.addMenuButton('fontsizeselect', {
                    text: '크기',
                    fetch: function(callback) {
                        callback([
                            { type: 'menuitem', text: '8pt', onAction: () => editor.execCommand('FontSize', false, '8pt') },
                            { type: 'menuitem', text: '9pt', onAction: () => editor.execCommand('FontSize', false, '9pt') },
                            { type: 'menuitem', text: '10pt', onAction: () => editor.execCommand('FontSize', false, '10pt') },
                            { type: 'menuitem', text: '11pt', onAction: () => editor.execCommand('FontSize', false, '11pt') },
                            { type: 'menuitem', text: '12pt', onAction: () => editor.execCommand('FontSize', false, '12pt') },
                            { type: 'menuitem', text: '14pt', onAction: () => editor.execCommand('FontSize', false, '14pt') },
                            { type: 'menuitem', text: '16pt', onAction: () => editor.execCommand('FontSize', false, '16pt') },
                            { type: 'menuitem', text: '18pt', onAction: () => editor.execCommand('FontSize', false, '18pt') },
                            { type: 'menuitem', text: '24pt', onAction: () => editor.execCommand('FontSize', false, '24pt') },
                        ]);
                    }
                });

                // --- 기존 이미지 관련 및 붙여넣기 차단 등 ---
                editor.on('SetContent', function(e) {
                    setTimeout(function() {
                        const imgs = editor.dom.select('img');
                        if (imgs.length > 0) {
                            const img = imgs[imgs.length - 1];
                            let next = img.nextSibling;
                            if (!next || (next.nodeName !== 'BR' && next.nodeName !== 'P')) {
                                editor.dom.insertAfter(editor.dom.create('br'), img);
                            }
                            const rng = editor.dom.createRng();
                            rng.setStartAfter(img);
                            rng.setEndAfter(img);
                            editor.selection.setRng(rng);
                        }
                    }, 10);
                });

                editor.on('drop', function(e) {
                    if (e.dataTransfer && e.dataTransfer.files?.length > 0) {
                        for (let i = 0; i < e.dataTransfer.files.length; i++) {
                            if (e.dataTransfer.files[i].type.startsWith('image/')) {
                                e.preventDefault();
                                e.stopPropagation();
                                editor.notificationManager.open({ text: '드래그앤드롭 이미지 첨부는 불가합니다.', type: 'warning' });
                                return false;
                            }
                        }
                    }
                });

                editor.on('paste', function(e) {
                    if (e.clipboardData?.items) {
                        for (let i = 0; i < e.clipboardData.items.length; i++) {
                            if (e.clipboardData.items[i].type.startsWith('image/')) {
                                e.preventDefault();
                                editor.notificationManager.open({ text: '이미지 붙여넣기는 지원하지 않습니다.', type: 'warning' });
                                return false;
                            }
                        }
                    }
                });
            },

            images_upload_url: G5_EDITOR_URL + '/editor.upload.php',
            file_picker_types: 'image',
            automatic_uploads: true,

            file_picker_callback: function(callback, value, meta) {
                if (meta.filetype === 'image') {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = 'image/*';
                    input.onchange = function() {
                        const file = this.files[0];
                        const reader = new FileReader();
                        reader.onload = function() {
                            const id = 'blobid' + (new Date()).getTime();
                            const blobCache = tinymce.activeEditor.editorUpload.blobCache;
                            const base64 = reader.result.split(',')[1];
                            const blobInfo = blobCache.create(id, file, base64);
                            blobCache.add(blobInfo);
                            callback(blobInfo.blobUri(), { title: file.name });
                        };
                        reader.readAsDataURL(file);
                    };
                    input.click();
                }
            },

            images_upload_handler: function(blobInfo, progress) {
                return new Promise(function(resolve, reject) {
                    const imgCount = tinymce.activeEditor.dom.select('img').length;
                    if (imgCount >= 9) {
                        tinymce.activeEditor.notificationManager.open({
                            text: '이미지는 최대 10개까지만 첨부할 수 있습니다.',
                            type: 'warning'
                        });
                        reject('이미지 10개 제한');
                        return;
                    }
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', G5_EDITOR_URL + '/editor.upload.php');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                const json = JSON.parse(xhr.responseText);
                                let url = String(json.location || '').replace(/[%\s\r\n]+$/g, '');
                                if (url) resolve(url);
                                else reject('업로드 후 URL 없음');
                            } catch (err) {
                                reject('응답 파싱 실패');
                            }
                        } else {
                            reject('업로드 실패 (' + xhr.status + ')');
                        }
                    };
                    xhr.onerror = () => reject('업로드 오류');
                    const formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    formData.append('editor_type', 'tinymce');
                    xhr.send(formData);
                });
            }
        });
    }
}

// beforeunload에 저장 (기존 동일)
window.addEventListener("beforeunload", () => {
    const editor = window["wr_content_editor"];
    const el = document.getElementById("wr_content");
    if (editor && el) {
        el.value = editor.getData();
    }
});
