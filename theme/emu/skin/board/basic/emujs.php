<?php
$sysRatio = [
    'psp' => '16/9',
    'gb' => '10/9',
    'gba' => '3/2', 
    'nds' => '21/8'
];
$sysRatio = $sysRatio[$selCore] ?? "4/3";
//$sysRatio = $view['wr_3'] ?? $sysRatio;
?>
<style>
.ejs_parent {
  width: 750px;
  aspect-ratio: <?php echo $sysRatio?>;
  margin-bottom:15px;
}
.ejs_parent canvas {
  width: 100%;
  height: auto;
}

.ejs_start_button {
    text-shadow: none;
    font-size: 1.2em;
    font-weight: normal;
    color: #fff !important;
    border-radius: 22px;
}


.ejs_start_button:active,
.ejs_start_button:hover {
    background-color: #fff;
    color: #000 !important;
    animation: none;
}
</style>

<div id='game'></div>

<script>
if (selCa == "SMS")
    mdOptions = {}
else
    mdOptions = {
        "genesis_plus_gx_aspect_ratio": "4:3",
        "genesis_plus_gx_overscan": "disabled",
        "picodrive_aspect": "4/3",
        "picodrive_overscan": "disabled"
    }

nesOptions = {
    "fceumm_aspect": "4:3",
    "fceumm_overscan_h_left": "0",
    "fceumm_overscan_h_right": "0",
    "fceumm_overscan_v_top": "0",
    "fceumm_overscan_v_bottom": "0",
    "nestopia_aspect": "4:3",
    "nestopia_overscan_v_top": "0",
    "nestopia_overscan_v_bottom": "0",
    "nestopia_overscan_h_left": "0",
    "nestopia_overscan_h_right": "0"
}

fbnOptions = {
    "fbneo-neogeo-mode": "UNIBIOS"
}

mame2003pOptions = {
    "mame2003-plus_skip_disclaimer": "enabled",
    "mame2003-plus_skip_warnings": "enabled"
}

const coreOptions = {
    "segaMD": mdOptions,
    "genesis_plus_gx": mdOptions,
    "picodrive": mdOptions,
    "nes": nesOptions,
    "fceumm": nesOptions,
    "nestopia": nesOptions,
    "fbneo": fbnOptions,
    "mame2003_plus" : mame2003pOptions,
    "nds": {
        "melonds_screen_layout": "Left/Right"
    }
};

// EmulatorJS 설정
EJS_DEBUG_XX = true;
EJS_threads = true;
EJS_player = "#game";
EJS_core = "<?php echo $selCore?>";
if (selParent)
    EJS_gameParentUrl = "/api/" + selParent + ".zip";
EJS_gameUrl = "<?php echo $gameUrl?>";
EJS_pathtodata = "/emujs/";
EJS_language = "ko-KO";
EJS_gameTitle = "<?php echo $view["wr_subject"]?>";
EJS_startOnLoaded = false;
EJS_alignStartButton = "center";
EJS_color = "#D52A22";
EJS_startButtonName = "> 게임을 시작하려면 클릭하세요 <";
EJS_defaultOptions = {
    "save-state-location": "browser",
    "input_menu_toggle": "escape",
    ...coreOptions[EJS_core]
}
if (selCa == "NEOGEO")
    EJS_biosUrl = '/api/neogeo.zip';

