<?php
session_start();


    $action = $_POST['action'];
    $key = $_POST['key'];
    
    if (!isset($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng không tồn tại']);
        exit;
    }
    
    switch ($action) {
        case 'update':
            $quantity = intval($_POST['quantity']);
            if ($quantity > 0 && isset($_SESSION['cart'][$key])) {
                $_SESSION['cart'][$key]['quantity'] = $quantity;
             
            } else {
             
            }
            break;
            
        case 'remove':
            if (isset($_SESSION['cart'][$key])) {
                unset($_SESSION['cart'][$key]);
                echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm']);
            } 
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
            break;
    }

?>