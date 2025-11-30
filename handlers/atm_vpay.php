<?php 

  
function processVnpayPayment($order_id, $total_price, $orderInfo,$phuongthucchuyenkhoan) {

    // URL thanh toán sandbox
    $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

    // Cấu hình store
    $vnp_Returnurl = "http://localhost/unitop/backend/lesson/school/project_pizza/order_confirmation.php?order_id=$order_id&phuongthucthanhtoan=$phuongthucchuyenkhoan";
    $vnp_TmnCode = "E2XVD9QA"; 
    $vnp_HashSecret = "1DWMI4Z5SBGQAN3X6HVMA4ZI7TUJTWMH"; 

    // Dữ liệu đơn hàng
    $vnp_TxnRef = "DH" . $order_id . "_" . time();;  // Mã đơn hàng
    $vnp_OrderInfo = $orderInfo;
    $vnp_OrderType = 'billpayment';
    $vnp_Amount = $total_price * 100; // Quy chuẩn theo VNPAY
    $vnp_Locale = 'vn';
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

    // Dữ liệu gửi đến VNPAY
    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef
    );

    // Sắp xếp key A-Z
    ksort($inputData);

    $query = "";
    $hashdata = "";
    $i = 0;

    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }

    // Tạo chữ ký
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

    // Gộp URL
    $paymentUrl = $vnp_Url . "?" . $query . "vnp_SecureHash=" . $vnpSecureHash;

    return [
        "code" => "00",
        "message" => "success",
        "payment_url" => $paymentUrl
    ];
}




function processVnpayPayment_ordertable($order_id, $total_price, $orderInfo,$thanhtoan) {

    // URL thanh toán sandbox
    $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

    // Cấu hình store
    $vnp_Returnurl = "http://localhost/unitop/backend/lesson/school/project_pizza/datban/confirm_booking.php?id=$order_id&thanhtoan=$thanhtoan";
    $vnp_TmnCode = "E2XVD9QA"; 
    $vnp_HashSecret = "1DWMI4Z5SBGQAN3X6HVMA4ZI7TUJTWMH"; 

    // Dữ liệu đơn hàng
    $vnp_TxnRef = "DB" . $order_id . "_" . time();;  // Mã đơn hàng
    $vnp_OrderInfo = $orderInfo;
    $vnp_OrderType = 'billpayment';
    $vnp_Amount = $total_price * 100; // Quy chuẩn theo VNPAY
    $vnp_Locale = 'vn';
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

    // Dữ liệu gửi đến VNPAY
    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef
    );

    // Sắp xếp key A-Z
    ksort($inputData);

    $query = "";
    $hashdata = "";
    $i = 0;

    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }

    // Tạo chữ ký
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

    // Gộp URL
    $paymentUrl = $vnp_Url . "?" . $query . "vnp_SecureHash=" . $vnpSecureHash;

    return [
        "code" => "00",
        "message" => "success",
        "payment_url" => $paymentUrl
    ];
}
?>




