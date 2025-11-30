<?php
ob_start(); // Bắt output không mong muốn
session_start();

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']) ? true : false;

require '../includes/db_connect.php';

// Hàm trả về JSON và dừng script
function sendJSON($data) {
    ob_end_clean(); // Xóa hết output cũ
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

// Validate input
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJSON([
        'success' => false,
        'error_type' => 'email_format',
        'message' => 'Email không hợp lệ.'
    ]);
}

if (strlen($password) < 6) {
    sendJSON([
        'success' => false,
        'error_type' => 'password_length',
        'message' => 'Mật khẩu tối thiểu 6 ký tự.'
    ]);
}

// --- 1️⃣ Kiểm tra admin ---
$email_escaped = mysqli_real_escape_string($ketnoi, $email);
$sql_admin = "SELECT * FROM admin WHERE email='$email_escaped'";
$result_admin = mysqli_query($ketnoi, $sql_admin);

if (mysqli_num_rows($result_admin) > 0) {
    $admin = mysqli_fetch_assoc($result_admin);

    if (password_verify($password, $admin['password'])) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['name'] = $admin['ten'];
        $_SESSION['role'] = 'admin';
        $_SESSION['phanquyen'] = $admin['phanquyen'];
        
        sendJSON([
            'success' => true,
            'admin' => true
        ]);
    } else {
        $_SESSION['old_email'] = $email;
        sendJSON([
            'success' => false,
            'error_type' => 'password',
            'message' => 'Mật khẩu không đúng.'
        ]);
    }
}

// --- 2️⃣ Kiểm tra khách hàng ---
$sql_user = "SELECT * FROM khachhang WHERE Email='$email_escaped'";
$result_user = mysqli_query($ketnoi, $sql_user);

if (mysqli_num_rows($result_user) > 0) {
    $user = mysqli_fetch_assoc($result_user);
    $id = $user['MaKH'];

    if (password_verify($password, $user['MatKhau'])) {
        $_SESSION['user_id'] = $id;
        $_SESSION['name'] = $user['HoTen'];
        $_SESSION['role'] = 'user';
        
        // Lưu thông tin địa chỉ (NAME để hiển thị)
        $_SESSION['temp_ward'] = $user['xaphuong'];
        $_SESSION['temp_district'] = $user['huyenquan'];
        $_SESSION['temp_province'] = $user['tinhthanhpho'];
        $_SESSION['temp_so_nha'] = $user['sonha'];
        $_SESSION['temp_sodt'] = $user['SoDT'];
        $_SESSION['temp_hoten'] = $user['HoTen'];
        $_SESSION['temp_diachi'] =$user['sonha'].",".$user['xaphuong'].",".$user['huyenquan'].",".$user['tinhthanhpho'] ;
        
        // Lưu CODE để prefill select (nếu có cột mới)
        $_SESSION['old_address'] = [
            'province' => $user['tinh_code'] ?? '', // ⬅️ CODE
            'district' => $user['huyen_code'] ?? '', // ⬅️ CODE
            'ward' => $user['xaphuong'], // ⬅️ NAME (ward không có code)
            'so_nha' => $user['sonha'],
        ];

        // Ghi nhớ đăng nhập
        if ($remember) {
            $token = bin2hex(random_bytes(16));
            $sql_update = "UPDATE khachhang SET token='$token' WHERE MaKH='$id'";
            mysqli_query($ketnoi, $sql_update);
            setcookie('remember', $token, time() + (30 * 24 * 60 * 60), "/");
        }

        sendJSON([
            'success' => true,
            'admin' => false
        ]);
    } else {
        $_SESSION['old_email'] = $email;
        sendJSON([
            'success' => false,
            'error_type' => 'password',
            'message' => 'Mật khẩu không đúng.'
        ]);
    }
} else {
    // Email không tồn tại
    $_SESSION['old_email'] = $email;
    sendJSON([
        'success' => false,
        'error_type' => 'email_not_found',
        'message' => 'Email chưa được đăng ký.'
    ]);
}

mysqli_close($ketnoi);
?>