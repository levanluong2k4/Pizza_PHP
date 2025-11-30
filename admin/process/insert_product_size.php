<?php
$id_sanpham=$_POST['masanpham'];
$id_size=$_POST['masize'];
$image=$_FILES['Anh'];
$gia=$_POST['gia'];

$folder = "../../img/"; // vì file nằm trong admin/process
$file_extension = pathinfo($image['name'], PATHINFO_EXTENSION);
$file_name = uniqid() . '.' . $file_extension;
$path_file = $folder.$file_name;
$save_file_name = "img/".$file_name;

if (move_uploaded_file($image["tmp_name"], $path_file)) {
    $ketnoi = mysqli_connect("localhost","root","","php_pizza");
    mysqli_set_charset($ketnoi,"utf8");
    $sql="INSERT INTO `sanpham_size`(`MaSP`, `MaSize`, `Gia`, `Anh`) 
    VALUES ('$id_sanpham','$id_size','$gia','$save_file_name')";
    mysqli_query($ketnoi,$sql);
    mysqli_close($ketnoi);
    header("location: ../view/view_sanphamsize.php");
} else {
    echo "Upload ảnh thất bại!";
}


?>