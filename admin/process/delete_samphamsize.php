<?php
require __DIR__ . '/../../includes/db_connect.php';

// Kiểm tra ID có tồn tại và hợp lệ
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID không hợp lệ!");
}

$id = intval($_GET['id']);

// Sử dụng Prepared Statement để tránh SQL Injection
$sql = "DELETE FROM sanpham_size WHERE id = ?";
$stmt = mysqli_prepare($ketnoi, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($ketnoi);
        header("Location: /unitop/backend/lesson/school/project_pizza/admin/view/product/list_product_prices.php");
        exit();
    } else {
        die("Lỗi khi xóa: " . mysqli_error($ketnoi));
    }
} else {
    die("Lỗi chuẩn bị câu lệnh: " . mysqli_error($ketnoi));
}
?>