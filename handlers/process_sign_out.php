<?php session_start();
// Xóa session và cookie liên quan đến người dùng
unset($_SESSION['user_id']);
unset($_SESSION['role']);
unset($_SESSION['phanquyen']);

unset($_SESSION['temp_hoten']);
unset($_SESSION['temp_sodt']);
unset($_SESSION['temp_diachi']);
        unset($_SESSION['temp_ward']);
        unset($_SESSION['temp_district']);
        unset($_SESSION['temp_province']);
        unset($_SESSION['temp_so_nha']);
     
        
        unset($_SESSION['old_address'] );

unset($_SESSION['name']);
if (isset($_COOKIE['remember'])) {
    setcookie('remember', '', time() - 3600, "/"); // xóa cookie
    unset($_COOKIE['remember']); // xóa khỏi mảng PHP hiện tại
}

header("Location: ../trangchu.php");


?>