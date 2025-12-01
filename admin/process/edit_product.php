<?php
$ma = $_POST['ma']; // lấy từ input hidden
$name = $_POST['tensp'];
$description = $_POST['mota'];
$maLoai = $_POST['maloai'];
$image_new = $_FILES['Anhnew'];

// Nếu có chọn ảnh mới
if ($image_new['size'] > 0) {
    $folder = "../../img/";
    $file_extension = pathinfo($image_new['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '.' . $file_extension;
    $path_file = $folder . $file_name;
    $save_file_name = "img/" . $file_name;

    move_uploaded_file($image_new["tmp_name"], $path_file);
} else {
    // Giữ ảnh cũ
    $save_file_name = $_POST['Anhold'];
}

$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

$sql = "UPDATE sanpham 
        SET TenSP='$name',
            Anh='$save_file_name',
            MaLoai=$maLoai,
            MoTa='$description' 
        WHERE MaSP=$ma";

mysqli_query($ketnoi, $sql);
mysqli_close($ketnoi);

header("location: ../view/indexadmin.php");
exit;
?>
