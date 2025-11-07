<?php
session_start();
header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']) ? true : false;

require '../includes/db_connect.php';

// --- 1️ Kiểm tra trong bảng admin trước ---
$sql_admin = "SELECT * FROM admin WHERE email='$email'";
$result_admin = mysqli_query($ketnoi, $sql_admin);

if (mysqli_num_rows($result_admin) > 0) {
    $admin = mysqli_fetch_assoc($result_admin);

    // So sánh mật khẩu (hiện bạn dùng plain text)
    if (password_verify($password, $admin['password'])) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['name'] = $admin['ten'];
        $_SESSION['role'] = 'admin';
        $_SESSION['phanquyen'] = $admin['phanquyen']; // 0 hoặc 1
          echo json_encode([
            'success' => true,
            'admin' => true,
        
        ]);
     
        exit();
    } else {
        $_SESSION['old_email'] = $email;
        
        echo json_encode([
        'success' => false,
        'error_type' => 'password',
        'message' => 'Mật khẩu không đúng.'
        ]);
        exit;

        
       
    }
}


// --- 2️⃣ Nếu không phải admin, kiểm tra trong bảng khách hàng ---
$sql_user = "SELECT * FROM khachhang WHERE Email='$email'";
$result_user = mysqli_query($ketnoi, $sql_user);

if (mysqli_num_rows($result_user) > 0) {
    $user = mysqli_fetch_assoc($result_user);
    $id = $user['MaKH'];

    if (password_verify($password, $user['MatKhau'])) {
        $_SESSION['user_id'] = $id;
        $_SESSION['name'] = $user['HoTen'];
        $_SESSION['role'] = 'user';
          echo json_encode([
            'success' => true,
            'admin'=>false,
        
        ]);
       
        // Ghi nhớ đăng nhập (chỉ áp dụng cho khách hàng)
        if ($remember) {
            $token = bin2hex(random_bytes(16));
            $sql_update = "UPDATE khachhang SET token='$token' WHERE MaKH='$id'";
            mysqli_query($ketnoi, $sql_update);
            setcookie('remember', $token, time() + (30 * 24 * 60 * 60), "/");
        }

        exit();
    } else {

        if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'error_type' => 'password_length',
        'message' => 'Mật khẩu tối thiểu 6 ký tự.'
    ]);
    exit();
        }
        else {
            
            $_SESSION['old_email'] = $email;
            echo json_encode([
            'success' => false,
            'error_type' => 'password',
            'message' => 'Mật khẩu không đúng.'
            ]);
            
            exit();
        }
    }
} else {

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'error_type' => 'email_format',
        'message' => 'Email không hợp lệ.'
    ]);
    exit();
    }
    else{
        $_SESSION['old_email'] = $email;
            echo json_encode([
                'success' => false,
                'error_type' => 'email_not_found',
                'message' => 'Email chưa được đăng ký.'
                ]);
                exit;
    }
    // Không tìm thấy email trong cả 2 bảng
   
}

mysqli_close($ketnoi);
?>
