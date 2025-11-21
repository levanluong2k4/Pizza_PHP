<?php 
session_start();
require '../includes/db_connect.php';
require "atm_momo.php";
require "atm_vpay.php";

if(isset($_POST["btn_pay_order"])){
    $order_id = $_POST["order_id"] ?? "";
    $chuyenkhoan = $_POST["transfer_method"] ?? "";

    $sql = "SELECT * FROM donhang WHERE MaDHcode='$order_id'";
    $result_order_id = mysqli_query($ketnoi, $sql);
    $order = mysqli_fetch_array($result_order_id);
    $total_price =intval($order["TongTien"]) ;
    $phuongthucchuyenkhoan = $order["phuongthucthanhtoan"];

    if ($chuyenkhoan === 'momo') {
        $orderInfo = "Thanh toan don hang #" . $order_id;
        $momoResult = processmomoPayment($order_id, $total_price, $orderInfo, $phuongthucchuyenkhoan);
        
        // ✅ CHỈ CHECK payUrl, BỎ QUA resultCode
        if (isset($momoResult['payUrl'])) {
            header("Location: " . $momoResult['payUrl']);
            exit();
        } else {
            // ✅ DEBUG: Hiển thị lỗi chi tiết
            $_SESSION['error'] = "Lỗi MoMo: " . ($momoResult['message'] ?? 'Không xác định');
    
        
            
            header("Location: ../cart.php");
            exit();
        }
    } 
    else if($chuyenkhoan === 'vnpay') {
        $orderInfo = "Thanh toan don hang #" . $order_id;
        $vnpayResult = processVnpayPayment($order_id, $total_price, $orderInfo, $phuongthucchuyenkhoan);
       
         
        if (isset($vnpayResult['payment_url'])) {
            header("Location: " . $vnpayResult['payment_url']);
            exit();
        } else {
            $_SESSION['error'] = "Không thể kết nối đến VNPAY. Vui lòng thử lại.";
            header("Location: ../cart.php");
            exit();
        }
    }
}
?>