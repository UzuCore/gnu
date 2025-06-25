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
    config.extraPlugins = 'font,justify,colorbutton,specialchar,emoji,clipboard';
    config.removePlugins = 'uploadimage,exportpdf,copyformatting,preview,print,save,templates,about';

    config.removeButtons = 'Source,Cut,Copy,Paste,PasteText,Scayt,PasteFromWord,Subscript,Superscript,RemoveFormat,BulletedList,NumberedList,Outdent,Indent,Blockquote,Unlink,Anchor,Table,HorizontalRule,Styles,Format,Italic,About';

    config.font_names = '맑은 고딕/Malgun Gothic;굴림/Gulim;돋움/Dotum;바탕/Batang;궁서/Gungsuh;' +
                        'Arial;Comic Sans MS;Courier New;Georgia;Lucida Sans Unicode;Tahoma;Times New Roman;Trebuchet MS;Verdana';

    config.fontSize_sizes = '8pt;9pt;10pt;11pt;12pt;14pt;16pt;18pt;20pt;24pt';

    config.height = g5_is_mobile ? '220px' : '330px';

    let upload_url = "/plugin/editor/ckeditor422/editor.upload.php?type=Images";
    if (typeof(editor_id) !== "undefined") upload_url += "&editor_id=" + editor_id;
    if (typeof(editor_uri) !== "undefined") upload_url += "&editor_uri=" + editor_uri;
    if (typeof(editor_form_name) !== "undefined") upload_url += "&editor_form_name=" + editor_form_name;

    config.filebrowserImageUploadUrl = upload_url;

    if (g5_is_mobile) {
        config.toolbar = window.innerWidth > 414 ? [
			{ name: 'clipboard', items: ['Undo', 'Redo'] },
            { name: 'basicstyles', items: ['Bold', 'Underline', 'Strike'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'links', items: ['Link'] },
            { name: 'insert', items: ['Image', 'EmojiPanel', 'SpecialChar'] },
            { name: 'tools', items: ['Maximize'] }
        ] : [
			{ name: 'clipboard', items: ['Undo', 'Redo'] },
            { name: 'basicstyles', items: ['Bold', 'Underline', 'Strike'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'links', items: ['Link'] },
            '/',
            { name: 'insert', items: ['Image', 'EmojiPanel', 'SpecialChar'] },
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
            { name: 'insert', items: ['Image', 'EmojiPanel', 'SpecialChar'] },
            { name: 'tools', items: ['Maximize'] }
        ];
    }

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

    CKEDITOR.on('instanceReady', function(ev) {
        const editor = ev.editor;
        const MAX_IMAGES = 10;

        function checkImageLimit(evt, addedCount = 1) {
            const html = editor.getData();
            const matches = html.match(/<img\b[^>]*>/gi);
            const count = matches ? matches.length : 0;
            if (count + addedCount > MAX_IMAGES) {
                const message = count > 0
                    ? `이미지는 최대 ${MAX_IMAGES}장까지 등록 가능합니다. 현재 ${count}장 등록됨.`
                    : `이미지는 최대 ${MAX_IMAGES}장까지 등록 가능합니다.`;
                editor.showNotification(message, 'warning');
                if (evt) evt.cancel();
                return false;
            }
            return true;
        }

        function scrollToLastImage(editor) {
            try {
                const iframe = editor.container.$.querySelector('iframe');
                if (!iframe || !iframe.contentWindow) return;

                const doc = iframe.contentDocument || iframe.contentWindow.document;
                const images = doc.querySelectorAll('img');
                const last = images[images.length - 1];
                if (!last) return;

                const oldAnchor = doc.getElementById('scroll-anchor');
                if (oldAnchor) oldAnchor.remove();

                const anchor = doc.createElement('p');
                anchor.id = 'scroll-anchor';
                anchor.innerHTML = '&nbsp;';
                last.parentNode.insertBefore(anchor, last.nextSibling);

                if (iframe.contentWindow && iframe.contentWindow.scrollTo) {
                    const top = anchor.getBoundingClientRect().top + iframe.contentWindow.scrollY;
                    iframe.contentWindow.scrollTo({ top, behavior: 'smooth' });
                }

                const editorAnchor = editor.document.getById('scroll-anchor');
                if (editorAnchor) editorAnchor.remove();

                const newEl = new CKEDITOR.dom.element('p');
                newEl.setAttribute('id', 'scroll-anchor');
                newEl.setHtml('&nbsp;');

                editor.insertElement(newEl);

                const range = editor.createRange();
                range.moveToElementEditablePosition(newEl, true);
                editor.getSelection().selectRanges([range]);
            } catch (e) {
                console.warn('[스크롤 오류]', e);
            }
        }

        editor.addCommand('image', {
            exec: function(editor) {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.multiple = true;
                input.style.display = 'none';
                document.body.appendChild(input);

                input.addEventListener('change', function() {
                    const files = Array.from(input.files);

                    const html = editor.getData();
                    const currentImages = html.match(/<img\b[^>]*>/gi) || [];
                    const currentCount = currentImages.length;

                    if (currentCount + files.length > MAX_IMAGES) {
                        const message = currentCount > 0
                            ? `이미지는 최대 ${MAX_IMAGES}장까지 등록 가능합니다. 현재 ${currentCount}장 등록됨.`
                            : `이미지는 최대 ${MAX_IMAGES}장까지 등록 가능합니다.`;
                        editor.showNotification(message, 'warning');
                        document.body.removeChild(input);
                        return;
                    }

                    editor.showNotification('업로드를 시작합니다.', 'info');

                    let uploadedCount = 0;

                    files.forEach((file, index) => {
                        const formData = new FormData();
                        formData.append('upload', file);

                        fetch(upload_url + '&responseType=json', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.uploaded && data.url) {
                                const html = `<img src="${data.url}" style="max-width:100%;height:auto;">`;
                                editor.insertHtml(html);
                                editor.showNotification(`이미지 ${index + 1} 업로드 완료`, 'success');
                            } else {
                                editor.showNotification(data?.error?.message || `이미지 ${index + 1} 업로드 실패`, 'warning');
                            }
                        })
                        .catch(() => {
                            editor.showNotification(`이미지 ${index + 1} 업로드 중 오류`, 'warning');
                        })
                        .finally(() => {
                            uploadedCount++;
                            if (uploadedCount === files.length) {
                                setTimeout(() => scrollToLastImage(editor), 300);
                            }
                        });
                    });

                    document.body.removeChild(input);
                });

                input.click();
            }
        });

        editor.on('contentDom', function() {
            const editable = editor.editable();

            editable.attachListener(editable, 'paste', function(evt) {
                evt.cancel();
            });

            editable.attachListener(editable, 'drop', function(evt) {
                evt.cancel();
            });

            editable.attachListener(editable, 'dragover', function(evt) {
                evt.data.preventDefault(true);
            });

            const iframeDoc = editable.$.ownerDocument;

            if (iframeDoc) {
                ['paste', 'drop', 'dragover'].forEach(type => {
                    iframeDoc.addEventListener(type, function(e) {
                        if (e.clipboardData?.types.includes('Files') || e.dataTransfer?.types.includes('Files')) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    }, true);
                });
            }
        });
    });
};

window.addEventListener("beforeunload", function () {
    const editor = CKEDITOR.instances.wr_content;
    if (editor) {
        document.getElementById("wr_content").value = editor.getData();
    }
});