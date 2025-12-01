<?php
session_start();
require '../includes/db_connect.php';
require '../includes/send_mail.php';
header('Content-Type: application/json');

$email = $_POST['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email không được để trống.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email không hợp lệ.']);
    exit;
}

$sql = "SELECT COUNT(*) as dem FROM khachhang WHERE Email='$email'";
$result = mysqli_query($ketnoi, $sql);
$number_row = mysqli_fetch_array($result);

if ($number_row["dem"] > 0) {
    echo json_encode(['success' => false, 'message' => 'Email đã tồn tại! Vui lòng chọn email khác.']);
} else {
    // Debug: Log the email being processed
    error_log("Sending OTP to email: $email");
    // Gửi mã OTP ngay lập tức
    $verification_code = rand(100000, 999999);
    $_SESSION['temp_user'] = [
        'email' => $email,
        'code' => $verification_code,
        'created_at' => time()
    ];
    $_SESSION['otp_sent_for'] = $email;

    $subject = "Mã xác nhận đăng ký Pizza Store";
    $body = "<p>Mã xác nhận của bạn là: <h1>$verification_code</h1></p>
    <p>Mã có hiệu lực trong 15 phút.</p>";

    $result = sendMail($email, 'Người dùng', $subject, $body);

    if ($result === true) {
        echo json_encode(['success' => true, 'message' => 'Email có thể sử dụng. Mã OTP đã được gửi!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể gửi email OTP. Lỗi: ' . $result]);
    }
}
?>
