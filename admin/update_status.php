
<?php
$conn = new mysqli("localhost","root","","php_pizza");

$MaDH = $_POST['MaDH'];
$trangthai = $_POST['trangthai'];

$conn->query("UPDATE donhang SET trangthai='$trangthai' WHERE MaDH=$MaDH");

header("Location: detail.php?MaDH=$MaDH");
exit();
?>