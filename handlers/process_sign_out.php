<?php session_start();
// Xóa session và cookie liên quan đến người dùng
unset($_SESSION['user_id']);
unset($_SESSION['temp_hoten']);
unset($_SESSION['temp_sodt']);
unset($_SESSION['temp_diachi']);

unset($_SESSION['name']);
if (isset($_COOKIE['remember'])) {
    setcookie('remember', '', time() - 3600, "/"); // xóa cookie
    unset($_COOKIE['remember']); // xóa khỏi mảng PHP hiện tại
}

header("Location: ../trangchu.php");


?>