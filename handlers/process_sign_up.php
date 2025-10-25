<?php session_start();
$ketnoi=mysqli_connect("localhost","root","","php_pizza");
mysqli_set_charset($ketnoi,"utf8");
$name=$_POST['name'];
$sdt=$_POST['sdt'];
$email=$_POST['email'];
$password=$_POST['password'];
$password_confirm=$_POST['password_confirm'];

$sql=" select count(*) as dem from khachhang where Email='$email' ";

$result=mysqli_query($ketnoi,$sql);
$number_row=mysqli_fetch_array($result);
if( $number_row["dem"]>0){
       $_SESSION['old_name'] = $name;
    $_SESSION['old_sdt'] = $sdt;
  
     $_SESSION['old_password'] = $password;
    $_SESSION['old_password_confirm'] = $password_confirm;
    $_SESSION['old_email'] = $email;
    $_SESSION['error']= 'password_already_exists';
   
      header("Location: ../sign_up.php");
    exit();
}
if($password != $password_confirm){
     $_SESSION['old_name'] = $name;
    $_SESSION['old_sdt'] = $sdt;
    $_SESSION['old_email'] = $email;
 
      $_SESSION['old_password'] = $password;
    $_SESSION['old_password_confirm'] = $password_confirm;
       $_SESSION['error']= 'password_mismatch';
   
    header("Location: ../sign_up.php");
    
    exit();
}

$sql=" insert into khachhang (Hoten,SoDT,Email,MatKhau) values ('$name','$sdt','$email','$password') ";
 $_SESSION['name'] = $name;
 
echo "<script>alert('Đăng ký thành công!'); window.location.href='../trangchu.php';</script>";

unset($_SESSION['old_name']);
unset($_SESSION['old_sdt']);
unset($_SESSION['old_email']);
unset($_SESSION['old_password']);
unset($_SESSION['old_password_confirm']);

mysqli_query($ketnoi,$sql);

mysqli_close($ketnoi);
?>