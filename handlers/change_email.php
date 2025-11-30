<?php
session_start();
header('Content-Type: application/json');

// ✅ Debug: Ghi log những gì nhận được
error_log("POST data: " . print_r($_POST, true));
error_log("Email received: " . ($_POST['email'] ?? 'EMPTY'));

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error_type' => 'auth',
        'message' => 'Vui lòng đăng nhập!'
    ]);
    exit;
}

require '../includes/db_connect.php';
require '../includes/send_mail.php';

// ✅ Kiểm tra POST có tồn tại không
if (!isset($_POST['email'])) {
    echo json_encode([
        'success' => false,
        'error_type' => 'missing_data',
        'message' => 'Không nhận được dữ liệu email!'
    ]);
    exit;
}

$email = trim($_POST['email']);
$user_id = $_SESSION['user_id'];
$user = $_SESSION['name'] ?? 'Khách';


error_log("Email after trim: '$email'");
error_log("Email length: " . strlen($email));

// Kiểm tra email rỗng
if (empty($email)) {
    echo json_encode([
        'success' => false,
        'error_type' => 'email_format',
        'message' => 'Email không được để trống!'
    ]);
    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'error_type' => 'email_format',
        'message' => 'Email không hợp lệ. Định dạng đúng: example@domain.com',
        'debug_email' => $email 
    ]);
    exit;
}


$email_safe = mysqli_real_escape_string($ketnoi, $email);
$sql = "SELECT COUNT(*) AS dem FROM khachhang WHERE Email='$email_safe' AND MaKH != '$user_id'";
$result = mysqli_query($ketnoi, $sql);
$row = mysqli_fetch_assoc($result);

if ($row['dem'] > 0) {
    echo json_encode([
        'success' => false,
        'error_type' => 'email',
        'message' => 'Email đã được sử dụng bởi tài khoản khác!'
    ]);
    exit;
}

// ✅ Tạo mã xác minh
$verification_code = rand(100000, 999999);
$_SESSION['temp_user'] = [
    'email' => $email,
    'code' => $verification_code,
    'created_at' => time()
];

// ✅ Gửi email xác nhận
$subject = "Mã xác nhận thay đổi email Pizza Store";
$body = "<p>Xin chào <b>$user</b>,</p>
<p>Bạn đang thực hiện thay đổi email tài khoản.</p>
<p>Mã xác nhận của bạn là: <h1 style='color: #ff6b6b;'>$verification_code</h1></p>
<p>Mã có hiệu lực trong <b>200 giây</b>.</p>
<p>Nếu không phải bạn thực hiện, vui lòng bỏ qua email này.</p>";

$result = sendMail($email, $user, $subject, $body);

if ($result === true) {
    echo json_encode([
        'success' => true,
        'debug_email' => $email // ✅ Để xem email được gửi
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error_type' => 'system',
        'message' => 'Không thể gửi email xác nhận. Vui lòng thử lại sau!',
        'debug_error' => $result
    ]);
}
?>