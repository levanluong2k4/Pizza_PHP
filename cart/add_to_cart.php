<?php
session_start();
require '../includes/db_connect.php';

$product_id = $_GET['id'] ?? '';
$size_id = $_GET['masize'] ?? '';
$quantity = (int)($_GET['soluong'] ?? 1);
$user_id = $_SESSION['user_id'] ?? null;

if (empty($product_id) || empty($size_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin sản phẩm']);
    exit;
}

try {
    if ($user_id) {
        // User đã đăng nhập - Lưu vào database
        
        // Kiểm tra hoặc tạo giỏ hàng
        $sql_cart = "SELECT CartID FROM giohang WHERE MaKH='$user_id'";
        $result = mysqli_query($ketnoi, $sql_cart);
        
        if (mysqli_num_rows($result) == 0) {
            mysqli_query($ketnoi, "INSERT INTO giohang (MaKH) VALUES ('$user_id')");
            $cart_id = mysqli_insert_id($ketnoi);
        } else {
            $row = mysqli_fetch_assoc($result);
            $cart_id = $row['CartID'];
        }
        
        // Kiểm tra sản phẩm đã tồn tại chưa
        $sql_check = "SELECT * FROM chitietgiohang 
                      WHERE CartID='$cart_id' AND MaSP='$product_id' AND MaSize='$size_id'";
        $check_result = mysqli_query($ketnoi, $sql_check);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Cập nhật số lượng
            $sql_update = "UPDATE chitietgiohang 
                          SET Quantity = Quantity + $quantity 
                          WHERE CartID='$cart_id' AND MaSP='$product_id' AND MaSize='$size_id'";
            mysqli_query($ketnoi, $sql_update);
        } else {
            // Thêm mới
            $sql_insert = "INSERT INTO chitietgiohang (CartID, MaSP, MaSize, Quantity) 
                          VALUES ('$cart_id', '$product_id', '$size_id', $quantity)";
            mysqli_query($ketnoi, $sql_insert);
        }
        
        // Lấy tổng số lượng trong giỏ hàng
        $sql_total = "SELECT SUM(Quantity) AS total FROM chitietgiohang WHERE CartID='$cart_id'";
        $total_result = mysqli_query($ketnoi, $sql_total);
        $total_row = mysqli_fetch_assoc($total_result);
        $total_quantity = (int)($total_row['total'] ?? 0);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã thêm vào giỏ hàng',
            'totalQuantity' => $total_quantity
        ]);
        
    } else {
        // User chưa đăng nhập - Lưu vào session
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $key = $product_id . '_' . $size_id;
        
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$key] = [
                'masp' => $product_id,
                'size_id' => $size_id,
                'quantity' => $quantity
            ];
        }
        
        // Tính tổng số lượng
        $total_quantity = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_quantity += $item['quantity'];
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã thêm vào giỏ hàng',
            'totalQuantity' => $total_quantity
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}
?>