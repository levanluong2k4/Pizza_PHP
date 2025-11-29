<?php
require __DIR__ . '/../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $maLoai = $_POST['maLoai'];
    $tenLoai = $_POST['tenLoai'];

    $sql = "UPDATE loaisanpham SET TenLoai = ? WHERE MaLoai = ?";
    $stmt = mysqli_prepare($ketnoi, $sql);
    mysqli_stmt_bind_param($stmt, "si", $tenLoai, $maLoai);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../view/manage_categories.php?success=2");
        exit();
    } else {
        echo "Lỗi: " . mysqli_error($ketnoi);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($ketnoi);
}
?>
