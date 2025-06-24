<?php
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/admin.head.php');

if (!defined('_GNUBOARD_') || !$is_admin) exit;

$target_mb_id = $_POST['mb_id'] ?? '';
$mode = $_POST['mode'] ?? '';
$sent = false;

if ($mode && $target_mb_id) {
    $url = 'https://127.0.0.1:3001/notify';
    $payload = [];

    if ($mode === 'memo') {
        $payload = [
            'mb_id' => $target_mb_id,
            'type' => 'memo',
            'cnt' => 1
        ];
    } elseif ($mode === 'comment') {
        $payload = [
            'mb_id' => $target_mb_id,
            'type' => 'comment',
            'text' => '관리자 테스트 댓글입니다!',
            'url' => G5_URL . '/bbs/board.php?bo_table=free&wr_id=1',
            'comment_id' => 123
        ];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 로컬 테스트용
    $response = curl_exec($ch);
    curl_close($ch);

    $sent = true;
}
?>

<h2>🔔 실시간 알림 테스트</h2>

<form method="post">
    <label>대상 회원아이디 (mb_id):</label>
    <input type="text" name="mb_id" value="<?=htmlspecialchars($target_mb_id)?>" required>
    <br><br>
    <button type="submit" name="mode" value="memo">📩 쪽지 알림 보내기</button>
    <button type="submit" name="mode" value="comment">💬 댓글 알림 보내기</button>
</form>

<?php if ($sent): ?>
    <p style="color:green;">✅ 알림이 전송되었습니다!</p>
<?php endif; ?>

<?php include_once(G5_ADMIN_PATH.'/admin.tail.php'); ?>
