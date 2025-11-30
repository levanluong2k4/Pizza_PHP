<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

?>