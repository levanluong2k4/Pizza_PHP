<?php
session_start();

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']) ? true : false;

$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

// --- 1️⃣ Kiểm tra trong bảng admin trước ---
$sql_admin = "SELECT * FROM admin WHERE email='$email'";
$result_admin = mysqli_query($ketnoi, $sql_admin);

if (mysqli_num_rows($result_admin) > 0) {
    $admin = mysqli_fetch_assoc($result_admin);

    // So sánh mật khẩu (hiện bạn dùng plain text)
    if ($admin['password'] === $password) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['name'] = $admin['ten'];
        $_SESSION['role'] = 'admin';
        $_SESSION['phanquyen'] = $admin['phanquyen']; // 0 hoặc 1

        header("Location: ../admin/navbar_admin.php");
        exit();
    } else {
        $_SESSION['old_email'] = $email;
        $_SESSION['error'] = 'wrong_password';
        header("Location: ../sign_in.php");
        exit();
    }
}

// --- 2️⃣ Nếu không phải admin, kiểm tra trong bảng khách hàng ---
$sql_user = "SELECT * FROM khachhang WHERE Email='$email'";
$result_user = mysqli_query($ketnoi, $sql_user);

if (mysqli_num_rows($result_user) > 0) {
    $user = mysqli_fetch_assoc($result_user);
    $id = $user['MaKH'];

    if ($user['MatKhau'] === $password) {
        $_SESSION['user_id'] = $id;
        $_SESSION['name'] = $user['HoTen'];
        $_SESSION['role'] = 'user';

        // Ghi nhớ đăng nhập (chỉ áp dụng cho khách hàng)
        if ($remember) {
            $token = bin2hex(random_bytes(16));
            $sql_update = "UPDATE khachhang SET token='$token' WHERE MaKH='$id'";
            mysqli_query($ketnoi, $sql_update);
            setcookie('remember', $token, time() + (30 * 24 * 60 * 60), "/");
        }

        header("Location: ../trangchu.php");
        exit();
    } else {
        $_SESSION['old_email'] = $email;
        $_SESSION['error'] = 'wrong_password';
        header("Location: ../sign_in.php");
        exit();
    }
} else {
    // Không tìm thấy email trong cả 2 bảng
    $_SESSION['old_email'] = $email;
    $_SESSION['error'] = 'email_not_found';
    header("Location: ../sign_in.php");
    exit();
}

mysqli_close($ketnoi);
?>
