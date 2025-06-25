// ✅ TinyMCE 초기화 스크립트 (editor.init.js)
function initTinymce(selectorId) {
    if (window.tinymce) {
        tinymce.init({
            selector: '#' + selectorId,
            height: 500,
            menubar: 'file edit view insert format tools table help',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: [
                'undo redo | formatselect | fontselect fontsizeselect | bold italic underline strikethrough forecolor backcolor',
                'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table',
                'charmap insertdatetime | visualblocks code fullscreen | removeformat | help | emoji | pdfdownload'
            ],
            font_family_formats: '맑은 고딕=Malgun Gothic;굴림=Gulim;돋움=Dotum;바탕=Batang;궁서=Gungsuh;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier,monospace;Georgia=georgia,palatino;',
            fontsize_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt',
            content_style: `
                body { font-family:Malgun Gothic,Arial,sans-serif; font-size:14px; }
                img { width:100%!important; max-width:100%!important; height:auto!important; display:block; }
            `,
            branding: false,
            elementpath: false,
            resize: true,
            convert_urls: false,
            cache_suffix: '?v=1',
            language: 'ko_KR',
            language_url: G5_EDITOR_URL + '/tinymce/langs/ko_KR.js',
            promotion: false,
            paste_data_images: false, // base64 이미지 붙여넣기 기본차단

            // ✅ 이벤트 핸들러/차단 등 setup에서 통합 관리
            setup: function(editor) {
                // 1) 이미지 삽입 후 커서를 이미지 아래로 자동 이동
                editor.on('SetContent', function(e) {
                    setTimeout(function() {
                        const imgs = editor.dom.select('img');
                        if (imgs.length > 0) {
                            const img = imgs[imgs.length - 1];
                            // 이미지 다음에 p/br 없으면 <br> 추가
                            let next = img.nextSibling;
                            if (!next || (next.nodeName !== 'BR' && next.nodeName !== 'P')) {
                                editor.dom.insertAfter(editor.dom.create('br'), img);
                            }
                            // 커서(캐럿)를 이미지 바로 뒤로 이동
                            const rng = editor.dom.createRng();
                            rng.setStartAfter(img);
                            rng.setEndAfter(img);
                            editor.selection.setRng(rng);
                        }
                    }, 10);
                });

                // 2) 드래그앤드롭 이미지 업로드 차단
                editor.on('drop', function(e) {
                    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
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

                // 3) 붙여넣기 이미지 차단
                editor.on('paste', function(e) {
                    if (e.clipboardData && e.clipboardData.items) {
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

            // ✅ 업로드 관련 설정
            images_upload_url: G5_EDITOR_URL + '/editor.upload.php',
            file_picker_types: 'image',
            automatic_uploads: true,

            // 파일 선택창에서 이미지 업로드(직접 선택)
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

            // Promise 기반 이미지 업로드 핸들러 (커스텀 서버)
            images_upload_handler: function(blobInfo, progress) {
				return new Promise(function(resolve, reject) {
					// 현재 에디터에 이미 삽입된 이미지 개수 카운트
					const imgCount = tinymce.activeEditor.dom.select('img').length;

					if (imgCount >= 9) {
						// 업로드 막고 경고
						tinymce.activeEditor.notificationManager.open({
							text: '이미지는 최대 10개까지만 첨부할 수 있습니다.',
							type: 'warning'
						});
						reject('이미지 10개 제한');
						return;
					}

					// 실제 업로드 진행
					const xhr = new XMLHttpRequest();
					xhr.open('POST', G5_EDITOR_URL + '/editor.upload.php');
					xhr.onload = function() {
						if (xhr.status === 200) {
							try {
								const json = JSON.parse(xhr.responseText);
								let url = json.location;
								url = String(url).replace(/[%\s\r\n]+$/g, '');
								if (url) {
									resolve(url);
								} else {
									reject('업로드 후 URL 없음');
								}
							} catch (err) {
								reject('응답 파싱 실패');
							}
						} else {
							reject('업로드 실패 (' + xhr.status + ')');
						}
					};
					xhr.onerror = function() {
						reject('업로드 오류');
					};
					const formData = new FormData();
					formData.append('file', blobInfo.blob(), blobInfo.filename());
					formData.append('editor_type', 'tinymce');
					xhr.send(formData);
				});
			}
        });
    }
}

window.addEventListener("beforeunload", () => {
    const editor = window["wr_content_editor"];
    const el = document.getElementById("wr_content");
    if (editor && el) {
        el.value = editor.getData();
    }
});