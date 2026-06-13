<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/head.php');
    return;
}

if(G5_COMMUNITY_USE === false) {
    define('G5_IS_COMMUNITY_PAGE', true);
    include_once(G5_THEME_SHOP_PATH.'/shop.head.php');
    return;
}
include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');

function getCoreTempsOnlyFromSensors() {
    $output = [];
    exec("sudo /usr/bin/sensors", $output);  // 전체 출력 받아오기

    $temps = [];
    foreach ($output as $line) {
        // Core 온도 추출
        if (preg_match('/Core\s+(\d+):\s+\+([\d\.]+)°C/', $line, $matches)) {
            $core = $matches[1];
            $temp = $matches[2];
            $temps["Core {$core}"] = "{$temp}°C";
        }

        // Package 온도도 같이 보여주고 싶다면:
        if (preg_match('/Package id 0:\s+\+([\d\.]+)°C/', $line, $matches)) {
            $temps["Package"] = "{$matches[1]}°C";
        }
    }

    return $temps;
}

function getLoadAverage() {
    $load = sys_getloadavg();
    return [
        '1min' => $load[0],
        '5min' => $load[1],
        '15min' => $load[2],
    ];
}


?>

<!-- 상단 시작 { -->
<div id="hd">
    <h1 id="hd_h1"><?php echo $g5['title'] ?></h1>
    <div id="skip_to_container"><a href="#container">본문 바로가기</a></div>

    <?php
    if(defined('_INDEX_')) { // index에서만 실행
        include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
    }
    ?>
    <div id="hd_wrapper">

        <div id="logo">
            <a href="<?php echo G5_URL ?>"><img src="<?php echo G5_IMG_URL ?>/emu.png" alt="<?php echo $config['cf_title']; ?>"></a>
        </div>
    
        <div class="hd_sch_wr">
            <fieldset id="hd_sch">
                <legend>사이트 내 전체검색</legend>
                <form name="fsearchbox" method="get" action="<?php echo G5_URL ?>/search.php" onsubmit="return fsearchbox_submit(this);">
                <input type="hidden" name="sfl" value="wr_subject||wr_content">
                <input type="hidden" name="sop" value="and">
                <label for="sch_stx" class="sound_only">검색어 필수</label>
                <input type="text" name="stx" id="sch_stx" maxlength="20" placeholder="검색어를 입력해주세요">
                <button type="submit" id="sch_submit" value="검색"><i class="ri-search-line"></i><span class="sound_only">검색</span></button>
                </form>
            </fieldset>
        </div>
        <ul class="hd_login">
            <li><a href="<?php echo G5_BBS_URL ?>/new.php">전체글</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/current_connect.php" class="visit">온라인 <?php echo connect('theme/basic');?></a></li>
            <li><a href="javascript:void(0);" id="toggle-dark" aria-pressed="false"><span class="dark-icon">＊</span><span class="dark-label">다크모드</span></a></li>
            <?php if ($is_member) {  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php">회원정보</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></li>
            <?php } else {  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/login.php">로그인</a></li>
            <?php }  ?>
        </ul>
    </div>
    
    <nav id="gnb">
        <h2>메인메뉴</h2>
        <div class="gnb_wrap">
            <ul id="gnb_1dul">
                <li class="gnb_1dli gnb_mnal" style='line-height:55px;'>
                    <?php
echo "<div class='gnb_1da' style='float:right'>💻 서버 부하율 (Load Average) ";
$load = getLoadAverage();
echo number_format($load['1min'], 3);
echo "</div>";

echo "<div class='gnb_1da' style='float:right'>🌡️ CPU 코어 온도 (Sensors) ";
$temps = getCoreTempsOnlyFromSensors();
if (empty($temps)) {
    echo "❌ sensors 데이터를 읽을 수 없습니다.";
} else {
    echo $temps['Package'];
}
echo "</div>";
?>
                </li>
                <?php
				$menu_datas = get_menu_db(0, true);
				$gnb_zindex = 999; // gnb_1dli z-index 값 설정용
                $i = 0;
                foreach( $menu_datas as $row ){
                    if( empty($row) ) continue;
                    $add_class = (isset($row['sub']) && $row['sub']) ? 'gnb_al_li_plus' : '';
                ?>
                <li class="gnb_1dli <?php echo $add_class; ?>" style="z-index:<?php echo $gnb_zindex--; ?>">
                    <a href="<?php echo $row['me_link']; ?>" target="_<?php echo $row['me_target']; ?>" class="gnb_1da"><?php echo $row['me_name'] ?></a>
                    <?php
                    $k = 0;
                    foreach( (array) $row['sub'] as $row2 ){

                        if( empty($row2) ) continue; 

                        if($k == 0)
                            echo '<span class="bg">하위분류</span><div class="gnb_2dul"><ul class="gnb_2dul_box">'.PHP_EOL;
                    ?>
                        <li class="gnb_2dli"><a href="<?php echo $row2['me_link']; ?>" target="_<?php echo $row2['me_target']; ?>" class="gnb_2da"><?php echo $row2['me_name'] ?></a></li>
                    <?php
                    $k++;
                    }   //end foreach $row2

                    if($k > 0)
                        echo '</ul></div>'.PHP_EOL;
                    ?>
                </li>
                <?php
                $i++;
                }   //end foreach $row

                if ($i == 0) {  ?>
                    <li class="gnb_empty">메뉴 준비 중입니다.<?php if ($is_admin) { ?> <a href="<?php echo G5_ADMIN_URL; ?>/menu_list.php">관리자모드 &gt; 환경설정 &gt; 메뉴설정</a>에서 설정하실 수 있습니다.<?php } ?></li>
                <?php } ?>
            </ul>
        </div>
    </nav>
    <script>
    (function () {
        const mql = window.matchMedia('(prefers-color-scheme: dark)');

        // 적용할 다크 여부 판단: 사용자가 직접 고른 값(localStorage) 우선, 없으면 OS 설정 따름
        function shouldBeDark() {
            const saved = localStorage.getItem('theme');
            if (saved === 'dark')  return true;
            if (saved === 'light') return false;
            return mql.matches; // 저장값 없음 → OS 다크모드 설정 자동 인식
        }

        // 다크모드 적용 + 토글 버튼 상태 동기화
        function applyDark(isDark) {
            document.body.classList.toggle('dark-mode', isDark);
            const btn = document.getElementById('toggle-dark');
            if (btn) {
                btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                const label = btn.querySelector('.dark-label');
                if (label) label.textContent = isDark ? '라이트모드' : '다크모드';
            }
        }

        // 즉시 적용 (이 스크립트는 body 내부에 있어 document.body 접근 가능 → 깜빡임 최소화)
        applyDark(shouldBeDark());

        // 토글 버튼 클릭 → 수동 선택 저장 + 적용
        const btn = document.getElementById('toggle-dark');
        if (btn) btn.addEventListener('click', function () {
            const isDark = !document.body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            applyDark(isDark);

            // wr_content textarea가 숨김 상태면 새로고침 (에디터 재적용)
            const wrContent = document.querySelector('[name="wr_content"]');
            if (wrContent) {
                const style = window.getComputedStyle(wrContent);
                if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
                    location.reload();
                }
            }
        });

        // OS 다크모드 설정이 바뀌면 실시간 반영 (사용자가 수동 선택한 적 없을 때만)
        mql.addEventListener('change', function (e) {
            if (localStorage.getItem('theme') === null) applyDark(e.matches);
        });
    })();
    </script>
</div>
<!-- } 상단 끝 -->


<hr>

<!-- 콘텐츠 시작 { -->
<div id="wrapper">
    <div id="container_wr">
   
    <div id="container">
        <?php if (!defined("_INDEX_")) { ?><h2 id="container_title"><span title="<?php echo get_text($g5['title']); ?>"><?php echo get_head_title($g5['title']); ?></span></h2><?php }