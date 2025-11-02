<?php 

require "includes/db_connect.php";

// lấy thức uống
$thucuong="SELECT sanpham.MaSP,
sanpham.TenSP,
sanpham.MoTa,
sanpham.Anh,
sanpham.MaLoai,
sanpham.NgayThem
FROM sanpham, loaisanpham
WHERE sanpham.MaLoai = loaisanpham.MaLoai
AND loaisanpham.TenLoai = 'Đồ Uống';
";
$view_thucuong=mysqli_query($ketnoi,$thucuong);

// lấy Khai Vị
$khaivi="SELECT sanpham.MaSP,
sanpham.TenSP,
sanpham.MoTa,
sanpham.Anh,
sanpham.MaLoai,
sanpham.NgayThem
FROM sanpham, loaisanpham
WHERE sanpham.MaLoai = loaisanpham.MaLoai
AND loaisanpham.TenLoai = 'Khai Vị';
";
$view_khaivi=mysqli_query($ketnoi,$khaivi);

// Lấy sản phẩm
if (isset($_GET['maloai'])) {
$maloai=$_GET['maloai'] ?? '';
$sql_sp = "SELECT * FROM sanpham WHERE MaLoai =$maloai";
} else {

$sql_sp = "SELECT * FROM sanpham WHERE TenSP LIKE '%Pizza%'";

}
$sanpham_rs = mysqli_query($ketnoi, $sql_sp);

// Lấy loại sản phẩm
$sql_loai = "SELECT * FROM loaisanpham";
$loai_rs = mysqli_query($ketnoi, $sql_loai);

// Xử lý thêm sản phẩm vào giỏ hàng

if (isset($_GET['id'])) {
$id=$_GET['id'] ?? '';
$sqlsize = "SELECT ss.MaSize, s.TenSize, ss.Gia , ss.Anh
FROM sanpham_size ss
INNER JOIN size s ON ss.MaSize = s.MaSize
WHERE ss.MaSP = $id
ORDER BY s.MaSize";

$sanphamsize = mysqli_query($ketnoi, $sqlsize);
$sizes = array();
if ($sanphamsize && mysqli_num_rows($sanphamsize) > 0
) {
while ($row = mysqli_fetch_assoc($sanphamsize)) {
$sizes[] = $row;
}

}

// Lấy thông tin sản phẩm
$sql_sp_info = "SELECT * FROM sanpham WHERE MaSP = $id";
$sp_info_result = mysqli_query($ketnoi, $sql_sp_info);
$sp_info = mysqli_fetch_assoc($sp_info_result);

}

?>