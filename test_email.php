<?php
require 'includes/send_mail.php';

$email = 'test@example.com'; // Thay bằng email thật để test
$name = 'Test User';
$subject = 'Test OTP';
$body = '<p>Mã OTP test: 123456</p>';

$result = sendMail($email, $name, $subject, $body);

if ($result === true) {
    echo 'Email sent successfully!';
} else {
    echo 'Failed to send email: ' . $result;
}
?>
