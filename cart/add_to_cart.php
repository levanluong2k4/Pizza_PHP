<?php
session_start();
$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $masp = intval($_POST['masp']);
    $masize = intval($_POST['masize']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    $key = $masp . '_' . $masize;
    
    // Lấy thông tin sản phẩm + size + ảnh theo size
    $sql = "SELECT sp.TenSP, ss.Anh, s.TenSize 
            FROM sanpham sp
            INNER JOIN sanpham_size ss ON sp.MaSP = ss.MaSP
            INNER JOIN size s ON ss.MaSize = s.MaSize
            WHERE sp.MaSP = $masp AND ss.MaSize = $masize";
    
    $result = mysqli_query($ketnoi, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $product_info = mysqli_fetch_assoc($result);
        
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$key] = array(
                'masp' => $masp,
                'masize' => $masize,
                'tensp' => $product_info['TenSP'],
                'tensize' => $product_info['TenSize'],
                'anh' => $product_info['Anh'], // ✅ ảnh theo size
                'price' => $price,
                'quantity' => $quantity
            );
        }
        
        echo json_encode(array('success' => true, 'message' => 'Đã thêm vào giỏ hàng'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Không tìm thấy sản phẩm'));
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'Phương thức không hợp lệ'));
}

mysqli_close($ketnoi);
?>