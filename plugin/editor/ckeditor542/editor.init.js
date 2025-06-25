function showToast(message) {
    const toast = document.createElement("div");
    toast.className = "custom-toast";
    toast.innerText = message;
    toast.style.cssText = `
        position: fixed; bottom: 30px; right: 30px; background: rgba(0,0,0,0.85);
        color: white; padding: 12px 20px; border-radius: 6px; font-size: 14px;
        opacity: 0; transform: translateY(20px); transition: all 0.3s ease; z-index: 9999;
    `;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.style.opacity = "1");
    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function initCkeditor(id) {
    ClassicEditor.create(document.querySelector("#" + id), {
        language: "ko",
        mediaEmbed: {
            previewsInData: true,
            removeProviders: ["instagram", "twitter", "googleMaps", "flickr", "facebook"]
        },
        simpleUpload: {
            uploadUrl: "/plugin/editor/ckeditor542/editor.upload.php"
        },
        /*toolbar: {
            items: [
                'undo', 'redo', '|', 'heading', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', '|',
                'link', 'uploadImage', 'insertTable', 'mediaEmbed', '|',
                'codeBlock', 'horizontalLine', 'blockQuote', '|',
                'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                'findAndReplace', 'sourceEditing'
            ]
        }*/
    }).then(editor => {
        window[id + "_editor"] = editor;

        // ✅ data-content에서 본문을 안전하게 세팅
        const el = document.getElementById(id);
        const initial = el?.dataset?.content || '';
        if (initial) {
            requestAnimationFrame(() => {
                editor.setData(initial);
            });
        }

        // ✅ 이미지 개수 제한
        const MAX_IMAGES = 10;
        editor.model.document.on("change:data", () => {
            const images = Array.from(editor.model.document.getRoot().getChildren())
                .filter(child => child.is("element", "image"));
            if (images.length > MAX_IMAGES) {
                alert("이미지는 최대 " + MAX_IMAGES + "장까지 등록 가능합니다.");
                const last = images.pop();
                editor.model.change(writer => writer.remove(last));
            }
        });

        // ✅ 업로드 에러 처리
        const fileRepo = editor.plugins.get('FileRepository');
        fileRepo.on('uploadComplete', (evt, data) => {
            if (!data.response?.url) {
                const msg = data?.response?.error?.message || "❌ 업로드된 파일이 없거나 오류가 발생했습니다.";
                showToast(msg);
            }
        });
        fileRepo.on('uploadFailed', (evt, data) => {
            const msg = data?.error?.message || "❌ 업로드 실패";
            showToast(msg);
        });

    }).catch(error => {
        console.error("CKEditor 초기화 실패:", error);
    });
}

// ✅ 페이지 떠날 때 textarea에 내용 백업
window.addEventListener("beforeunload", () => {
    const editor = window["wr_content_editor"];
    const el = document.getElementById("wr_content");
    if (editor && el) {
        el.value = editor.getData();
    }
});
