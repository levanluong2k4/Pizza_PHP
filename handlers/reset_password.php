<?php
session_start();
require '../includes/db_connect.php';

// Kiểm tra đã xác thực chưa
if (!isset($_SESSION['verified_reset'])) {
    header('Location: ../forget_password.php');
    exit;
}

// Kiểm tra thời gian xác thực (5 phút)
$verified_time = $_SESSION['verified_reset']['verified_at'];
if (time() - $verified_time > 300) { // 300 giây = 5 phút
    unset($_SESSION['verified_reset']);
    header('Location: ../forget_password.php?error=expired');
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($new_password) || empty($confirm_password)) {
        $message = 'Vui lòng điền đầy đủ thông tin!';
        $message_type = 'danger';
    } elseif (strlen($new_password) < 6) {
        $message = 'Mật khẩu phải có ít nhất 6 ký tự!';
        $message_type = 'danger';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Mật khẩu xác nhận không khớp!';
        $message_type = 'danger';
    } else {
        $user_id = $_SESSION['verified_reset']['user_id'];
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Cập nhật mật khẩu và xóa token
        $sql = "UPDATE khachhang SET MatKhau='$hashed_password', token=NULL WHERE MaKH='$user_id'";
        
        if (mysqli_query($ketnoi, $sql)) {
            $message = 'Đặt lại mật khẩu thành công! Đang chuyển đến trang đăng nhập...';
            $message_type = 'success';
            
            // Xóa session
            unset($_SESSION['verified_reset']);
            
            echo "<script>
                setTimeout(function() {
                    window.location.href = '../sign_in.php';
                }, 2000);
            </script>";
        } else {
            $message = 'Có lỗi xảy ra! Vui lòng thử lại.';
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
    <title>Đặt Lại Mật Khẩu - Pizza Store</title>
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
        .reset-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        .reset-icon {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="text-center">
            <i class="fas fa-key reset-icon"></i>
            <h3 class="mb-3">Đặt Lại Mật Khẩu</h3>
            <p class="text-muted mb-4">Nhập mật khẩu mới cho tài khoản của bạn</p>
        </div>

        <?php if($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" id="autoCloseAlert">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Mật khẩu mới * (tối thiểu 6 ký tự)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="new_password" 
                           id="new_password" minlength="6" required>
                    <button class="btn btn-outline-secondary" type="button" 
                            onclick="togglePassword('new_password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Xác nhận mật khẩu mới *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="confirm_password" 
                           id="confirm_password" minlength="6" required>
                    <button class="btn btn-outline-secondary" type="button" 
                            onclick="togglePassword('confirm_password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" name="reset_password" class="btn btn-primary w-100">
                <i class="fas fa-check"></i> Đặt Lại Mật Khẩu
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>