<?php
require __DIR__ . '/../../includes/db_connect.php';

if (isset($_GET['id'])) {
    $maLoai = $_GET['id'];

    $sql = "DELETE FROM loaisanpham WHERE MaLoai = ?";
    $stmt = mysqli_prepare($ketnoi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $maLoai);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../view/product/manage_categories.php?success=3");
        exit();
    } else {
        echo "Lỗi: " . mysqli_error($ketnoi);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($ketnoi);
}
?>