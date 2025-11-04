<?php 
require "db_connect.php";

// Xử lý lấy thông tin sản phẩm khi click "Mua ngay"
if (isset($_GET['id']) && !isset($_GET['maloai'])) {
    $id = intval($_GET['id']);
    
    // Lấy thông tin sản phẩm
    $sql_sp_info = "SELECT * FROM sanpham WHERE MaSP = $id";
    $sp_info_result = mysqli_query($ketnoi, $sql_sp_info);
    $sp_info = mysqli_fetch_assoc($sp_info_result);
    
    // Lấy các size
    $sqlsize = "SELECT ss.MaSize, s.TenSize, ss.Gia, ss.Anh
                FROM sanpham_size ss
                INNER JOIN size s ON ss.MaSize = s.MaSize
                WHERE ss.MaSP = $id
                ORDER BY s.MaSize";
    $sanphamsize = mysqli_query($ketnoi, $sqlsize);
    
    $sizes = array();
    if ($sanphamsize && mysqli_num_rows($sanphamsize) > 0) {
        while ($row = mysqli_fetch_assoc($sanphamsize)) {
            $sizes[] = $row;
        }
    }
    
    // Trả về JSON
    header('Content-Type: application/json');
    echo json_encode([
        'product' => $sp_info,
        'sizes' => $sizes
    ]);
    exit;
}

// Lấy sản phẩm theo loại (cho category filter)
if (isset($_GET['maloai'])) {
    $maloai = intval($_GET['maloai']);
    $sql_sp = "SELECT * FROM sanpham WHERE MaLoai = $maloai";
    $sanpham_rs = mysqli_query($ketnoi, $sql_sp);

    if (mysqli_num_rows($sanpham_rs) > 0) {
        foreach ($sanpham_rs as $sp): 
            include "../components/product_card.php"; 
        endforeach; 
    } else {
        echo '<p>Không có sản phẩm nào trong danh mục này.</p>';
    }
    exit;
}

// Load mặc định (Pizza)
$sql_sp = "SELECT * FROM sanpham WHERE TenSP LIKE '%Pizza%'";
$sanpham_rs = mysqli_query($ketnoi, $sql_sp);

// Lấy thức uống
$thucuong = "SELECT sanpham.MaSP, sanpham.TenSP, sanpham.MoTa, sanpham.Anh, sanpham.MaLoai, sanpham.NgayThem
             FROM sanpham, loaisanpham
             WHERE sanpham.MaLoai = loaisanpham.MaLoai
             AND loaisanpham.TenLoai = 'Đồ Uống'";
$view_thucuong = mysqli_query($ketnoi, $thucuong);

// Lấy Khai Vị
$khaivi = "SELECT sanpham.MaSP, sanpham.TenSP, sanpham.MoTa, sanpham.Anh, sanpham.MaLoai, sanpham.NgayThem
           FROM sanpham, loaisanpham
           WHERE sanpham.MaLoai = loaisanpham.MaLoai
           AND loaisanpham.TenLoai = 'Khai Vị'";
$view_khaivi = mysqli_query($ketnoi, $khaivi);

// Lấy loại sản phẩm
$sql_loai = "SELECT * FROM loaisanpham";
$loai_rs = mysqli_query($ketnoi, $sql_loai);
?>