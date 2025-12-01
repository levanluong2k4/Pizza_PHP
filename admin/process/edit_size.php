<?php
require __DIR__ . '/../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $maSize = mysqli_real_escape_string($ketnoi, $_POST['maSize']);
    $tenSize = mysqli_real_escape_string($ketnoi, $_POST['tenSize']);

    $sql = "UPDATE size SET TenSize = '$tenSize' WHERE MaSize = $maSize";
    if (mysqli_query($ketnoi, $sql)) {
        header("Location: ../view/product/list_sizes.php?success=2");
        exit();
    } else {
        echo "Lỗi: " . mysqli_error($ketnoi);
    }
}
?>