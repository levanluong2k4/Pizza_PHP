<?php
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id'])) {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}

require __DIR__ . '/../../includes/db_connect.php';
require __DIR__ . '/../../includes/send_mail.php';
require __DIR__ . '/../../includes/email_validator.php';

if (!$ketnoi) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten = mysqli_real_escape_string($ketnoi, trim($_POST['ten']));
    $email = mysqli_real_escape_string($ketnoi, trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phanquyen = (int)$_POST['phanquyen'];

    // 1. Validate dữ liệu cơ bản
    if (empty($ten) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    // 2. Kiểm tra mật khẩu
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Mật khẩu phải có ít nhất 6 ký tự!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    // 3. Kiểm tra format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email không hợp lệ!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    // 4. Kiểm tra email đã tồn tại trong hệ thống chưa
    $check_email = "SELECT * FROM admin WHERE email=?";
    $stmt = mysqli_prepare($ketnoi, $check_email);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Email đã tồn tại trong hệ thống!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    // 5. KIỂM TRA EMAIL CÓ TỒN TẠI THẬT (SMTP Verification)
    if (!verifyEmailSMTP($email)) {
        $_SESSION['error'] = "Email không tồn tại hoặc không thể nhận thư. Vui lòng kiểm tra lại.";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    // 6. Mã hóa mật khẩu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // 7. Tạo mã xác nhận 6 số
    $verification_code = rand(100000, 999999);
    
    // 8. Lưu thông tin tạm vào session (chưa insert vào DB)
    $_SESSION['temp_employee'] = [
        'ten' => $ten,
        'email' => $email,
        'password' => $hashed_password,
        'phanquyen' => $phanquyen,
        'code' => $verification_code,
        'created_at' => time()
    ];

    // 9. Gửi email xác nhận
    $subject = "Mã xác nhận tạo tài khoản nhân viên - Pizza Store";
    $body = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #28a745;'>Xác nhận tạo tài khoản nhân viên</h2>
        <p>Xin chào <b>$ten</b>,</p>
        <p>Bạn đang được tạo tài khoản nhân viên tại hệ thống Pizza Store.</p>
        <p>Mã xác nhận của bạn là:</p>
        <h1 style='background: #f8f9fa; padding: 20px; text-align: center; color: #28a745; border-radius: 8px;'>$verification_code</h1>
        <p><strong>Lưu ý:</strong> Mã xác nhận có hiệu lực trong <b>15 phút</b>.</p>
        <p>Nếu bạn không yêu cầu tạo tài khoản, vui lòng bỏ qua email này.</p>
        <hr>
        <p style='color: #6c757d; font-size: 12px;'>Email này được gửi tự động từ hệ thống Pizza Store. Vui lòng không trả lời email này.</p>
    </div>";

    $result = sendMail($email, $ten, $subject, $body);

    if ($result === true) {
        $_SESSION['success'] = "Mã xác nhận đã được gửi đến email: $email. Vui lòng kiểm tra hộp thư.";
        // Chuyển hướng đến trang nhập mã xác nhận
        header("Location: ../view/employee/verify_employee.php");
        exit();
    } else {
        $_SESSION['error'] = "Không thể gửi email xác nhận. Vui lòng thử lại sau.";
        header("Location: ../view/employee/create_account.php");
        exit();
    }
}

// Nếu không phải POST, redirect về trang form
mysqli_close($ketnoi);
header("Location: ../view/employee/create_account.php");
exit();
?>