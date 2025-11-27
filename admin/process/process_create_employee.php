<?php
session_start();

// ⚠️ SỬA: Thêm dòng này để giả lập đăng nhập (test tạm)
$_SESSION['admin_id'] = 1;  // ← THÊM DÒNG NÀY

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// ⚠️ SỬA: Thay đổi port nếu cần (8889 cho MAMP, 3306 cho XAMPP)
$ketnoi = mysqli_connect("localhost:8889", "root", "root", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

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

    // Validate
    if (empty($ten) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Mật khẩu phải có ít nhất 6 ký tự!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    // Kiểm tra email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email không hợp lệ!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    // Kiểm tra email đã tồn tại chưa
    $check_email = "SELECT * FROM admin WHERE email='$email'";
    $result = mysqli_query($ketnoi, $check_email);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Email đã tồn tại trong hệ thống!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    // Mã hóa mật khẩu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert vào database
    $sql = "INSERT INTO admin (ten, email, password, phanquyen) 
            VALUES ('$ten', '$email', '$hashed_password', '$phanquyen')";
    
    if (mysqli_query($ketnoi, $sql)) {
        $_SESSION['success'] = "Tạo tài khoản nhân viên thành công!";
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra: " . mysqli_error($ketnoi);
    }

    mysqli_close($ketnoi);
    header("Location: ../view/employee/create_account.php");
    exit();
}

// Nếu không phải POST, redirect về trang form
header("Location: ../view/employee/create_account.php");
exit();
?>