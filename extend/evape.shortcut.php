<?php
if (!defined('_GNUBOARD_') || !defined('G5_THEME_PATH')) return;
//if (defined('G5_IS_ADMIN')) return;  // 관리자 제외
if (defined('G5_POPUP')) return;     // 팝업창 제외
if (defined('G5_AJAX')) return;      // ajax 응답 제외
if (defined('G5_IS_MOBILE')) return; // 모바일 제외

add_event('tail_sub', 'insert_keyboard_shortcut');

function insert_keyboard_shortcut() {
    ?>
    <script>
    (function(){
        function singleKeyRedirect(map) {
            document.addEventListener('keydown', function(e) {
                const tag = e.target.tagName.toLowerCase();
                if (tag === 'input' || tag === 'textarea' || e.target.isContentEditable) return;
                const key = e.key.toLowerCase();
                if (map[key]) {
                    e.preventDefault();
                    location.href = map[key];
                }
            });
        }
        singleKeyRedirect({
            'f': '/board.php?bo_table=free',
            'n': '/board.php?bo_table=notice'
        });
    })();
    </script>
    <?php
}
