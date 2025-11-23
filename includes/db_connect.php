<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ketnoi = mysqli_connect("localhost:8889", "root", "root", "php_pizza");

if (!$ketnoi) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

mysqli_set_charset($ketnoi, "utf8");
?>
