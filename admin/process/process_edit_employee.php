<?php
session_start();

// ⚠️ SỬA: Thêm dòng này để giả lập đăng nhập (test tạm)
$_SESSION['user_id'] = 1;  // ← THÊM DÒNG NÀY

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id'])) {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}

// ⚠️ SỬA: Thay đổi port nếu cần
$ketnoi = mysqli_connect("localhost:8889", "root", "root", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

if (!$ketnoi) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $ten = mysqli_real_escape_string($ketnoi, trim($_POST['ten']));
    $email = mysqli_real_escape_string($ketnoi, trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phanquyen = (int)$_POST['phanquyen'];

    // Validate
    if (empty($ten) || empty($email)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
        header("Location: ../view/employee/edit_employee.php?id=$id");
        exit();
    }

    // Kiểm tra email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email không hợp lệ!";
        header("Location: ../view/employee/edit_employee.php?id=$id");
        exit();
    }

    // Kiểm tra email đã tồn tại chưa (trừ email của chính user đang sửa)
    $check_email = "SELECT * FROM admin WHERE email='$email' AND id != '$id'";
    $result = mysqli_query($ketnoi, $check_email);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Email đã tồn tại trong hệ thống!";
        header("Location: ../view/employee/edit_employee.php?id=$id");
        exit();
    }

    // Nếu có nhập mật khẩu mới
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
            header("Location: ../view/employee/edit_employee.php?id=$id");
            exit();
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = "Mật khẩu phải có ít nhất 6 ký tự!";
            header("Location: ../view/employee/edit_employee.php?id=$id");
            exit();
        }

        // Mã hóa mật khẩu mới
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update với mật khẩu mới
        $sql = "UPDATE admin 
                SET ten='$ten', email='$email', password='$hashed_password', phanquyen='$phanquyen' 
                WHERE id='$id'";
    } else {
        // Update không đổi mật khẩu
        $sql = "UPDATE admin 
                SET ten='$ten', email='$email', phanquyen='$phanquyen' 
                WHERE id='$id'";
    }
    
    if (mysqli_query($ketnoi, $sql)) {
        $_SESSION['success'] = "Cập nhật thông tin nhân viên thành công!";
        header("Location: ../view/employee/edit_employee.php?id=$id");
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra: " . mysqli_error($ketnoi);
        header("Location: ../view/employee/edit_employee.php?id=$id");
    }

    mysqli_close($ketnoi);
    exit();
}

// Nếu không phải POST, redirect về trang danh sách
header("Location: ../view/employee/create_account.php");
exit();
?>