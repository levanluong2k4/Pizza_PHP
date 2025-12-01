<?php
require __DIR__ . '/../../includes/db_connect.php';

if (isset($_GET['id'])) {
    $maSize = mysqli_real_escape_string($ketnoi, $_GET['id']);

    // Kiểm tra xem size có đang được sử dụng trong sanpham_size không
    $check_sql = "SELECT COUNT(*) as count FROM sanpham_size WHERE MaSize = $maSize";
    $check_result = mysqli_query($ketnoi, $check_sql);
    $check_row = mysqli_fetch_assoc($check_result);

    if ($check_row['count'] > 0) {
        echo "<script>alert('Không thể xóa size này vì đang có sản phẩm sử dụng!'); window.location.href='../view/list_sizes.php';</script>";
        exit();
    }

    $sql = "DELETE FROM size WHERE MaSize = $maSize";
    if (mysqli_query($ketnoi, $sql)) {
        header("Location: /unitop/backend/lesson/school/project_pizza/admin/view/product/list_sizes.php");
        exit();
    } else {
        echo "Lỗi: " . mysqli_error($ketnoi);
    }
}
?>