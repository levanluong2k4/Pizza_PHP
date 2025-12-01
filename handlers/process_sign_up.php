<?php
session_start();
require '../includes/db_connect.php';
require '../includes/send_mail.php';
require '../includes/email_validator.php'; // THÊM DÒNG NÀY
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

// 1. Kiểm tra email đã tồn tại trong DB
$sql = "SELECT COUNT(*) as dem FROM khachhang WHERE Email=?";
$stmt = mysqli_prepare($ketnoi, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$number_row = mysqli_fetch_array($result);

if ($number_row["dem"] > 0) {
    echo json_encode([
        'success' => false,
        'error_type' => 'email',
        'message' => 'Email đã tồn tại! Vui lòng chọn email khác.'
    ]);
    exit;
}

// 1. Kiểm tra email đã tồn tại trong DB
$sql = "SELECT COUNT(*) as dem FROM admin WHERE email=?";
$stmt = mysqli_prepare($ketnoi, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$number_row_admin = mysqli_fetch_array($result);

if ($number_row_admin["dem"] > 0) {
    echo json_encode([
        'success' => false,
        'error_type' => 'email',
        'message' => 'Email đã tồn tại! Vui lòng chọn email khác.'
    ]);
    exit;
}

// 2. Kiểm tra format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'error_type' => 'email_format',
        'message' => 'Email không hợp lệ.'
    ]);
    exit;
}

// 3. KIỂM TRA EMAIL CÓ TỒN TẠI THẬT (QUAN TRỌNG)
if (!verifyEmailSMTP($email)) {
    echo json_encode([
        'success' => false,
        'error_type' => 'email_not_exist',
        'message' => 'Email không tồn tại hoặc không thể nhận thư. Vui lòng kiểm tra lại.'
    ]);
    exit;
}

// 4. Kiểm tra số điện thoại
if (!preg_match('/^[0-9]{10}$/', $sdt)) {
    echo json_encode([
        'success' => false,
        'error_type' => 'phone',
        'message' => 'Số điện thoại phải có đúng 10 chữ số.'
    ]);
    exit;
}

// 5. Kiểm tra mật khẩu
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

// 6. Tạo mã xác nhận
$verification_code = rand(100000, 999999);
$_SESSION['temp_user'] = [
    'name' => $name,
    'sdt' => $sdt,
    'email' => $email,
    'password' => $hashedPassword,
    'code' => $verification_code,
    'created_at' => time()
];

// 7. Gửi email (chắc chắn email tồn tại rồi)
$subject = "Mã xác nhận đăng ký Pizza Store";
$body = "<p>Xin chào <b>$name</b>,</p>
<p>Mã xác nhận của bạn là: <h1>$verification_code</h1></p>
<p>Mã có hiệu lực trong 15 phút.</p>";

$result = sendMail($email, $name, $subject, $body);

if ($result === true) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'error_type' => 'email_send_failed',
        'message' => 'Không thể gửi email. Vui lòng thử lại sau.'
    ]);
}
?>