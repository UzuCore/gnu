<?php
include_once('../common.php');
include_once(G5_PATH.'/_head.php');
?>
<style>
  .coil-layout {
    margin: 60px auto;
    max-width: 960px;
    padding: 0 20px;
  }
  .coil-panel {
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    padding: 30px;
    width: 100%;
  }
  .coil-panel label {
    font-size: 13px;
    display: block;
    margin-top: 16px;
    margin-bottom: 4px;
  }
  .coil-panel select,
  .coil-panel input[type="range"] {
    width: 100%;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-bottom: 8px;
    background: #ffffff;
    color: #000000;
    padding: 8px;
  }
  .coil-slider-label {
    font-size: 13px;
    margin-top: 4px;
  }
  .coil-svg {
    width: 100%;
    height: 220px;
    margin-bottom: 10px;
    overflow: visible;
  }
  .coil-wrap-count {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #000000;
    text-align: center;
  }
  .coil-data {
    font-size: 13px;
    color: #333333;
    width: 100%;
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
  }
  .coil-result-box {
    background: #f5f8fa;
    color: #000000;
    border-radius: 6px;
    text-align: center;
    padding: 12px;
    font-size: 16px;
    margin-top: 20px;
  }
  .coil-panel button {
    width: 100%;
    margin-top: 20px;
    padding: 10px;
    background-color: #225577;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
  }
  .coil-panel button:hover {
    background-color: #113355;
  }
  .coil-wrapper {
    display: flex;
    flex-direction: column;
    gap: 40px;
  }
  @media (min-width: 992px) {
    .coil-wrapper {
      flex-direction: row;
      align-items: flex-start;
    }
  }
  .coil-visual {
    order: -1;
  }
  @media (min-width: 768px) {
    .coil-visual {
      order: initial;
    }
  }
  #toast {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    background: #1c5ea8;
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    font-size: 14px;
    display: none;
    z-index: 9999;
  }
</style>
<div class="coil-layout">
  <div class="coil-panel">
    <h1>코일 계산기</h1>
    <div class="coil-wrapper">
      <div style="flex: 1;">
        <label for="wireType">코일 종류</label>
        <select id="wireType" onchange="updateDisplay()">
          <option value="1.45">Kanthal A1</option>
          <option value="1.10">NiChrome 80</option>
          <option value="0.94">Stainless 316L</option>
        </select>
        <label for="awg">AWG (두께)</label>
        <input type="range" id="awg" min="24" max="28" step="1" value="28" oninput="updateDisplay()">
        <div class="coil-slider-label"> <span id="val-awg">28</span></div>
        <label for="legLength">다리 길이</label>
        <input type="range" id="legLength" min="1" max="10" step="0.1" value="5" oninput="updateDisplay()">
        <div class="coil-slider-label"><span id="val-legLength">5</span> mm</div>
        <label for="targetResistance">목표 저항값</label>
        <input type="range" id="targetResistance" min="0.1" max="3.0" step="0.01" value="1.1" oninput="updateDisplay()">
        <div class="coil-slider-label"><span id="val-targetResistance">1.1</span> Ω</div>
        <button onclick="resetForm()">초기화</button>
        <button onclick="saveSettings()">저장</button>
      </div>
      <div style="width:1px; background:#ccc; height:auto;"></div>
      <div class="coil-visual" style="flex: 1;">
        <svg class="coil-svg" viewBox="0 0 240 240">
          <g id="coilGroup"></g>
        </svg>
        <div class="coil-wrap-count"><span id="coilWrap">7</span> 바퀴</div>
        <div class="coil-data">
          <div>와이어 길이<br><span id="coilLength">65</span> mm</div>
          <div>예상 저항값<br><span id="resistance">1.16</span> Ω</div>
        </div>
        <div class="coil-result-box" id="finalResult">1.16 Ω</div>
      </div>
    </div>
  </div>
