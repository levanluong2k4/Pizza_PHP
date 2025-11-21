<?php 
session_start();
require "../includes/db_connect.php";
require "../handlers/atm_momo.php";
require "../handlers/atm_vpay.php";
if (!isset($_SESSION['user_id']) && (!isset($_POST['order_guest']) || !isset($_SESSION['cart']))) {
    header("Location: ../sign_in.php");
    exit();
}

$tennguoinhan = $_SESSION['temp_hoten'];
$sdtnguoinhan = $_SESSION['temp_sodt'];
$diachinguoinhan = $_SESSION['temp_diachi'];
$phuongthucchuyenkhoan = $_POST['payment_method'] ;
$chuyenkhoan = $_POST['transfer_method'] ?? null;



// --- Trường hợp khách vãng lai ---
if (isset($_POST['order_guest'])) {

    $total_price = 0;
    foreach ($_SESSION['cart'] as $item) {
        $maSP = $item['masp'];
        $maSize = $item['size_id'];
        $soLuong = $item['quantity'];
        $sql_product = "SELECT Gia FROM sanpham_size WHERE MaSP='$maSP' AND MaSize='$maSize'";
        $res = mysqli_query($ketnoi, $sql_product);
        $row = mysqli_fetch_assoc($res);
        $price = $row['Gia'];
        $total_price += $price * $soLuong;
    }

    // Lưu phương thức thanh toán
    
    $order_id=time();
    $sql_order = "INSERT INTO donhang (MaDHcode,TongTien, diachinguoinhan, sdtnguoinhan, Tennguoinhan, is_guest, phuongthucthanhtoan)
                  VALUES ('$order_id','$total_price', '$diachinguoinhan', '$sdtnguoinhan', '$tennguoinhan', 1, '$phuongthucchuyenkhoan')";
    mysqli_query($ketnoi, $sql_order);
    $id_main = mysqli_insert_id($ketnoi);
    $_SESSION['is_guest'] = 1;

    foreach ($_SESSION['cart'] as $item) {
        $maSP = $item['masp'];
        $maSize = $item['size_id'];
        $soLuong = $item['quantity'];
        $sql_product = "SELECT Gia FROM sanpham_size WHERE MaSP='$maSP' AND MaSize='$maSize'";
        $res = mysqli_query($ketnoi, $sql_product);
        $row = mysqli_fetch_assoc($res);
        $price = $row['Gia'];
        $thanh_tien = $price * $soLuong;

        mysqli_query($ketnoi, "INSERT INTO chitietdonhang (MaDH, MaSP, MaSize, SoLuong, ThanhTien)
                               VALUES ('$id_main', '$maSP', '$maSize', '$soLuong', '$thanh_tien')");
    }

    unset($_SESSION['cart']);
    
    // Xử lý thanh toán MoMo
    if ($phuongthucchuyenkhoan === 'Chuyển khoản' && $chuyenkhoan === 'momo') {
        $orderInfo = "Thanh toan don hang #" . $order_id;
        $momoResult = processmomoPayment($order_id, $total_price, $orderInfo,$phuongthucchuyenkhoan);
        
        if (isset($momoResult['payUrl'])) {
            
            // Chuyển hướng đến trang thanh toán MoMo
            header("Location: " . $momoResult['payUrl']);
            exit();
        } else {
            // Lỗi khi tạo thanh toán MoMo
            $_SESSION['error'] = "Không thể kết nối đến MoMo. Vui lòng thử lại.";
            header("Location: ../cart.php");
            exit();
        }
    } else if($phuongthucchuyenkhoan === 'Chuyển khoản' && $chuyenkhoan === 'vnpay') {
            $orderInfo = "Thanh toan don hang #" . $order_id;

    // Gọi hàm xử lý VNPAY giống như momo
    $vnpayResult = processVnpayPayment($order_id, $total_price, $orderInfo,$phuongthucchuyenkhoan);
       
    if (isset($vnpayResult['payment_url'])) {
        // Chuyển hướng đến trang thanh toán VNPAY
        header("Location: " . $vnpayResult['payment_url']);
        exit();
    } else {
        // Lỗi khi tạo thanh toán
        $_SESSION['error'] = "Không thể kết nối đến VNPAY. Vui lòng thử lại.";
        header("Location: ../cart.php");
        exit();
    }
      
    }
    else {
          // Thanh toán tiền mặt
        header("Location: ../order_confirmation.php?order_id=$order_id&phuongthucthanhtoan=$phuongthucchuyenkhoan");
        exit();
    }
}

// --- Trường hợp khách đăng nhập ---
else {
    $user_id = $_SESSION['user_id'];
    $total_price = 0;

    $res_cart = mysqli_query($ketnoi, "SELECT * FROM giohang WHERE MaKH='$user_id'");
    $cart = mysqli_fetch_assoc($res_cart);
    $cartId = $cart['CartID'];

    $res_items = mysqli_query($ketnoi, "SELECT * FROM chitietgiohang WHERE CartID='$cartId'");
    while ($item = mysqli_fetch_assoc($res_items)) {
        $maSP = $item['MaSP'];
        $maSize = $item['MaSize'];
        $quantity = $item['Quantity'];
        $res_price = mysqli_query($ketnoi, "SELECT Gia FROM sanpham_size WHERE MaSP='$maSP' AND MaSize='$maSize'");
        $p = mysqli_fetch_assoc($res_price);
        $price = $p['Gia'];
        $total_price += $price * $quantity;
    }

   
    $order_id=time();
    mysqli_query($ketnoi, "INSERT INTO donhang (MaDHcode,MaKH, TongTien, diachinguoinhan, sdtnguoinhan, Tennguoinhan, is_guest, phuongthucthanhtoan)
                           VALUES ('$order_id','$user_id', '$total_price', '$diachinguoinhan', '$sdtnguoinhan', '$tennguoinhan', 0, '$phuongthucchuyenkhoan')");
    
    $id_main = mysqli_insert_id($ketnoi);
    mysqli_data_seek($res_items, 0);
    while ($item = mysqli_fetch_assoc($res_items)) {
        $maSP = $item['MaSP'];
        $maSize = $item['MaSize'];
        $quantity = $item['Quantity'];
        $res_price = mysqli_query($ketnoi, "SELECT Gia FROM sanpham_size WHERE MaSP='$maSP' AND MaSize='$maSize'");
        $p = mysqli_fetch_assoc($res_price);
        $price = $p['Gia'];
        $thanh_tien = $price * $quantity;
        mysqli_query($ketnoi, "INSERT INTO chitietdonhang (MaDH, MaSP, MaSize, SoLuong, ThanhTien)
                               VALUES ('$id_main', '$maSP', '$maSize', '$quantity', '$thanh_tien')");
    }

    // Xóa giỏ hàng
    mysqli_query($ketnoi, "DELETE FROM chitietgiohang WHERE CartID='$cartId'");
    mysqli_query($ketnoi, "DELETE FROM giohang WHERE MaKH='$user_id'");

 
    if ($phuongthucchuyenkhoan === 'Chuyển khoản' && $chuyenkhoan === 'momo') {
        $orderInfo = "Thanh toan don hang #" . $order_id;
        $momoResult = processmomoPayment($order_id, $total_price, $orderInfo,$phuongthucchuyenkhoan);
        
        if (isset($momoResult['payUrl'])) {
            
            
            // Chuyển hướng đến trang thanh toán MoMo
            header("Location: " . $momoResult['payUrl']);
            exit();
        } else {
            // Lỗi khi tạo thanh toán MoMo
            $_SESSION['error'] = "Không thể kết nối đến MoMo. Vui lòng thử lại.";
            header("Location: ../cart.php");
            exit();
        }
    } 
else if($phuongthucchuyenkhoan === 'Chuyển khoản' && $chuyenkhoan === 'vnpay') {
    $orderInfo = "Thanh toan don hang #" . $order_id;
    
   
    $vnpayResult = processVnpayPayment($order_id, $total_price, $orderInfo, $phuongthucthanhtoan);
    
    if (isset($vnpayResult['payment_url'])) {
        // Chuyển hướng đến trang thanh toán VNPAY
        header("Location: " . $vnpayResult['payment_url']);
        exit();
    } else {
        // Lỗi khi tạo thanh toán
        $_SESSION['error'] = "Không thể kết nối đến VNPAY. Vui lòng thử lại.";
        header("Location: ../cart.php");
        exit();
    }
}
    else{
           // Thanh toán tiền mặt
        header("Location: ../order_confirmation.php?order_id=$order_id&phuongthucthanhtoan=$phuongthucchuyenkhoan");
        exit();
    }
}
?>