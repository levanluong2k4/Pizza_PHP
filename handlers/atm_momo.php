<?php
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
    $orderId = "DH" . $order_id . "_" . time(); ; // Mã đơn hàng unique
    $amount = (string)$total_price;
    $requestId = time() . "";
    $requestType = "payWithATM";
    
    // URL callback - Thay bằng domain thật của bạn
    $redirectUrl = "http://localhost/unitop/backend/lesson/school/project_pizza/order_confirmation.php?order_id=$order_id"; // Trang xác nhận thanh toán
    $ipnUrl = "http://localhost/unitop/backend/lesson/school/project_pizza/order_confirmation.php?order_id=$order_id"; // Webhook nhận thông báo từ MoMo
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


function processmomoPayment_ordertable($order_id, $total_price, $orderInfo,$thanhtoan) {
    // Cấu hình MoMo - Môi trường TEST
    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
    $partnerCode = 'MOMOBKUN20180529';
    $accessKey = 'klm05TvNBzhg7h7j';
    $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
    
    // Thông tin giao dịch
    $orderId = "DB" . $order_id . "_" . time();; // Mã đơn hàng unique
    $amount = (string)$total_price;
    $requestId = time() . "";
    $requestType = "payWithATM";
    
    // URL callback - Thay bằng domain thật của bạn
    $redirectUrl = "http://localhost/unitop/backend/lesson/school/project_pizza/datban/confirm_booking.php?id=$order_id&thanhtoan=$thanhtoan"; // Trang xác nhận thanh toán
    $ipnUrl = "http://localhost/unitop/backend/lesson/school/project_pizza/datban/confirm_booking.php?id=$order_id&thanhtoan=$thanhtoan"; // Webhook nhận thông báo từ MoMo
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
        'partnerName' => "PizzaCompany",
        "storeId" => "PizzaCompany",
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
?>
