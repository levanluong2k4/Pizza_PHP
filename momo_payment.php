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
    //execute post
    $result = curl_exec($ch);
    //close connection
    curl_close($ch);
    return $result;
}


function momo_payment(Request $request)
  {

    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

    $partnerCode = 'MOMOBKUN20180529';
    $accessKey = 'klm05TvNBzhg7h7j';
    $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
    $orderInfo = "Thanh toán qua ATM MoMo";
    $amount = $_POST['total_momo'];
    $orderId = time() . "";
    $redirectUrl = "http://localhost:8080/weblinhkienmaytinh/checkout";
    $ipnUrl = "http://localhost:8080/weblinhkienmaytinh/checkout";
    $extraData = "";

    $requestId = time() . "";
    $requestType = "payWithATM";
    // $extraData = ($_POST["extraData"] ? $_POST["extraData"] : "");
    //before sign HMAC SHA256 signature
    $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
    $signature = hash_hmac("sha256", $rawHash, $secretKey);
    $data = array(
      'partnerCode' => $partnerCode,
      'partnerName' => "Test",
      "storeId" => "MomoTestStore",
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
    $result = $this->execPostRequest($endpoint, json_encode($data));
    $jsonResult = json_decode($result, true);  // decode json

    //Just a example, please check more in there
    return redirect()->to($jsonResult['payUrl']);

    header('Location: ' . $jsonResult['payUrl']);
  }

  



?>