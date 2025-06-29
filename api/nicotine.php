<?php
include_once('../common.php');
include_once(G5_PATH.'/_head.php');
?>
<style>
  .nicocalc-layout {
    margin: 60px auto;
    max-width: 960px;
    padding: 0 20px;
  }
  .nicocalc-panel {
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    padding: 30px;
    width: 100%;
    box-sizing: border-box;
  }
  .nicocalc-panel label {
    font-size: 13px;
    display: block;
    margin-top: 16px;
    margin-bottom: 4px;
  }
  .nicocalc-panel input {
    width: 100%;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-bottom: 8px;
    background: #ffffff;
    color: #000000;
    padding: 8px;
    box-sizing: border-box;
  }
  .nicocalc-panel button {
    width: 100%;
    margin-top: 12px;
    padding: 10px;
    background-color: #225577;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
  }
  .nicocalc-panel button:hover {
    background-color: #113355;
  }
  .nicocalc-result {
    margin-top: 20px;
    padding: 16px;
    text-align: center;
    font-size: 18px;
    background: #f5f8fa;
    border-radius: 6px;
    font-weight: bold;
  }
  .nicocalc-bar-container {
    margin-top: 20px;
    height: 30px;
    background: #ddd;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
    position: relative;
    font-family: sans-serif;
    flex-wrap: nowrap;
  }
  .nicocalc-bar-nic {
    background: #f1c40f;
    height: 100%;
    transition: width 0.3s;
    position: relative;
    display: flex;
    align-items: center;
  }
  .nicocalc-bar-base {
    background: #88aacc;
    height: 100%;
    transition: width 0.3s;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }
  .nicocalc-bar-label {
    font-size: 12px;
    font-weight: bold;
    color: #000;
    padding: 0 6px;
    white-space: nowrap;
    background: rgba(255,255,255,0.85);
    border-radius: 4px;
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

  @media (max-width: 600px) {
    .nicocalc-layout {
      padding: 0 10px;
    }
    .nicocalc-panel {
      padding: 20px;
    }
    .nicocalc-bar-label {
      font-size: 10px;
      padding: 0 4px;
    }
    .nicocalc-result {
      font-size: 16px;
    }
  }
</style>
<div class="nicocalc-layout">
  <div class="nicocalc-panel">
    <h1>니코틴 계산기</h1>
    <label>목표 액상 용량 (ml)</label>
    <input type="number" id="totalVolume" value="60" oninput="calcNicotine()">

    <label>목표 니코틴 농도 (mg/ml)</label>
    <input type="number" id="targetStrength" value="6" oninput="calcNicotine()">

    <label>희석 니코틴 농도 (mg/ml)</label>
    <input type="number" id="baseStrength" value="250" oninput="calcNicotine()">

    <div class="nicocalc-result" id="nicotineResult">필요 니코틴 용량: 0 ml<br>예상 투약: 0 방울</div>

    <div class="nicocalc-bar-container">
      <div class="nicocalc-bar-nic" id="barNic" style="width: 0%">
        <span class="nicocalc-bar-label" id="labelNic"></span>
      </div>
      <div class="nicocalc-bar-base" id="barBase" style="width: 100%">
        <span class="nicocalc-bar-label" id="labelBase"></span>
      </div>
    </div>

    <button onclick="resetNicoForm()">초기화</button>
    <button onclick="saveNicoSettings()">저장</button>
  </div>
</div>
<div id="toast">저장되었습니다!</div>
<script>
function calcNicotine() {
  const total = parseFloat(document.getElementById('totalVolume').value);
  const target = parseFloat(document.getElementById('targetStrength').value);
  const base = parseFloat(document.getElementById('baseStrength').value);
  if (!total || !target || !base || base <= 0) {
    document.getElementById('nicotineResult').textContent = '값을 입력해주세요';
    return;
  }
  const nicAmount = total * target / base;
  const drops = Math.round(nicAmount * 20);
  const baseAmount = total - nicAmount;
  const nicPercent = (nicAmount / total) * 100;
  const basePercent = 100 - nicPercent;

  document.getElementById('nicotineResult').innerHTML = `필요 니코틴 용량: ${nicAmount.toFixed(1)} ml<br>예상 투약: ${drops} 방울`;
  document.getElementById('barNic').style.width = nicPercent + '%';
  document.getElementById('barBase').style.width = basePercent + '%';

  const labelNic = document.getElementById('labelNic');
  const labelBase = document.getElementById('labelBase');

  labelNic.textContent = `${nicPercent.toFixed(1)}%`;
  labelBase.textContent = `${basePercent.toFixed(1)}%`;
}
function resetNicoForm() {
  document.getElementById('totalVolume').value = 60;
  document.getElementById('targetStrength').value = 6;
  document.getElementById('baseStrength').value = 250;
  calcNicotine();
}
function saveNicoSettings() {
  const settings = {
    totalVolume: document.getElementById('totalVolume').value,
    targetStrength: document.getElementById('targetStrength').value,
    baseStrength: document.getElementById('baseStrength').value
  };
  localStorage.setItem('nicotineSettings', JSON.stringify(settings));
  showToast('저장되었습니다!');
}
function loadNicoSettings() {
  const saved = localStorage.getItem('nicotineSettings');
  if (!saved) return;
  const s = JSON.parse(saved);
  document.getElementById('totalVolume').value = s.totalVolume;
  document.getElementById('targetStrength').value = s.targetStrength;
  document.getElementById('baseStrength').value = s.baseStrength;
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
  loadNicoSettings();
  calcNicotine();
};
</script>

<?php
include_once(G5_PATH.'/_tail.php');
