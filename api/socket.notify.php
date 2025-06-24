<?php if (!defined('_GNUBOARD_')) exit;
if (!$is_member) return;
?>

<style>
.notify-container {
    position: fixed; bottom: 10px; right: 10px; z-index: 9999;
}
.notify-layer {
    width: 280px; background: #34495e; color: #fff;
    padding: 12px 15px; border-radius: 6px; margin-top: 10px;
    font-size: 13px; cursor: pointer; position: relative;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    opacity: 0; transform: translateX(100%);
    animation: slideIn 0.4s forwards;
}
.notify-layer.no-anim {
    animation: none !important; opacity: 1 !important; transform: translateX(0%) !important;
}
.notify-layer .close-btn {
    position: absolute; top: 4px; right: 8px;
    cursor: pointer; color: #aaa; font-size: 15px;
}
.notify-layer .close-btn:hover { color: #fff; }

.notify-button {
    position: fixed; bottom: 10px; left: 10px;
    background: #2ecc71; color: white; border: none;
    padding: 10px 15px; border-radius: 6px;
    font-size: 13px; cursor: pointer;
    z-index: 10000;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(100%); }
    to { opacity: 1; transform: translateX(0%); }
}
</style>

<button class="notify-button" onclick="window.open('<?php echo G5_URL; ?>/api/chat.php', 'chat_window', 'width=600,height=600,left=200,top=100,scrollbars=1');">💬 채팅</button>
<div class="notify-container"></div>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mb_id = "<?= $member['mb_id'] ?>";
    const nickname = "<?= addslashes($member['mb_nick']) ?>";
    const currentUrl = location.href.split('#')[0];
    const socket = io('wss://' + location.hostname + ':3000', { transports: ['websocket'] });

    let memoCount = parseInt(localStorage.getItem('memoCount') || '0');
    let commentStack = JSON.parse(localStorage.getItem('commentStack') || '[]');

    if (memoCount > 0) showMemoLayer(memoCount, true);
    commentStack.forEach(item => addCommentNotification(item.text, item.url, false, currentUrl === item.pageUrl));

    socket.on('connect', () => {
        socket.emit('register', { mb_id: mb_id, nickname: nickname });
    });

    socket.on('memo_alert', cnt => {
        memoCount += cnt;
        localStorage.setItem('memoCount', memoCount);
        showMemoLayer(memoCount, true);
    });

    socket.on('comment_alert', data => {
        const preview = data.text.length > 40 ? data.text.substr(0, 40) + '...' : data.text;
        const baseUrl = data.url.split('#')[0];
        const commentUrl = data.comment_id ? baseUrl + '#c_' + data.comment_id : baseUrl + '#comments';
        addCommentNotification('💬 ' + preview, commentUrl, true, true);
    });

    function showMemoLayer(count, animate){
        let existing = document.getElementById('memo-layer');
        if (existing) {
            existing.querySelector('.msg').textContent = '📩 새 쪽지가 (' + count + ')건 있습니다!';
        } else {
            const layer = document.createElement('div');
            layer.className = 'notify-layer';
            layer.id = 'memo-layer';
            if (!animate) layer.classList.add('no-anim');
            layer.innerHTML = '<span class="close-btn">&times;</span><span class="msg">📩 새 쪽지가 (' + count + ')건 있습니다!</span>';
            document.querySelector('.notify-container').appendChild(layer);
            layer.addEventListener('click', function() {
                window.open('<?php echo G5_BBS_URL; ?>/memo.php', 'win_memo', 'left=100,top=100,width=600,height=600,scrollbars=1');
                layer.remove();
                memoCount = 0;
                localStorage.removeItem('memoCount');
            });
            layer.querySelector('.close-btn').addEventListener('click', function(e) {
                e.stopPropagation();
                layer.remove();
                memoCount = 0;
                localStorage.removeItem('memoCount');
            });
        }
    }

    function addCommentNotification(msg, link, save, animate){
        const container = document.querySelector('.notify-container');
        const existing = container.querySelectorAll('.notify-layer:not(#memo-layer)');
        if (existing.length >= 3) {
            existing[0].remove();
            commentStack.shift();
        }
        const layer = document.createElement('div');
        layer.className = 'notify-layer';
        if (!animate) layer.classList.add('no-anim');
        layer.innerHTML = '<span class="close-btn">&times;</span>' + msg;
        container.appendChild(layer);
        layer.addEventListener('click', function() {
            commentStack = commentStack.filter(item => item.url !== link);
            localStorage.setItem('commentStack', JSON.stringify(commentStack));
            layer.remove();
            location.href = link;
        });
        layer.querySelector('.close-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            layer.remove();
            commentStack = commentStack.filter(item => item.url !== link);
            localStorage.setItem('commentStack', JSON.stringify(commentStack));
        });
        if (save) {
            commentStack.push({ text: msg, url: link, pageUrl: location.href.split('#')[0] });
            localStorage.setItem('commentStack', JSON.stringify(commentStack));
        }
    }
});
</script>
