<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once('/var/www/server.config.php');

if (!isset($_GET['code'])) {
    // 1. 최초 접근: 카카오 인증 URL 안내
    $auth_url = "https://kauth.kakao.com/oauth/authorize?response_type=code"
        . "&client_id={$client_id}"
        . "&redirect_uri=" . urlencode($redirect_uri)
        . "&scope=talk_message";
    echo "<h2>1. 카카오 인증</h2>";
    echo "<a href='{$auth_url}' target='_blank' style='font-size:20px; color:#fff; background:#0076ff; padding:10px 20px; border-radius:5px; text-decoration:none;'>카카오 인증하러 가기</a>";
    echo "<br><br>인증 후 이 페이지로 다시 돌아오면 토큰이 자동 발급됩니다.";
} else {
    // 2. code GET으로 받은 경우: 바로 토큰 요청
    $code = $_GET['code'];
    $url = "https://kauth.kakao.com/oauth/token";
    $postData = [
        "grant_type" => "authorization_code",
        "client_id" => $client_id,
        "redirect_uri" => $redirect_uri,
        "code" => $code
    ];

    // curl로 POST
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "<h2>카카오 토큰 발급 결과</h2>";
    echo "<div style='background:#222;color:#fff;padding:12px;'>";
    echo "<b>요청 파라미터</b><br>";
    echo "<pre>" . htmlspecialchars(print_r($postData, true)) . "</pre>";
    echo "<b>응답 코드: $http_code</b><br>";
    echo "<b>응답 내용:</b><br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    echo "</div>";

    if ($http_code === 200) {
    $token_data = json_decode($response, true);
        file_put_contents(__DIR__ . '/data/kakao_token.json', json_encode($token_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "<p style='color:lime;font-size:1.2em;'>토큰 발급 성공! (kakao_token.json 저장됨)</p>";
    } else {
        echo "<p style='color:tomato;font-size:1.2em;'>토큰 발급 실패!</p>";
    }
}
?>
