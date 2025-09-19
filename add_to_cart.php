<?php
session_start();
$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $masp = intval($_POST['masp']);
    $masize = intval($_POST['masize']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    
    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Tạo key duy nhất cho sản phẩm (MaSP + MaSize)
    $key = $masp . '_' . $masize;
    
    // Lấy thông tin sản phẩm và size để lưu vào session
    $sql = "SELECT sp.TenSP, sp.Anh, s.TenSize 
            FROM sanpham sp, size s 
            WHERE sp.MaSP = $masp AND s.MaSize = $masize";
    
    $result = mysqli_query($ketnoi, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $product_info = mysqli_fetch_assoc($result);
        
        // Kiểm tra nếu sản phẩm đã có trong giỏ hàng
        if (isset($_SESSION['cart'][$key])) {
            // Cộng thêm số lượng
            $_SESSION['cart'][$key]['quantity'] += $quantity;
        } else {
            // Thêm sản phẩm mới vào giỏ hàng
            $_SESSION['cart'][$key] = array(
                'masp' => $masp,
                'masize' => $masize,
                'tensp' => $product_info['TenSP'],
                'tensize' => $product_info['TenSize'],
                'anh' => $product_info['Anh'],
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