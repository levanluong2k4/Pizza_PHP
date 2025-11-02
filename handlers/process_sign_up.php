<?php 
require '../includes/db_connect.php';
require '../includes/send_mail.php';

$name = $_POST['name'];
$sdt = $_POST['sdt'];
$email = $_POST['email'];
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];

    $_SESSION['old_name'] = $name;
    $_SESSION['old_sdt'] = $sdt;
    $_SESSION['old_password'] = $password;
    $_SESSION['old_password_confirm'] = $password_confirm;
    $_SESSION['old_email'] = $email;

$sql = "SELECT COUNT(*) as dem FROM khachhang WHERE Email='$email'";
$result = mysqli_query($ketnoi, $sql);
$number_row = mysqli_fetch_array($result);

if($number_row["dem"] > 0){

    $_SESSION['error'] = 'email_already_exists';
    header("Location: ../sign_up.php");
    exit();
}

if($password != $password_confirm){
 
    $_SESSION['error'] = 'password_mismatch';
    header("Location: ../sign_up.php");
    exit();
}

// Tạo mã xác nhận và lưu thời gian tạo
$verification_code = rand(100000, 999999);

$_SESSION['temp_user'] = [
    'name' => $name,
    'sdt' => $sdt,
    'email' => $email,
    'password' => $password,
    'code' => $verification_code,
    'created_at' => time() // Lưu thời gian tạo mã
];

// Gửi mã xác nhận qua email
$subject = "Mã xác nhận đăng ký Pizza Store";
$body = "<p>Xin chào <b>$name</b>,</p>
<p>Mã xác nhận của bạn là: <h1 style='font-size:18px;'>$verification_code</h1></p>
<p>Mã này có hiệu lực trong <b>15 giây</b>.</p>
<p>Vui lòng nhập mã này để hoàn tất đăng ký.</p>";

$result = sendMail($email, $name, $subject, $body);

if ($result === true) {
    header("Location: ../verify_email.php");
    exit();
} else {
    echo "❌ Gửi email thất bại: $result";
}

mysqli_close($ketnoi);
?>