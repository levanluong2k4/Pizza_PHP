<?php
$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

if (isset($_POST['masp'])) {
    $masp = intval($_POST['masp']);
    
    // Lấy thông tin size và giá của sản phẩm
    $sql = "SELECT ss.MaSize, s.TenSize, ss.Gia 
            FROM sanpham_size ss 
            INNER JOIN size s ON ss.MaSize = s.MaSize 
            WHERE ss.MaSP = $masp 
            ORDER BY s.MaSize";
    
    $result = mysqli_query($ketnoi, $sql);
    $sizes = array();
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sizes[] = $row;
        }
    }
    
    echo json_encode($sizes);
} else {
    echo json_encode(array());
}

mysqli_close($ketnoi);
?>