<?php
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}

// Kiểm tra có thông tin tạm trong session không
if (!isset($_SESSION['temp_employee'])) {
    $_SESSION['error'] = "Phiên làm việc đã hết hạn. Vui lòng tạo lại tài khoản.";
    header("Location: create_account.php");
    exit();
}

// Kiểm tra thời gian hết hạn (15 phút)
$created_at = $_SESSION['temp_employee']['created_at'];
if ((time() - $created_at) > 900) { // 900 giây = 15 phút
    unset($_SESSION['temp_employee']);
    $_SESSION['error'] = "Mã xác nhận đã hết hạn. Vui lòng tạo lại tài khoản.";
    header("Location: create_account.php");
    exit();
}

// Lấy thông báo từ session
$success = isset($_SESSION['success']) ? $_SESSION['success'] : "";
$error = isset($_SESSION['error']) ? $_SESSION['error'] : "";
unset($_SESSION['success'], $_SESSION['error']);

$email = $_SESSION['temp_employee']['email'];
$name = $_SESSION['temp_employee']['ten'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận Mã OTP - Nhân Viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
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
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .verify-header {
            background: linear-gradient(90deg, #28a745, #66bb6a);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .verify-body {
            padding: 40px;
        }
        .code-input {
            font-size: 32px;
            text-align: center;
            letter-spacing: 10px;
            font-weight: bold;
        }
        .countdown {
            font-size: 18px;
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="verify-card">
    <div class="verify-header">
        <i class="fa-solid fa-envelope-circle-check fa-3x mb-3"></i>
        <h4>Xác Nhận Email</h4>
        <p class="mb-0">Tạo tài khoản nhân viên</p>
    </div>
    
    <div class="verify-body">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fa-solid fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fa-solid fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="text-center mb-4">
            <p class="mb-2">Mã xác nhận đã được gửi đến:</p>
            <h6 class="text-success"><?php echo htmlspecialchars($email); ?></h6>
            <p class="text-muted small">Nhân viên: <strong><?php echo htmlspecialchars($name); ?></strong></p>
        </div>

        <form method="POST" action="../../process/verify_employee_code.php">
            <div class="mb-4">
                <label class="form-label text-center d-block">Nhập mã xác nhận (6 số)</label>
                <input type="text" name="code" class="form-control code-input" 
                       maxlength="6" pattern="[0-9]{6}" required 
                       placeholder="000000" autofocus>
            </div>

            <div class="text-center mb-3">
                <p class="mb-1">Mã sẽ hết hạn sau:</p>
                <div class="countdown" id="countdown">15:00</div>
            </div>

            <button type="submit" class="btn btn-success w-100 mb-3">
                <i class="fa-solid fa-check"></i> Xác Nhận
            </button>

            <div class="text-center">
                <a href="create_account.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại tạo tài khoản
                </a>
            </div>
        </form>

        <hr class="my-4">

        <div class="text-center">
            <p class="text-muted small mb-2">Không nhận được mã?</p>
            <form method="POST" action="../../process/resend_employee_code.php" style="display:inline;">
                <button type="submit" class="btn btn-link btn-sm" id="resendBtn">
                    <i class="fa-solid fa-rotate-right"></i> Gửi lại mã
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Đếm ngược thời gian
let timeLeft = 900; // 15 phút = 900 giây
const countdownElement = document.getElementById('countdown');
const resendBtn = document.getElementById('resendBtn');

function updateCountdown() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    if (timeLeft <= 0) {
        clearInterval(timer);
        countdownElement.textContent = 'Hết hạn';
        countdownElement.style.color = '#dc3545';
        alert('Mã xác nhận đã hết hạn. Vui lòng tạo lại tài khoản.');
        window.location.href = 'create_account.php';
    } else if (timeLeft <= 60) {
        countdownElement.style.color = '#dc3545';
    }
    
    timeLeft--;
}

const timer = setInterval(updateCountdown, 1000);
updateCountdown();

// Tự động focus và chỉ cho phép nhập số
document.querySelector('.code-input').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>