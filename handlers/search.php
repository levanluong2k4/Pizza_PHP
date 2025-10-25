<?php
$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

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