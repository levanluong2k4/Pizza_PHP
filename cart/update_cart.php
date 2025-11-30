<?php
session_start();
header('Content-Type: application/json');

$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

$id_product = $_POST['masp'] ?? '';
$maSize = $_POST['masize'] ?? '';
$type = $_POST['type'] ?? '';

$response = array(
    'success' => false,
    'message' => '',
    'quantity' => 0,
    'subtotal' => 0,
    'total' => 0,
    'cartCount' => 0
);

if ($id_product && $type) {
    
    // NGƯỜI DÙNG ĐÃ ĐĂNG NHẬP
    if (isset($_SESSION['user_id'])) {
        $user_id = mysqli_real_escape_string($ketnoi, $_SESSION['user_id']);
        
        $sql_cart_id = "SELECT CartID FROM giohang WHERE MaKH='$user_id'";
        $result_cart = mysqli_query($ketnoi, $sql_cart_id);
        
        if ($result_cart && mysqli_num_rows($result_cart) > 0) {
            $cart_row = mysqli_fetch_assoc($result_cart);
            $cartId = $cart_row['CartID'];
            
            $id_product = mysqli_real_escape_string($ketnoi, $id_product);
            $maSize = mysqli_real_escape_string($ketnoi, $maSize);
            
            if ($type === 'increase') {
                $sql_update = "UPDATE chitietgiohang 
                              SET Quantity = Quantity + 1 
                              WHERE CartID='$cartId' AND MaSP='$id_product' AND MaSize='$maSize'";
                
                if (mysqli_query($ketnoi, $sql_update)) {
                    $response['success'] = true;
                    $response['message'] = 'Đã tăng số lượng sản phẩm';
                }
                
            } elseif ($type === 'decrease') {
                // Kiểm tra số lượng hiện tại
                $sql_check = "SELECT Quantity FROM chitietgiohang 
                             WHERE CartID='$cartId' AND MaSP='$id_product' AND MaSize='$maSize'";
                $result_check = mysqli_query($ketnoi, $sql_check);
                
                if ($result_check && mysqli_num_rows($result_check) > 0) {
                    $item = mysqli_fetch_assoc($result_check);
                    
                    if ($item['Quantity'] > 1) {
                        $sql_update = "UPDATE chitietgiohang 
                                      SET Quantity = Quantity - 1 
                                      WHERE CartID='$cartId' AND MaSP='$id_product' AND MaSize='$maSize'";
                        
                        if (mysqli_query($ketnoi, $sql_update)) {
                            $response['success'] = true;
                            $response['message'] = 'Đã giảm số lượng sản phẩm';
                        }
                    } else {
                        $response['message'] = 'Số lượng tối thiểu là 1';
                    }
                }
                
            } elseif ($type === 'delete') {
                $sql_delete = "DELETE FROM chitietgiohang 
                              WHERE CartID='$cartId' AND MaSP='$id_product' AND MaSize='$maSize'";
                
                if (mysqli_query($ketnoi, $sql_delete)) {
                    $response['success'] = true;
                    $response['message'] = 'Đã xóa sản phẩm khỏi giỏ hàng';
                }
            }
            
            // Lấy thông tin sản phẩm sau khi cập nhật (nếu không phải xóa)
            if ($type !== 'delete') {
                $sql_item = "SELECT ct.Quantity, ss.Gia 
                            FROM chitietgiohang ct
                            JOIN sanpham_size ss ON ct.MaSP = ss.MaSP AND ct.MaSize = ss.MaSize
                            WHERE ct.CartID='$cartId' AND ct.MaSP='$id_product' AND ct.MaSize='$maSize'";
                $result_item = mysqli_query($ketnoi, $sql_item);
                
                if ($result_item && mysqli_num_rows($result_item) > 0) {
                    $item_data = mysqli_fetch_assoc($result_item);
                    $response['quantity'] = (int)$item_data['Quantity'];
                    $response['subtotal'] = (int)($item_data['Quantity'] * $item_data['Gia']);
                }
            }
            
            // Tính tổng tiền giỏ hàng
            $sql_total = "SELECT SUM(ct.Quantity * ss.Gia) as total 
                         FROM chitietgiohang ct
                         JOIN sanpham_size ss ON ct.MaSP = ss.MaSP AND ct.MaSize = ss.MaSize
                         WHERE ct.CartID='$cartId'";
            $result_total = mysqli_query($ketnoi, $sql_total);
            
            if ($result_total) {
                $total_data = mysqli_fetch_assoc($result_total);
                $response['total'] = (int)($total_data['total'] ?? 0);
            }
            
            // Tính tổng số lượng sản phẩm trong giỏ
            $sql_count = "SELECT SUM(Quantity) as count FROM chitietgiohang WHERE CartID='$cartId'";
            $result_count = mysqli_query($ketnoi, $sql_count);
            
            if ($result_count) {
                $count_data = mysqli_fetch_assoc($result_count);
                $response['cartCount'] = (int)($count_data['count'] ?? 0);
            }
        } else {
            $response['message'] = 'Không tìm thấy giỏ hàng';
        }
    }
    
    // NGƯỜI DÙNG CHƯA ĐĂNG NHẬP
    else {
        $cart_key = $id_product . '_' . $maSize;
        
        if (isset($_SESSION['cart'][$cart_key])) {
            
            if ($type === 'increase') {
                $_SESSION['cart'][$cart_key]['quantity']++;
                $_SESSION['cart'][$cart_key]['subtotal'] = 
                    $_SESSION['cart'][$cart_key]['price'] * 
                    $_SESSION['cart'][$cart_key]['quantity'];
                
                $response['success'] = true;
                $response['message'] = 'Đã tăng số lượng sản phẩm';
                
            } elseif ($type === 'decrease') {
                if ($_SESSION['cart'][$cart_key]['quantity'] > 1) {
                    $_SESSION['cart'][$cart_key]['quantity']--;
                    $_SESSION['cart'][$cart_key]['subtotal'] = 
                        $_SESSION['cart'][$cart_key]['price'] * 
                        $_SESSION['cart'][$cart_key]['quantity'];
                    
                    $response['success'] = true;
                    $response['message'] = 'Đã giảm số lượng sản phẩm';
                } else {
                    $response['message'] = 'Số lượng tối thiểu là 1';
                }
                
            } elseif ($type === 'delete') {
                unset($_SESSION['cart'][$cart_key]);
                $response['success'] = true;
                $response['message'] = 'Đã xóa sản phẩm khỏi giỏ hàng';
            }
            
            if ($type !== 'delete' && isset($_SESSION['cart'][$cart_key])) {
                $response['quantity'] = $_SESSION['cart'][$cart_key]['quantity'];
                $response['subtotal'] = $_SESSION['cart'][$cart_key]['subtotal'];
            }
            
            // Tính tổng tiền
            $total = 0;
            $totalCount = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['subtotal'];
                $totalCount += $item['quantity'];
            }
            $response['total'] = $total;
            $response['cartCount'] = $totalCount;
        } else {
            $response['message'] = 'Không tìm thấy sản phẩm trong giỏ hàng';
        }
    }
}

mysqli_close($ketnoi);
echo json_encode($response);
exit;
?>
