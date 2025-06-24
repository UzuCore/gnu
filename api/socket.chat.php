<?php
include_once('../common.php');
if (!$is_member) exit;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>1:1 채팅</title>
    <style>
        body {
            margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #f2f2f2;
        }
        .chat-wrap {
            max-width: 600px;
            margin: 0 auto;
            height: 100vh;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #ccc;
            border-right: 1px solid #ccc;
            background: #fff;
        }
        .chat-header {
            background: #007aff;
            color: white;
            padding: 14px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.4s, transform 0.4s ease;
        }
        .chat-header.animate {
            background-color: #34c759;
            transform: scale(1.05);
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }
        .message {
            display: flex;
            margin: 6px 0;
        }
        .message.me {
            justify-content: flex-end;
        }
        .message.you {
            justify-content: flex-start;
        }
        .bubble {
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 20px;
            font-size: 14px;
            line-height: 1.4;
            word-break: break-word;
        }
        .me .bubble {
            background: #007aff;
            color: white;
            border-bottom-right-radius: 0;
        }
        .you .bubble {
            background: #e5e5ea;
            color: black;
            border-bottom-left-radius: 0;
        }
        .system-message {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin: 10px 0;
        }
        .chat-input {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ddd;
            background: #fafafa;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 20px;
            outline: none;
        }
        .chat-input button {
            background: #007aff;
            color: white;
            border: none;
            padding: 10px 18px;
            margin-left: 8px;
            border-radius: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="chat-wrap">
    <div class="chat-header" id="chat-header">1:1 채팅</div>
    <div class="chat-messages" id="chat-box"></div>
    <form class="chat-input" id="chat-form">
        <input type="text" id="chat-msg" placeholder="메시지를 입력하세요..." autocomplete="off" />
        <button type="submit">전송</button>
    </form>
</div>

<script src="<?php echo G5_JS_URL ?>/jquery-1.12.4.min.js"></script>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
<script>
    const socket = io('wss://' + location.hostname + ':3000', { transports: ['websocket'] });
    const mb_id = <?= json_encode($member['mb_id']) ?>;
    const nickname = <?= json_encode($member['mb_nick']) ?>;
    const chatBox = document.getElementById('chat-box');
    const chatHeader = document.getElementById('chat-header');

    let otherNickname = '';

    function addMessage(msg, type) {
        const bubble = document.createElement('div');
        bubble.className = 'message ' + type;
        bubble.innerHTML = '<div class="bubble">' + msg + '</div>';
        chatBox.appendChild(bubble);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function addSystemMessage(msg) {
        const sys = document.createElement('div');
        sys.className = 'system-message';
        sys.textContent = msg;
        chatBox.appendChild(sys);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function animateHeader(newName) {
        chatHeader.classList.add('animate');
        chatHeader.textContent = '1:1 채팅 - ' + newName;
        setTimeout(() => chatHeader.classList.remove('animate'), 600);
    }

    socket.on('connect', () => {
        socket.emit('register', { mb_id, nickname });
    });

    socket.on('chat message', data => {
        const type = data.nickname === nickname ? 'me' : 'you';
        if (type === 'you' && data.nickname !== otherNickname) {
            otherNickname = data.nickname;
            animateHeader(otherNickname);
        }
        addMessage(data.message, type);
    });

    socket.on('user joined', data => {
        if (data.mb_id !== mb_id) {
            otherNickname = data.nickname;
            animateHeader(data.nickname);
            addSystemMessage(data.nickname + '님이 입장했습니다.');
        }
    });

    socket.on('user left', data => {
        if (data.mb_id !== mb_id) {
            addSystemMessage(data.nickname + '님이 퇴장했습니다.');
        }
    });

    document.getElementById('chat-form').addEventListener('submit', e => {
        e.preventDefault();
        const input = document.getElementById('chat-msg');
        const msg = input.value.trim();
        if (!msg) return;
        socket.emit('chat message', { nickname, message: msg });
        input.value = '';
    });
</script>
</body>
</html>
