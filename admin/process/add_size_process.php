<?php
require __DIR__ . '/../../includes/db_connect.php';

// Kiểm tra dữ liệu POST
if (!isset($_POST['tenSize']) || empty(trim($_POST['tenSize']))) {
    header("Location: ../view/product/add_size.php?error=empty");
    exit();
}

$tenSize = trim($_POST['tenSize']);

// Kiểm tra size đã tồn tại chưa
$sql_check = "SELECT MaSize FROM size WHERE TenSize = ?";
$stmt_check = mysqli_prepare($ketnoi, $sql_check);
mysqli_stmt_bind_param($stmt_check, "s", $tenSize);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    mysqli_stmt_close($stmt_check);
    mysqli_close($ketnoi);
    header("Location: ../view/product/add_size.php?error=exists");
    exit();
}
mysqli_stmt_close($stmt_check);

// Thêm size mới
$sql_insert = "INSERT INTO size (TenSize) VALUES (?)";
$stmt_insert = mysqli_prepare($ketnoi, $sql_insert);

if ($stmt_insert) {
    mysqli_stmt_bind_param($stmt_insert, "s", $tenSize);
    
    if (mysqli_stmt_execute($stmt_insert)) {
        mysqli_stmt_close($stmt_insert);
        mysqli_close($ketnoi);
        header("Location: ../view/product/list_sizes.php?success=add");
        exit();
    } else {
        mysqli_stmt_close($stmt_insert);
        mysqli_close($ketnoi);
        header("Location: ../view/product/add_size.php?error=failed");
        exit();
    }
} else {
    mysqli_close($ketnoi);
    header("Location: ../view/product/add_size.php?error=failed");
    exit();
}
?>