<?php
session_start();
header('Content-Type: application/json');

if (!isset($_POST['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Không nhận được dữ liệu email!'
    ]);
    exit;
}

require '../includes/db_connect.php';
require '../includes/send_mail.php';

$email = trim($_POST['email']);

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email không hợp lệ!'
    ]);
    exit;
}

// Kiểm tra email có tồn tại không
$email_safe = mysqli_real_escape_string($ketnoi, $email);
$sql = "SELECT MaKH, HoTen, Email FROM khachhang WHERE Email='$email_safe'";
$result = mysqli_query($ketnoi, $sql);

if (mysqli_num_rows($result) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Email không tồn tại trong hệ thống!'
    ]);
    exit;
}

$user = mysqli_fetch_assoc($result);

// Tạo mã xác thực ngẫu nhiên (6 chữ số)
$reset_code = rand(100000, 999999);

// Lưu vào bảng khachhang
$sql_update = "UPDATE khachhang SET token='$reset_code' WHERE Email='$email_safe'";
mysqli_query($ketnoi, $sql_update);

// Lưu vào session (dự phòng)
$_SESSION['reset_password'] = [
    'email' => $email,
    'code' => $reset_code,
    'created_at' => time()
];

// Gửi email
$subject = "Mã Xác Thực Đặt Lại Mật Khẩu - Pizza Store";
$body = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
        <h1 style='color: white; margin: 0;'>🔐 Đặt Lại Mật Khẩu</h1>
    </div>
    
    <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
        <p>Xin chào <strong>{$user['HoTen']}</strong>,</p>
        <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản <strong>{$email}</strong></p>
        
        <div style='background: white; padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0;'>
            <p style='margin: 0; color: #666;'>Mã xác thực của bạn là:</p>
            <h1 style='color: #667eea; font-size: 48px; margin: 10px 0; letter-spacing: 5px;'>{$reset_code}</h1>
            <p style='color: #dc3545; margin: 10px 0;'>⏰ Mã có hiệu lực trong <strong>5 phút</strong></p>
        </div>
        
        <div style='text-align: center; margin: 30px 0;'>
            <a href='http://localhost/pizza/handlers/verify_reset_code.php?code={$reset_code}' 
               style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                      color: white; 
                      padding: 15px 40px; 
                      text-decoration: none; 
                      border-radius: 50px; 
                      font-weight: bold;
                      display: inline-block;'>
                ✅ XÁC NHẬN ĐẶT LẠI MẬT KHẨU
            </a>
        </div>
        
        <hr style='border: 1px solid #ddd; margin: 30px 0;'>
        
        <p style='color: #666; font-size: 14px;'>
            ⚠️ <strong>Lưu ý:</strong> Nếu không phải bạn thực hiện, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi ngay.
        </p>
        
        <p style='color: #999; font-size: 12px; margin-top: 20px;'>
            © 2024 Pizza Store. All rights reserved.
        </p>
    </div>
</div>
";

$mail_result = sendMail($email, $user['HoTen'], $subject, $body);

if ($mail_result === true) {
    echo json_encode([
        'success' => true,
        'message' => 'Mã xác thực đã được gửi đến email của bạn! Vui lòng kiểm tra hộp thư.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Không thể gửi email! Vui lòng thử lại sau.'
    ]);
}
?>