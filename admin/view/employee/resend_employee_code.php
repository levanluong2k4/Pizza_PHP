<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}

require __DIR__ . '/../../includes/send_mail.php';

// Kiểm tra có thông tin tạm trong session không
if (!isset($_SESSION['temp_employee'])) {
    $_SESSION['error'] = "Phiên làm việc đã hết hạn. Vui lòng tạo lại tài khoản.";
    header("Location: ../view/employee/create_account.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['temp_employee']['email'];
    $name = $_SESSION['temp_employee']['ten'];
    
    // Tạo mã xác nhận mới
    $new_code = rand(100000, 999999);
    
    // Cập nhật lại session
    $_SESSION['temp_employee']['code'] = $new_code;
    $_SESSION['temp_employee']['created_at'] = time(); // Reset thời gian

    // Gửi email
    $subject = "Mã xác nhận mới - Tạo tài khoản nhân viên Pizza Store";
    $body = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #28a745;'>Gửi lại mã xác nhận</h2>
        <p>Xin chào <b>$name</b>,</p>
        <p>Bạn đã yêu cầu gửi lại mã xác nhận.</p>
        <p>Mã xác nhận mới của bạn là:</p>
        <h1 style='background: #f8f9fa; padding: 20px; text-align: center; color: #28a745; border-radius: 8px;'>$new_code</h1>
        <p><strong>Lưu ý:</strong> Mã xác nhận có hiệu lực trong <b>15 phút</b>.</p>
        <p>Nếu bạn không yêu cầu gửi lại mã, vui lòng bỏ qua email này.</p>
        <hr>
        <p style='color: #6c757d; font-size: 12px;'>Email này được gửi tự động từ hệ thống Pizza Store.</p>
    </div>";

    $result = sendMail($email, $name, $subject, $body);

    if ($result === true) {
        $_SESSION['success'] = "Mã xác nhận mới đã được gửi đến email: $email";
    } else {
        $_SESSION['error'] = "Không thể gửi lại mã. Vui lòng thử lại sau.";
    }

    header("Location: ../view/employee/verify_employee.php");
    exit();
}

// Nếu không phải POST
header("Location: ../view/employee/verify_employee.php");
exit();
?>