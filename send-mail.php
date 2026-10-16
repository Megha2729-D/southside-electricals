<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

error_reporting(E_ALL);
ini_set('display_errors', '0'); // Never expose server errors publicly
ini_set('log_errors', '1');

require __DIR__ . '/phpmailer/vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/vendor/phpmailer/phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact-us.html');
    exit;
}

// Honeypot spam protection.
// Add the hidden user_url field to your form if you want to use this check.
if (!empty($_POST['user_url'])) {
    header('Location: success.html');
    exit;
}

/**
 * Google reCAPTCHA verification.
 * Replace this with your newly generated secret key.
 */
$recaptchaSecret = '6LfTMvAtAAAAAEDOivXcnMY1m2uXb78CmhmWGOrs';
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

if (empty($recaptchaResponse) || !function_exists('curl_init')) {
    redirectPage('failed.html');
}

$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

$ch = curl_init($verifyUrl);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'secret' => $recaptchaSecret,
        'response' => $recaptchaResponse
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 15
]);

$verifyResponse = curl_exec($ch);
$curlError = curl_errno($ch);

curl_close($ch);

if ($verifyResponse === false || $curlError !== 0) {
    redirectPage('failed.html');
}

$responseData = json_decode($verifyResponse);

if (!$responseData || empty($responseData->success)) {
    redirectPage('failed.html');
}

// Collect raw form values.
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate required fields.
if (
    $name === '' ||
    $phone === '' ||
    $email === '' ||
    $message === ''
) {
    header('Location: failed.html');
    exit;
}

// Validate field lengths.
if (
    mb_strlen($name) < 2 ||
    mb_strlen($name) > 100 ||
    mb_strlen($phone) > 20 ||
    mb_strlen($email) > 254 ||
    mb_strlen($message) < 10 ||
    mb_strlen($message) > 5000
) {
    header('Location: failed.html');
    exit;
}

// Validate email.
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: failed.html');
    exit;
}

// Validate phone number.
$phoneDigits = preg_replace('/\D/', '', $phone);

if (
    !preg_match('/^[+()\d\s.-]+$/', $phone) ||
    strlen($phoneDigits) < 7 ||
    strlen($phoneDigits) > 15
) {
    header('Location: failed.html');
    exit;
}

// Basic spam filtering.
$lowerMessage = strtolower($message);

if (
    substr_count($lowerMessage, 'http') > 2 ||
    substr_count($lowerMessage, 'www.') > 2
) {
    header('Location: failed.html');
    exit;
}

// Escape user data before inserting into HTML.
$nameHtml = htmlspecialchars(
    $name,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$phoneHtml = htmlspecialchars(
    $phone,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$emailHtml = htmlspecialchars(
    $email,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$messageHtml = nl2br(
    htmlspecialchars(
        $message,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    )
);

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'southsideelectricals@gmail.com';
    $mail->Password = 'vkgwkgxxanagiqid';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 30;

    // Keep certificate verification enabled.
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
        ],
    ];
    $mail->SMTPDebug = 0;
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    // $mail->Debugoutput = 'html';

    // Sender and recipients.
    $mail->setFrom(
        'southsideelectricals@gmail.com',
        'Southside Electricals Private Limited'
    );

    $mail->addAddress('meghu27022002@gmail.com');

    // Replies go directly to the person who submitted the form.
    $mail->addReplyTo($email, $name);

    // Email content.
    $mail->isHTML(true);
    $mail->Subject = 'New Website Enquiry - Southside Electricals';

    $mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Website Enquiry</title>
<style>
body {
    margin: 0;
    padding: 0;
    background: #f4f6f8;
    font-family: Arial, Helvetica, sans-serif;
    color: #333333;
}
.wrapper {
    width: 100%;
    padding: 30px 12px;
    box-sizing: border-box;
}
.container {
    max-width: 650px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}
.header {
    background: #192853;
    padding: 28px 24px;
    text-align: center;
}
.header h2 {
    margin: 0;
    color: #ffffff;
    font-size: 23px;
}
.header p {
    margin: 8px 0 0;
    color: #dce8f5;
    font-size: 13px;
}
.content {
    padding: 28px 24px;
}
.content h3 {
    color: #192853;
    margin: 0 0 12px;
    font-size: 20px;
}
.intro {
    color: #606b7a;
    line-height: 1.7;
    margin-bottom: 22px;
}
.info-table {
    width: 100%;
    border-collapse: collapse;
}
.info-table td {
    padding: 13px 12px;
    border-bottom: 1px solid #e9edf2;
    vertical-align: top;
    line-height: 1.5;
    overflow-wrap: anywhere;
}
.info-table td:first-child {
    width: 115px;
    color: #192853;
    font-weight: bold;
}
.message-box {
    margin-top: 24px;
    padding: 18px;
    background: #f6f9fc;
    border: 1px solid #e4eaf2;
    border-radius: 7px;
}
.message-box h4 {
    margin: 0 0 12px;
    color: #192853;
}
.message-text {
    color: #4b5563;
    line-height: 1.8;
    overflow-wrap: anywhere;
}
.footer {
    padding: 18px 24px;
    background: #f0f3f7;
    color: #6b7280;
    font-size: 12px;
    text-align: center;
    line-height: 1.7;
}
a {
    color: #192853;
}
</style>
</head>
<body>
<div class="wrapper">
<div class="container">

    <div class="header">
        <h2>Southside Electricals</h2>
        <p>Private Limited</p>
    </div>

    <div class="content">
        <h3>New Website Enquiry</h3>

        <p class="intro">
            A new enquiry has been submitted through the
            Southside Electricals website. Please find the
            details below.
        </p>

        <table class="info-table">
            <tr>
                <td>Name</td>
                <td>{$nameHtml}</td>
            </tr>
            <tr>
                <td>Phone</td>
                <td>{$phoneHtml}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>
                    <a href="mailto:{$emailHtml}">{$emailHtml}</a>
                </td>
            </tr>
        </table>

        <div class="message-box">
            <h4>Project Details</h4>
            <div class="message-text">{$messageHtml}</div>
        </div>

        <p style="margin-top:24px;">
            You can reply directly to this email to contact
            the enquirer.
        </p>
    </div>

    <div class="footer">
        This is an automated notification from the
        Southside Electricals website enquiry form.
    </div>

</div>
</div>
</body>
</html>
HTML;

    // Plain-text fallback.
    $mail->AltBody =
        "New Website Enquiry - Southside Electricals\n\n" .
        "Name: {$name}\n" .
        "Phone: {$phone}\n" .
        "Email: {$email}\n\n" .
        "Project Details:\n{$message}";

    // Send email.
    $mail->send();

    header('Location: success.html');
    exit;
} catch (\Throwable $e) {

    showError(
        'Email Sending Failed',
        "Error: " . $e->getMessage() .
        "\n\nPHPMailer Details: " . $mail->ErrorInfo
    );
}
