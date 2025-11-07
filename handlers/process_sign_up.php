<?php
session_start();
require '../includes/db_connect.php';
require '../includes/send_mail.php';
header('Content-Type: application/json');

$name = $_POST['name'] ?? '';
$sdt = $_POST['sdt'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

$_SESSION['old_name'] = $name;
    $_SESSION['old_sdt'] = $sdt;
    $_SESSION['old_password'] = $password;
    $_SESSION['old_password_confirm'] = $password_confirm;
    $_SESSION['old_email'] = $email;

$sql = "SELECT COUNT(*) as dem FROM khachhang WHERE Email='$email'";
$result = mysqli_query($ketnoi, $sql);
$number_row = mysqli_fetch_array($result);

if ($number_row["dem"] > 0) {
    echo json_encode([
        'success' => false,
        'error_type' => 'email',
        'message' => 'Email đã tồn tại! Vui lòng chọn email khác.'
    ]);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'error_type' => 'email_format',
        'message' => 'Email không hợp lệ.'
    ]);
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $sdt)) {
    echo json_encode([
        'success' => false,
        'error_type' => 'phone',
        'message' => 'Số điện thoại phải có đúng 10 chữ số.'
    ]);
    exit;
}


if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'error_type' => 'password_length',
        'message' => 'Mật khẩu tối thiểu 6 ký tự.'
    ]);
    exit;
}

if ($password !== $password_confirm) {
    echo json_encode([
        'success' => false,
        'error_type' => 'password_mismatch',
        'message' => 'Mật khẩu xác nhận không trùng khớp.'
    ]);
    exit;
}
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
// Không có lỗi → gửi email xác nhận
$verification_code = rand(100000, 999999);
$_SESSION['temp_user'] = [
    'name' => $name,
    'sdt' => $sdt,
    'email' => $email,
    'password' => $hashedPassword,
    'code' => $verification_code,
    'created_at' => time()
];

$subject = "Mã xác nhận đăng ký Pizza Store";
$body = "<p>Xin chào <b>$name</b>,</p>
<p>Mã xác nhận của bạn là: <h1>$verification_code</h1></p>
<p>Mã có hiệu lực trong 15 giây.</p>";

$result = sendMail($email, $name, $subject, $body);

if ($result === true) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'error_type' => 'system',
        'message' => 'Không thể gửi email xác nhận. Lỗi: ' . $result
    ]);
}
?>
