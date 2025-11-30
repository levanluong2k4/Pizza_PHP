<?php
require __DIR__ . '/../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenLoai = $_POST['tenLoai'];

    $sql = "INSERT INTO loaisanpham (TenLoai) VALUES (?)";
    $stmt = mysqli_prepare($ketnoi, $sql);
    mysqli_stmt_bind_param($stmt, "s", $tenLoai);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../view/manage_categories.php?success=1");
        exit();
    } else {
        echo "Lỗi: " . mysqli_error($ketnoi);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($ketnoi);
}
?>
