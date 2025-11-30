<?php
session_start();
require '../includes/db_connect.php';

// Kiểm tra có code từ URL không (khi click link trong email)
$code_from_url = $_GET['code'] ?? '';

// Nếu có code từ URL, kiểm tra luôn
if (!empty($code_from_url)) {
    $code_safe = mysqli_real_escape_string($ketnoi, $code_from_url);
    $sql = "SELECT MaKH, Email, HoTen, ngaytao FROM khachhang WHERE token='$code_safe'";
    $result = mysqli_query($ketnoi, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Lưu vào session
        $_SESSION['verified_reset'] = [
            'user_id' => $user['MaKH'],
            'email' => $user['Email'],
            'verified_at' => time()
        ];
        
        // Chuyển đến trang đổi mật khẩu
        header('Location: reset_password.php');
        exit;
    } else {
        $error_message = 'Mã xác thực không hợp lệ hoặc đã hết hạn!';
    }
}

// Xử lý submit form xác thực
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_code'])) {
    $input_code = trim($_POST['code']);
    
    if (empty($input_code)) {
        $message = 'Vui lòng nhập mã xác thực!';
        $message_type = 'danger';
    } else {
        // Kiểm tra code trong database
        $code_safe = mysqli_real_escape_string($ketnoi, $input_code);
        $sql = "SELECT MaKH, Email, HoTen FROM khachhang WHERE token='$code_safe'";
        $result = mysqli_query($ketnoi, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Lưu vào session
            $_SESSION['verified_reset'] = [
                'user_id' => $user['MaKH'],
                'email' => $user['Email'],
                'verified_at' => time()
            ];
            
            $message = 'Xác thực thành công! Đang chuyển đến trang đặt lại mật khẩu...';
            $message_type = 'success';
            
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'reset_password.php';
                }, 1500);
            </script>";
        } else {
            $message = 'Mã xác thực không đúng! Vui lòng kiểm tra lại.';
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Thực Mã - Pizza Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verify-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        .code-input {
            font-size: 32px;
            text-align: center;
            letter-spacing: 10px;
            font-weight: bold;
        }
        .verify-icon {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="text-center">
            <i class="fas fa-shield-alt verify-icon"></i>
            <h3 class="mb-3">Xác Thực Mã</h3>
            <p class="text-muted mb-4">Nhập mã 6 chữ số đã được gửi đến email của bạn</p>
        </div>

        <?php if($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" id="autoCloseAlert">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            setTimeout(function() {
                var alert = document.getElementById('autoCloseAlert');
                if(alert) new bootstrap.Alert(alert).close();
            }, 3000);
        </script>
        <?php endif; ?>

        <?php if(isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i> <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <input type="text" name="code" class="form-control code-input" 
                       maxlength="6" placeholder="000000" required
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>

            <button type="submit" name="verify_code" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-check"></i> Xác Nhận
            </button>

            <div class="text-center">
                <a href="../forget_password.php" class="text-decoration-none">
                    <i class="fas fa-redo"></i> Gửi lại mã
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>