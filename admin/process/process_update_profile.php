<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}

require __DIR__ . '/../../includes/db_connect.php';

if (!$ketnoi) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';

    // ===== CẬP NHẬT EMAIL =====
    if ($action === 'update_email') {
        $new_email = mysqli_real_escape_string($ketnoi, trim($_POST['new_email']));
        $current_password = $_POST['current_password'] ?? '';

        // Validate
        if (empty($new_email) || empty($current_password)) {
            $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
            header("Location: ../view/employee/profile.php");
            exit();
        }

        // Kiểm tra email hợp lệ
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Email không hợp lệ!";
            header("Location: ../view/employee/profile.php");
            exit();
        }

        // Lấy thông tin user hiện tại
        $sql = "SELECT * FROM admin WHERE id='$user_id'";
        $result = mysqli_query($ketnoi, $sql);
        $user = mysqli_fetch_assoc($result);

        // Kiểm tra mật khẩu hiện tại
        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['error'] = "Mật khẩu không chính xác!";
            header("Location: ../view/employee/profile.php");
            exit();
        }

        // Kiểm tra email đã tồn tại chưa
        $check_email = "SELECT * FROM admin WHERE email='$new_email' AND id != '$user_id'";
        $check_result = mysqli_query($ketnoi, $check_email);

        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = "Email này đã được sử dụng!";
            header("Location: ../view/employee/profile.php");
            exit();
        }

        // Kiểm tra email có giống email cũ không
        if ($new_email === $user['email']) {
            $_SESSION['error'] = "Email mới trùng với email hiện tại!";
            header("Location: ../view/employee/profile.php");
            exit();
        }

        // Update email
        $update_sql = "UPDATE admin SET email='$new_email' WHERE id='$user_id'";
        
        if (mysqli_query($ketnoi, $update_sql)) {
            $_SESSION['success'] = "Cập nhật email thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra: " . mysqli_error($ketnoi);
        }
    }

    // ===== ĐỔI MẬT KHẨU =====
    elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
            header("Location: ../view/employee/profile.php");
            exit();
        }

        // Kiểm tra mật khẩu mới khớp
        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = "Mật khẩu mới không khớp!";
            header("Location: ../view/employee/profile.php");
            exit();
        }

        // Kiểm tra độ dài mật khẩu
        if (strlen($new_password) < 6) {
            $_SESSION['error'] = "Mật khẩu mới phải có ít nhất 6 ký tự!";
            header("Location: ../view/employee/profile.php");
            exit();
        }

        // Lấy thông tin user hiện tại
        $sql = "SELECT * FROM admin WHERE id='$user_id'";
        $result = mysqli_query($ketnoi, $sql);
        $user = mysqli_fetch_assoc($result);

        // Kiểm tra mật khẩu hiện tại
        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['error'] = "Mật khẩu hiện tại không chính xác!";
            header("Location: ../view/employee/profile.php");
            exit();
        }

        // Kiểm tra mật khẩu mới có giống mật khẩu cũ không
        if (password_verify($new_password, $user['password'])) {
            $_SESSION['error'] = "Mật khẩu mới không được trùng với mật khẩu cũ!";
            header("Location: ../view/employee/profile.php");
            exit();
        }

        // Mã hóa mật khẩu mới
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update mật khẩu
        $update_sql = "UPDATE admin SET password='$hashed_password' WHERE id='$user_id'";
        
        if (mysqli_query($ketnoi, $update_sql)) {
            $_SESSION['success'] = "Đổi mật khẩu thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra: " . mysqli_error($ketnoi);
        }
    }

    // Action không hợp lệ
    else {
        $_SESSION['error'] = "Hành động không hợp lệ!";
    }

    mysqli_close($ketnoi);
    header("Location: ../view/employee/profile.php");
    exit();
}

// Nếu không phải POST
header("Location: ../view/employee/profile.php");
exit();
?>