<?php
require __DIR__ . '/../../includes/db_connect.php';

// Kiểm tra dữ liệu POST
if (!isset($_POST['ma']) || !isset($_POST['gia'])) {
    die("Dữ liệu không hợp lệ!");
}

$id = intval($_POST['ma']);
$gia = intval($_POST['gia']);

// Xử lý ảnh nếu có upload
$image_new = $_FILES['Anhnew'];
$save_file_name = null;

if (isset($image_new) && $image_new['size'] > 0) {
    $folder = "../../img/";
    
    // Tạo thư mục nếu chưa có
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($image_new['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Kiểm tra định dạng file
    if (in_array($file_extension, $allowed_extensions)) {
        $file_name = uniqid() . '.' . $file_extension;
        $path_file = $folder . $file_name;
        $save_file_name = "img/" . $file_name;
        
        if (!move_uploaded_file($image_new["tmp_name"], $path_file)) {
            die("Lỗi khi upload ảnh!");
        }
    } else {
        die("Định dạng ảnh không hợp lệ!");
    }
}

// Cập nhật database với Prepared Statement
if ($save_file_name) {
    // Có ảnh mới
    $sql = "UPDATE sanpham_size SET Gia = ?, Anh = ? WHERE id = ?";
    $stmt = mysqli_prepare($ketnoi, $sql);
    mysqli_stmt_bind_param($stmt, "isi", $gia, $save_file_name, $id);
} else {
    // Không có ảnh mới
    $sql = "UPDATE sanpham_size SET Gia = ? WHERE id = ?";
    $stmt = mysqli_prepare($ketnoi, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $gia, $id);
}

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($ketnoi);
    header("Location: ../view/product/list_product_prices.php?success=1");
    exit();
} else {
    die("Lỗi cập nhật: " . mysqli_error($ketnoi));
}
?>