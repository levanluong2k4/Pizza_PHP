<?php
$id=$_GET['id'];
$ketnoi = mysqli_connect("localhost","root","","php_pizza");
mysqli_set_charset($ketnoi,"utf8");
$sql="delete from sanpham where MaSP=$id";
mysqli_query($ketnoi,$sql);
mysqli_close($ketnoi);
header("location: ../view/indexadmin.php");


?>