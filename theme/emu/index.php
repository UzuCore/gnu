<?php
if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/index.php');
    return;
}

if(G5_COMMUNITY_USE === false) {
    include_once(G5_THEME_SHOP_PATH.'/index.php');
    return;
}

include_once(G5_THEME_PATH.'/head.php');
?>
<a class="bub-tooltip" data-tooltip="이게 툴팁입니다!">버튼</a>
<span class="bub-tooltip" data-tooltip="텍스트에도 OK!">텍스트</span>

<h2 class="sound_only">최신글</h2>

<div class="latest_top_wr">
    <?php
    // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
    // 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
    // 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
    echo "<div style=\"float:left;".$lt_style."\" class=\"lt_wr\">";
    echo latest('theme/basic', 'free', 4, 23);
    echo "</div>";
    echo "<div style=\"float:left;".$lt_style."\" class=\"lt_wr\">";
	echo latest('theme/basic', 'qa', 4, 23);
    echo "</div>";
	echo latest('theme/pic_list', 'roms', 4, 23);
    ?>
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');