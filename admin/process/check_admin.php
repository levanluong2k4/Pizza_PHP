<?php
session_start();

// Kiểm tra nếu chưa đăng nhập hoặc không phải admin
if ((!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin')) {
  header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}
?>
