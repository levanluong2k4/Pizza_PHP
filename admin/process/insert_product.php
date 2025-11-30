<?php
$name=$_POST['tensp'];
$description=$_POST['mota'];
$image=$_FILES['Anh'];
$maLoai=$_POST['maloai'];

$folder = "../../img/"; // vì file nằm trong admin/process
$file_extension = pathinfo($image['name'], PATHINFO_EXTENSION);
$file_name = uniqid() . '.' . $file_extension;
$path_file = $folder.$file_name;
$save_file_name = "img/".$file_name;

if (move_uploaded_file($image["tmp_name"], $path_file)) {
    $ketnoi = mysqli_connect("localhost","root","","php_pizza");
    mysqli_set_charset($ketnoi,"utf8");
    $sql="insert into sanpham(TenSP,Anh,MaLoai,MoTa) 
          values('$name','$save_file_name',$maLoai,'$description')";
    mysqli_query($ketnoi,$sql);
    mysqli_close($ketnoi);
    header("location: ../view/indexadmin.php");
} else {
    echo "Upload ảnh thất bại!";
}


?>