<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>스마트폰 그리드 아이콘 Drag&Drop 데모</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Sortable.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
  <style>
    body {
      background: #181f2c;
      font-family: 'Pretendard', 'Noto Sans KR', Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    h2 {
      color: #fff;
      text-align: center;
      margin-top: 28px;
    }
    .grid-container {
      display: grid;
      grid-template-columns: repeat(10, 60px);
      gap: 16px;
      justify-content: center;
      padding: 32px 0 32px 0;
    }
    .icon-item {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #7f99ff 0%, #2ec3fb 100%);
      border-radius: 20px;
      box-shadow: 0 2px 10px #2225  ;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 22px;
      cursor: grab;
      user-select: none;
      transition: box-shadow 0.13s, transform 0.15s;
      position: relative;
    }
    .icon-item:active {
      box-shadow: 0 6px 18px #2227;
      transform: scale(1.08);
    }
    .icon-label {
      position: absolute;
      left: 0; right: 0; bottom: 8px;
      font-size: 12px;
      color: #fff;
      opacity: 0.87;
      text-align: center;
      font-family: 'Pretendard', Arial, sans-serif;
      letter-spacing: -1px;
      pointer-events: none;
    }
    @media (max-width: 900px) {
      .grid-container { grid-template-columns: repeat(6, 56px); }
      .icon-item { width: 56px; height: 56px; font-size: 18px; }
    }
    @media (max-width: 640px) {
      .grid-container { grid-template-columns: repeat(4, 52px);}
      .icon-item { width: 52px; height: 52px; font-size: 17px; }
    }
    /* 아이콘 프리셋용 (이모지 등) */
    .icon-emoji { font-size: 30px; }
  </style>
</head>
<body>
  <h2>Drag & Drop - 스마트폰 그리드 아이콘 데모 (100개)</h2>
  <div id="iconGrid" class="grid-container">
    <!-- 아이콘 100개 -->
    <!-- JS로 자동 생성 -->
  </div>

  <script>
    // 아이콘/이모지 종류를 10개씩 순환 (ex: 앱, 폴더 등 느낌)
    const emojiSet = [
      "📁","📷","💬","🗂️","🎵","📺","🔔","🗓️","🌏","⚙️"
    ];
    // 100개 생성
    const grid = document.getElementById('iconGrid');
    for(let i=1; i<=100; i++){
      const iconDiv = document.createElement('div');
      iconDiv.className = 'icon-item';
      iconDiv.innerHTML = `<span class="icon-emoji">${emojiSet[(i-1)%emojiSet.length]}</span>
        <div class="icon-label">앱${i}</div>`;
      grid.appendChild(iconDiv);
    }

    // Sortable.js 활성화
    Sortable.create(grid, {
      animation: 170,
      delay: 0,
      ghostClass: 'icon-ghost',
      chosenClass: 'icon-chosen',
      dragClass: 'icon-drag',
      forceFallback: true,  // 모바일 터치 강제 지원
    });
  </script>
</body>
</html>
