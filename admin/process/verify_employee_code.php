<?php
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id'])) {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}

require __DIR__ . '/../../includes/db_connect.php';

if (!$ketnoi) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Kiểm tra có thông tin tạm trong session không
if (!isset($_SESSION['temp_employee'])) {
    $_SESSION['error'] = "Phiên làm việc đã hết hạn. Vui lòng tạo lại tài khoản.";
    header("Location: ../view/employee/create_account.php");
    exit();
}

// Kiểm tra thời gian hết hạn (15 phút)
$created_at = $_SESSION['temp_employee']['created_at'];
if ((time() - $created_at) > 900) {
    unset($_SESSION['temp_employee']);
    $_SESSION['error'] = "Mã xác nhận đã hết hạn. Vui lòng tạo lại tài khoản.";
    header("Location: ../view/employee/create_account.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = trim($_POST['code']);
    $stored_code = $_SESSION['temp_employee']['code'];

    // Kiểm tra mã xác nhận
    if ($code != $stored_code) {
        $_SESSION['error'] = "Mã xác nhận không đúng. Vui lòng thử lại.";
        header("Location: ../view/employee/verify_employee.php");
        exit();
    }

    // Mã đúng - Lấy thông tin từ session
    $ten = $_SESSION['temp_employee']['ten'];
    $email = $_SESSION['temp_employee']['email'];
    $hashed_password = $_SESSION['temp_employee']['password'];
    $phanquyen = $_SESSION['temp_employee']['phanquyen'];

    // Insert vào database
    $sql = "INSERT INTO admin (ten, email, password, phanquyen) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($ketnoi, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $ten, $email, $hashed_password, $phanquyen);
    
    if (mysqli_stmt_execute($stmt)) {
        // Xóa thông tin tạm trong session
        unset($_SESSION['temp_employee']);
        
        $_SESSION['success'] = "Tạo tài khoản nhân viên thành công! Email: $email";
        header("Location: ../view/employee/create_account.php");
        exit();
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra khi tạo tài khoản: " . mysqli_error($ketnoi);
        header("Location: ../view/employee/verify_employee.php");
        exit();
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($ketnoi);
header("Location: ../view/employee/verify_employee.php");
exit();
?>