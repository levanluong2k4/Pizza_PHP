<?php
require '../includes/db_connect.php';

if (isset($_GET['q'])) {
    $keyword = mysqli_real_escape_string($ketnoi, $_GET['q']);
 $sql = "
SELECT 
    sp.MaSP,
    sp.TenSP,
    MIN(ss.Gia) AS Gia,
    MIN(ss.Anh) AS Anh
FROM sanpham AS sp
JOIN sanpham_size AS ss ON sp.MaSP = ss.MaSP
JOIN size AS s ON s.MaSize = ss.MaSize
WHERE sp.TenSP LIKE '%$keyword%'
GROUP BY sp.MaSP, sp.TenSP
LIMIT 5
";

    $result = mysqli_query($ketnoi, $sql);

   header('Content-Type: application/json; charset=utf-8');
$suggestions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $suggestions[] = $row;
}
echo json_encode($suggestions, JSON_UNESCAPED_UNICODE);

}
?>