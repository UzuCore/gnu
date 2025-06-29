<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_PHPMAILER_PATH . '/PHPMailerAutoload.php');

/* ---------------------------
   ✅ Gmail 전용 SMTP 설정
---------------------------- */
include_once('/var/www/server.config.php');

/* ---------------------------
   ✅ 메일 전송 함수
---------------------------- */
function mailer($fname, $fmail, $to, $subject, $content, $type=0, $file="", $cc="", $bcc="")
{
    global $config;

    // 메일발송 사용을 하지 않는다면
    if (!$config['cf_email_use']) return;

    if ($type != 1)
        $content = nl2br($content);

    $result = run_replace('mailer', $fname, $fmail, $to, $subject, $content, $type, $file, $cc, $bcc);
    if (is_array($result) && isset($result['return'])) {
        return $result['return'];
    }

    $mail_send_result = false;

    try {
        $mail = new PHPMailer(true);

        // Gmail SMTP 설정
        $mail->isSMTP();
        $mail->Host       = GMAIL_SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = GMAIL_SMTP_USER;
        $mail->Password   = GMAIL_SMTP_PASS;
        $mail->SMTPSecure = GMAIL_SMTP_SECURE;
        $mail->Port       = GMAIL_SMTP_PORT;

        $mail->CharSet    = 'UTF-8';
        $mail->setFrom($fmail, $fname);
        $mail->addAddress($to);
        if ($cc)  $mail->addCC($cc);
        if ($bcc) $mail->addBCC($bcc);

        $mail->Subject = $subject;
        $mail->AltBody = strip_tags($content);
        $mail->msgHTML($content);

        if (is_array($file) && !empty($file)) {
            foreach ($file as $f) {
                if (file_exists($f['path']))
                    $mail->addAttachment($f['path'], $f['name']);
            }
        }

        $mail = run_replace('mail_options', $mail, $fname, $fmail, $to, $subject, $content, $type, $file, $cc, $bcc);

        if (!($mail_send_result = $mail->send())) {
            throw new Exception($mail->ErrorInfo);
        }

    } catch (Exception $e) {
        error_log("[Gmail 메일 전송 오류] " . $e->getMessage());
    }

    run_event('mail_send_result', $mail_send_result, $mail, $to, $cc, $bcc);

    return $mail_send_result;
}

/* ---------------------------
   ✅ 첨부파일 핸들링 함수
---------------------------- */
function attach_file($filename, $tmp_name)
{
    $dest_file = G5_DATA_PATH . '/tmp/' . str_replace('/', '_', $tmp_name);
    move_uploaded_file($tmp_name, $dest_file);
    return array("name" => $filename, "path" => $dest_file);
}
