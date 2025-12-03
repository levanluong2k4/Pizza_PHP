<?php
session_start();

// ⚠️ SỬA: Thêm dòng này để giả lập đăng nhập (test tạm)
$_SESSION['user_id'] = 1;  // ← THÊM DÒNG NÀY

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id'])) {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}

require __DIR__ . '/../../includes/db_connect.php';

if (!$ketnoi) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $phanquyen = (int)$_POST['phanquyen'];

    // Validate phân quyền (chỉ chấp nhận 0 hoặc 1)
    if ($phanquyen !== 0 && $phanquyen !== 1) {
        $_SESSION['error'] = "Phân quyền không hợp lệ!";
        header("Location: ../view/employee/edit_employee.php?id=$id");
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

    // Update chỉ phân quyền
    $sql = "UPDATE admin SET phanquyen='$phanquyen' WHERE id='$id'";
    
    if (mysqli_query($ketnoi, $sql)) {
        $_SESSION['success'] = "Cập nhật phân quyền thành công!";
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