<?php
function verifyEmailSMTP($email) {
    // Lấy domain từ email
    $domain = substr(strrchr($email, "@"), 1);
    
    // Kiểm tra MX record
    if (!getmxrr($domain, $mx_records, $mx_weight)) {
        return false; // Không có mail server
    }
    
    // Sắp xếp MX records theo priority
    array_multisort($mx_weight, $mx_records);
    
    // Kết nối đến mail server đầu tiên
    $mx_host = $mx_records[0];
    
    // Mở kết nối SMTP
    $connect = @fsockopen($mx_host, 25, $errno, $errstr, 5);
    
    if (!$connect) {
        return false; // Không thể kết nối
    }
    
    // Đọc response ban đầu
    $response = fgets($connect, 1024);
    
    // Gửi HELO
    fputs($connect, "HELO pizza-store.com\r\n");
    $response = fgets($connect, 1024);
    
    // Gửi MAIL FROM
    fputs($connect, "MAIL FROM: <verify@pizza-store.com>\r\n");
    $response = fgets($connect, 1024);
    
    // Gửi RCPT TO (kiểm tra email có tồn tại)
    fputs($connect, "RCPT TO: <{$email}>\r\n");
    $response = fgets($connect, 1024);
    
    // Đóng kết nối
    fputs($connect, "QUIT\r\n");
    fclose($connect);
    
    // Kiểm tra response code
    // 250 = OK, 251 = User not local
    // 550 = User unknown, 553 = Mailbox name not allowed
    if (preg_match('/^250|^251/', $response)) {
        return true; // Email tồn tại
    }
    
    return false; // Email không tồn tại
}
?>