<?php 
require_once "db_connect.php";

// Load mặc định (Pizza)
$keyword = '%Pizza%';

$stmt = mysqli_prepare($ketnoi, "SELECT sanpham.*, MIN(sps.Gia) as GiaThapNhat 
                                 FROM sanpham 
                                 INNER JOIN sanpham_size as sps ON sps.MaSP = sanpham.MaSP 
                                 WHERE sanpham.TenSP LIKE ? 
                                 GROUP BY sanpham.MaSP");

mysqli_stmt_bind_param($stmt, "s", $keyword);
mysqli_stmt_execute($stmt);
$sanpham_rs = mysqli_stmt_get_result($stmt);

// Lấy thức uống
$thucuong = "SELECT sanpham.MaSP, sanpham.TenSP, sanpham.MoTa, sanpham.Anh, 
            sanpham.MaLoai, sanpham.NgayThem, MIN(sps.Gia) as GiaThapNhat
        FROM sanpham
        INNER JOIN loaisanpham ON sanpham.MaLoai = loaisanpham.MaLoai
        INNER JOIN sanpham_size as sps ON sps.MaSP = sanpham.MaSP
        WHERE loaisanpham.TenLoai = 'Đồ Uống'
        GROUP BY sanpham.MaSP, sanpham.TenSP, sanpham.MoTa, sanpham.Anh, 
                sanpham.MaLoai, sanpham.NgayThem";
$view_thucuong = mysqli_query($ketnoi, $thucuong);

// Lấy Khai Vị
$khaivi = "SELECT sanpham.MaSP, sanpham.TenSP, sanpham.MoTa, sanpham.Anh, 
            sanpham.MaLoai, sanpham.NgayThem, MIN(sps.Gia) as GiaThapNhat
        FROM sanpham
        INNER JOIN loaisanpham ON sanpham.MaLoai = loaisanpham.MaLoai
        INNER JOIN sanpham_size as sps ON sps.MaSP = sanpham.MaSP
        WHERE loaisanpham.TenLoai = 'Khai Vị'
        GROUP BY sanpham.MaSP, sanpham.TenSP, sanpham.MoTa, sanpham.Anh, 
                sanpham.MaLoai, sanpham.NgayThem";
$view_khaivi = mysqli_query($ketnoi, $khaivi);

// Lấy loại sản phẩm
$sql_loai = "SELECT * FROM loaisanpham";
$loai_rs = mysqli_query($ketnoi, $sql_loai);
?>