</div>
<div id="toast">저장되었습니다!</div>
<script>
function updateDisplay() {
  const awg = parseInt(document.getElementById('awg').value);
  const leg = parseFloat(document.getElementById('legLength').value);
  const targetRes = parseFloat(document.getElementById('targetResistance').value);
  const resistivity = parseFloat(document.getElementById('wireType').value);
  document.getElementById('val-awg').textContent = awg;
  document.getElementById('val-legLength').textContent = leg;
  document.getElementById('val-targetResistance').textContent = targetRes;
  const diameter = {28: 0.321, 27: 0.361, 26: 0.405, 25: 0.455, 24: 0.511}[awg] || 0.321;
  const radius = diameter / 2;
  const area = Math.PI * Math.pow(radius, 2);
  let turns = 3, resultRes = 0, length = 0;
  for (let i = 3; i <= 20; i++) {
    const coilLength = i * Math.PI * 2.5;
    const totalLength = coilLength + leg * 2;
    const r = resistivity * totalLength / 1000 / area;
    if (r >= targetRes) {
      turns = i;
      resultRes = r;
      length = totalLength;
      break;
    }
  }
  document.getElementById('coilWrap').textContent = turns;
  document.getElementById('coilLength').textContent = length.toFixed(0);
  document.getElementById('resistance').textContent = resultRes.toFixed(2);
  document.getElementById('finalResult').textContent = resultRes.toFixed(2) + ' Ω';
  const group = document.getElementById('coilGroup');
  group.innerHTML = '';
  const spacing = 12, dx = 14, dy = 45, baseY = 120;
  const startX = 10 + (turns - 1) * spacing;
  for (let i = 0; i < turns; i++) {
    const x1 = startX - i * spacing;
    const y1 = baseY;
    const x2 = x1 - dx;
    const y2 = baseY + dy;
    const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    line.setAttribute('x1', x1);
    line.setAttribute('y1', y1);
    line.setAttribute('x2', x2);
    line.setAttribute('y2', y2);
    line.setAttribute('stroke', '#000000');
    line.setAttribute('stroke-width', '6');
    group.appendChild(line);
  }
  const lastX = startX - (turns - 1) * spacing - dx;
  const outputLeg = document.createElementNS('http://www.w3.org/2000/svg', 'line');
  outputLeg.setAttribute('x1', lastX);
  outputLeg.setAttribute('y1', baseY + dy);
  outputLeg.setAttribute('x2', lastX - 5);
  outputLeg.setAttribute('y2', baseY - 20);
  outputLeg.setAttribute('stroke', '#000000');
  outputLeg.setAttribute('stroke-width', '6');
  group.appendChild(outputLeg);
}
function resetForm() {
  document.getElementById('wireType').value = '1.45';
  document.getElementById('awg').value = 28;
  document.getElementById('legLength').value = 5;
  document.getElementById('targetResistance').value = 1.1;
  updateDisplay();
}
function saveSettings() {
  const settings = {
    wireType: document.getElementById('wireType').value,
    awg: document.getElementById('awg').value,
    legLength: document.getElementById('legLength').value,
    targetResistance: document.getElementById('targetResistance').value
  };
  localStorage.setItem('coilSettings', JSON.stringify(settings));
  showToast('저장되었습니다!');
}
function loadSettings() {
  const saved = localStorage.getItem('coilSettings');
  if (!saved) return;
  const s = JSON.parse(saved);
  document.getElementById('wireType').value = s.wireType;
  document.getElementById('awg').value = s.awg;
  document.getElementById('legLength').value = s.legLength;
  document.getElementById('targetResistance').value = s.targetResistance;
}
function showToast(message) {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.style.display = 'block';
  setTimeout(() => {
    toast.style.display = 'none';
  }, 2000);
}
window.onload = () => {
  loadSettings();
  updateDisplay();
};
</script>
<?php
include_once(G5_PATH.'/_tail.php');
?>