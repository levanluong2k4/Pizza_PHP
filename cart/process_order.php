<?php 
session_start();
require "../includes/db_connect.php";

if (!isset($_SESSION['user_id']) && (!isset($_POST['order_guest']) || !isset($_SESSION['cart']))) {
    header("Location: ../sign_in.php");
    exit();
}

$tennguoinhan = $_SESSION['temp_hoten'];
$sdtnguoinhan = $_SESSION['temp_sodt'];
$diachinguoinhan = $_SESSION['temp_diachi'];
$phuongthucchuyenkhoan = $_POST['payment_method'] ;
$chuyenkhoan = $_POST['transfer_method'] ?? null;

// Hàm gửi request đến MoMo
function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// Hàm xử lý thanh toán MoMo
function processmomoPayment($order_id, $total_price, $orderInfo) {
    // Cấu hình MoMo - Môi trường TEST
    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
    $partnerCode = 'MOMOBKUN20180529';
    $accessKey = 'klm05TvNBzhg7h7j';
    $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
    
    // Thông tin giao dịch
    $orderId = $order_id ; // Mã đơn hàng unique
    $amount = (string)$total_price;
    $requestId = time() . "";
    $requestType = "payWithATM";
    
    // URL callback - Thay bằng domain thật của bạn
    $redirectUrl = "http://localhost/unitop/backend/lesson/school/project_pizza/order_confirmation.php?order_id=$orderId&phuongthucthanhtoan=$phuongthucchuyenkhoan"; // Trang xác nhận thanh toán
    $ipnUrl = "http://localhost/unitop/backend/lesson/school/project_pizza/order_confirmation.php?order_id=$orderId&phuongthucthanhtoan=$phuongthucchuyenkhoan"; // Webhook nhận thông báo từ MoMo
    $extraData = "";
    
    // Tạo chữ ký
    $rawHash = "accessKey=" . $accessKey . 
               "&amount=" . $amount . 
               "&extraData=" . $extraData . 
               "&ipnUrl=" . $ipnUrl . 
               "&orderId=" . $orderId . 
               "&orderInfo=" . $orderInfo . 
               "&partnerCode=" . $partnerCode . 
               "&redirectUrl=" . $redirectUrl . 
               "&requestId=" . $requestId . 
               "&requestType=" . $requestType;
    
    $signature = hash_hmac("sha256", $rawHash, $secretKey);
    
    // Dữ liệu gửi đến MoMo
    $data = array(
        'partnerCode' => $partnerCode,
        'partnerName' => "YourShopName",
        "storeId" => "YourShopStore",
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'lang' => 'vi',
        'extraData' => $extraData,
        'requestType' => $requestType,
        'signature' => $signature
    );
    
    // Gửi request đến MoMo
    $result = execPostRequest($endpoint, json_encode($data));
    $jsonResult = json_decode($result, true);
    
    return $jsonResult;
}

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
        $momoResult = processmomoPayment($order_id, $total_price, $orderInfo);
        
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
    } else {
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

    // Xử lý thanh toán MoMo
    if ($phuongthucchuyenkhoan === 'Chuyển khoản' && $chuyenkhoan === 'momo') {
        $orderInfo = "Thanh toan don hang #" . $order_id;
        $momoResult = processmomoPayment($order_id, $total_price, $orderInfo);
        
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
    } else {
        // Thanh toán tiền mặt
        header("Location: ../order_confirmation.php?order_id=$order_id&phuongthucthanhtoan=$phuongthucchuyenkhoan");
        exit();
    }
}
?>