// 스크린샷 서버 업로드 함수
async function uploadScreenshotAndUpdatePost(blob) {
    // 간단한 로딩 표시
    const loading = document.createElement('div');
    loading.innerHTML = '스크린샷 업로드 중...';
    loading.style.cssText = 'position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(0,0,0,0.8); color:white; padding:20px; border-radius:8px; z-index:10000;';
    document.body.appendChild(loading);
    
    try {
        const formData = new FormData();
        formData.append('screenshot', blob, 'screenshot.png');
        formData.append('bo_table', '<?php echo $bo_table; ?>');
        formData.append('wr_id', '<?php echo $wr_id; ?>');
        
        const response = await fetch('/api/screenshots/', {
            method: 'POST',
            body: formData,
            signal: AbortSignal.timeout(30000) // 30초 타임아웃
        });
        
        document.body.removeChild(loading);
        
        if (!response.ok) {
            throw new Error(`업로드 실패 (${response.status})`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            alert('스크린샷이 업로드되었습니다.');
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(result.message || '업로드 실패');
        }
        
    } catch (error) {
        if (document.body.contains(loading)) {
            document.body.removeChild(loading);
        }
        
        console.error('업로드 실패:', error);
        alert('업로드 실패: ' + error.message);
    }
}

// 스크린샷 함수
async function takeScreenshotLowVersion() {
    try {
        // 게임 매니저가 준비되었는지 확인
        if (!window.EJS_emulator || !window.EJS_emulator.gameManager) {
            throw new Error('에뮬레이터가 아직 준비되지 않았습니다.');
        }
        
        // 스크린샷 촬영 (낮은 버전 방식)
        const screenshotData = await window.EJS_emulator.gameManager.screenshot();
        
        // DOS 코어인 경우 4/3 비율로 강제 변환
        if (EJS_core === 'dos') {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            const originalBlob = new Blob([screenshotData], { type: 'image/png' });
            const imageUrl = URL.createObjectURL(originalBlob);
            
            await new Promise((resolve) => {
                img.onload = () => {
                    const ratio = 4/3;
                    let width = img.width;
                    let height = img.height;
                    
                    if (width / height > ratio) {
                        width = Math.round(height * ratio);
                    } else {
                        height = Math.round(width / ratio);
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    canvas.toBlob((resizedBlob) => {
                        blob = resizedBlob;
                        URL.revokeObjectURL(imageUrl);
                        resolve();
                    }, 'image/png');
                };
                img.src = imageUrl;
            });
        } else {
            blob = new Blob([screenshotData], { type: 'image/png' });
        }
        
        const date = new Date();
        const fileName = window.EJS_emulator.getBaseFileName() + "-" + 
                        date.getMonth() + "-" + date.getDate() + "-" + date.getFullYear();
        
        if (g5_is_admin) {
            // 관리자인 경우: 서버에 업로드하고 게시물 업데이트
            if (confirm('스크린샷을 서버에 업로드하고 게시물을 업데이트하시겠습니까?')) {
                await uploadScreenshotAndUpdatePost(blob);
            } else {
                // 취소 시 일반 다운로드
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName + ".png";
                a.click();
                URL.revokeObjectURL(url);
            }
        } else {
            // 일반 사용자인 경우: 다운로드만
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = fileName + ".png";
            a.click();
            URL.revokeObjectURL(url);
        }
        
    } catch (error) {
        console.error('스크린샷 촬영 실패:', error);
        alert('스크린샷 촬영에 실패했습니다: ' + error.message);
    }
}

// 커스텀 버튼 설정
EJS_Buttons = {
    saveSavFiles: false,
    loadSavFiles: false,
    contextMenu: false,
    customDownload: {
        visible: true,
        displayName: "customDownload",
        icon: '<svg viewBox="0 0 512 512" fill="currentColor"><path d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V274.7l-73.4-73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L288 274.7V32zM64 352c-35.3 0-64 28.7-64 64v32c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V416c0-35.3-28.7-64-64-64H346.5l-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352H64zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg>',
        callback: async () => {
            let state;
            try {
                state = window.EJS_emulator.gameManager.getState();
            } catch(e) {
                console.error('상태 저장 실패:', e);
                return;
            }
            const blob = new Blob([state]);
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = window.EJS_emulator.getBaseFileName() + ".state";
            a.click();
            URL.revokeObjectURL(url);
        }
    },
    customUpload: {
        visible: true,
        displayName: "customUpload", 
        icon: '<svg viewBox="0 0 512 512" fill="currentColor"><path d="M288 109.3V352c0 17.7-14.3 32-32-32s-32-14.3-32-32V109.3l-73.4 73.4c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l128-128c12.5-12.5 32.8-12.5 45.3 0l128 128c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L288 109.3zM64 352c-35.3 0-64 28.7-64 64v32c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V416c0-35.3-28.7-64-64-64H346.5l-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352H64zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg>',
        callback: async () => {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.state';
            input.onchange = async (e) => {
                const file = e.target.files[0];
                if (file) {
                    const state = new Uint8Array(await file.arrayBuffer());
                    window.EJS_emulator.gameManager.loadState(state);
                }
            };
            input.click();
        }
    },
    customScreenshot: {
        visible: true,
        displayName: "customScreenshot",
        icon: '<svg viewBox="0 0 512 512" fill="currentColor"><path d="M149.1 64.8L138.7 96H64C28.7 96 0 124.7 0 160V416c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V160c0-35.3-28.7-64-64-64H373.3L362.9 64.8C356.4 45.2 338.1 32 317.4 32H194.6c-20.7 0-39 13.2-45.5 32.8zM256 192a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"/></svg>',
        callback: takeScreenshotLowVersion
    }
};

// 캔버스 자동 포커스
EJS_onGameStart = function(e) {
    const canvas = document.querySelector(".ejs_canvas");
    if (canvas) {
        canvas.tabIndex = 1;
        canvas.focus();
    }
}

/*
EJS_defaultControls = {
    0: { // Player 1
        0: { 'value': 'x', 'value2': 'BUTTON_2' },        // X버튼
        1: { 'value': 's', 'value2': 'BUTTON_4' },        // Y버튼  
        2: { 'value': 'v', 'value2': 'SELECT' },          // Select
        3: { 'value': 'enter', 'value2': 'START' },       // Start
        4: { 'value': 'up arrow', 'value2': 'DPAD_UP' },  // 방향키 위
        5: { 'value': 'down arrow', 'value2': 'DPAD_DOWN' }, // 방향키 아래
        6: { 'value': 'left arrow', 'value2': 'DPAD_LEFT' }, // 방향키 왼쪽
        7: { 'value': 'right arrow', 'value2': 'DPAD_RIGHT' }, // 방향키 오른쪽
        8: { 'value': 'z', 'value2': 'BUTTON_1' },        // A버튼
        9: { 'value': 'a', 'value2': 'BUTTON_3' },        // B버튼
        10: { 'value': 'q', 'value2': 'LEFT_TOP_SHOULDER' }, // L1
        11: { 'value': 'w', 'value2': 'RIGHT_TOP_SHOULDER' }, // R1 (E->W로 변경)
        12: { 'value': '1', 'value2': 'LEFT_BOTTOM_SHOULDER' }, // L2 (tab->1로 변경)
        13: { 'value': '2', 'value2': 'RIGHT_BOTTOM_SHOULDER' }, // R2 (r->2로 변경)
        14: { 'value': '', 'value2': 'LEFT_STICK' },
        15: { 'value': '', 'value2': 'RIGHT_STICK' },
        16: { 'value': 'd', 'value2': 'LEFT_STICK_X:+1' }, // 왼쪽 스틱 우측 (h->d로 변경)
        17: { 'value': 'f', 'value2': 'LEFT_STICK_X:-1' }, // 왼쪽 스틱 좌측
        18: { 'value': 'g', 'value2': 'LEFT_STICK_Y:+1' }, // 왼쪽 스틱 아래
        19: { 'value': 'r', 'value2': 'LEFT_STICK_Y:-1' }, // 왼쪽 스틱 위 (t->r로 변경)
        20: { 'value': 'l', 'value2': 'RIGHT_STICK_X:+1' }, // 오른쪽 스틱 우측
        21: { 'value': 'j', 'value2': 'RIGHT_STICK_X:-1' }, // 오른쪽 스틱 좌측
        22: { 'value': 'k', 'value2': 'RIGHT_STICK_Y:+1' }, // 오른쪽 스틱 아래
        23: { 'value': 'i', 'value2': 'RIGHT_STICK_Y:-1' }, // 오른쪽 스틱 위
        24: { 'value': 'escape' }, // ESC키로 RetroArch 메뉴 호출
        25: { 'value': 'f1' },     // F1키 (보조)
        26: { 'value': 'f2' },     // F2키
        27: { 'value': 'f12' },    // F12키 (종료)
        28: { 'value': 'space' },  // 스페이스바
        29: { 'value': 'c' },      // C키
    },
    1: {}, // Player 2
    2: {}, // Player 3  
    3: {}  // Player 4
};
*/
</script>
<script src='<?php echo G5_URL; ?>/emujs/loader.js'></script>