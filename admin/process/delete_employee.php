<?php
session_start();

// ⚠️ SỬA: Thêm dòng này để giả lập đăng nhập (test tạm)
$_SESSION['admin_id'] = 1;  // ← THÊM DÒNG NÀY

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// ⚠️ SỬA: Thay đổi port nếu cần
$ketnoi = mysqli_connect("localhost:8889", "root", "root", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

if (!$ketnoi) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Kiểm tra có ID không
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Không cho phép xóa chính mình
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['error'] = "Bạn không thể xóa chính tài khoản của mình!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    // Kiểm tra nhân viên có tồn tại không
    $check_sql = "SELECT * FROM admin WHERE id='$id'";
    $check_result = mysqli_query($ketnoi, $check_sql);

    if (mysqli_num_rows($check_result) == 0) {
        $_SESSION['error'] = "Nhân viên không tồn tại!";
        header("Location: ../view/employee/create_account.php");
        exit();
    }

    // Xóa nhân viên
    $sql = "DELETE FROM admin WHERE id='$id'";
    
    if (mysqli_query($ketnoi, $sql)) {
        $_SESSION['success'] = "Xóa nhân viên thành công!";
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra: " . mysqli_error($ketnoi);
    }
} else {
    $_SESSION['error'] = "ID không hợp lệ!";
}

mysqli_close($ketnoi);
header("Location: ../view/employee/create_account.php");
exit();
?>