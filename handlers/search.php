<?php
require '../includes/db_connect.php';

if (isset($_GET['q'])) {
    $keyword = mysqli_real_escape_string($ketnoi, $_GET['q']);
    $sql = "SELECT MaSP, TenSP,Anh FROM sanpham 
            WHERE TenSP LIKE '%$keyword%' 
            LIMIT 5";
    $result = mysqli_query($ketnoi, $sql);

   header('Content-Type: application/json; charset=utf-8');
$suggestions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $suggestions[] = $row;
}
echo json_encode($suggestions, JSON_UNESCAPED_UNICODE);

}
?>