<?php
session_start();
if (!isset($_SESSION['temp_user'])) {
    header("Location: sign_up.php");
    exit();
}

$error = "";
$success = "";

// Xử lý gửi lại mã
if (isset($_POST['resend_code'])) {
    require 'includes/send_mail.php';
    
    // Tạo mã mới
    $verification_code = rand(100000, 999999);
    $_SESSION['temp_user']['code'] = $verification_code;
    $_SESSION['temp_user']['created_at'] = time();
    
    // Gửi email
    $email = $_SESSION['temp_user']['email'];
    $name = $_SESSION['temp_user']['name'];
    $subject = "Mã xác nhận đăng ký Pizza Store";
    $body = "<p>Xin chào <b>$name</b>,</p>
    <p>Mã xác nhận mới của bạn là: <b style='font-size:18px;'>$verification_code</b></p>
    <p>Mã này có hiệu lực trong <b>200 giây</b>.</p>";
    
    $result = sendMail($email, $name, $subject, $body);
    
    if ($result === true) {
        $success = "✅ Mã xác nhận mới đã được gửi!";
    } else {
        $error = "❌ Không thể gửi email. Vui lòng thử lại.";
    }
}

// Xử lý xác nhận mã
if (isset($_POST['verify_code'])) {
    $input_code = $_POST['code'];
    $current_time = time();
    $code_age = $current_time - $_SESSION['temp_user']['created_at'];
    
    // Kiểm tra mã hết hạn (50 giây)
    if ($code_age > 200) {
        $error = "❌ Mã xác nhận đã hết hạn. Vui lòng nhấn 'Gửi lại mã'.";
    } elseif ($input_code == $_SESSION['temp_user']['code']) {
        require 'includes/db_connect.php';

        $name = $_SESSION['temp_user']['name'];
        $sdt = $_SESSION['temp_user']['sdt'];
        $email = $_SESSION['temp_user']['email'];
        $password = $_SESSION['temp_user']['password'];

        $sql = "INSERT INTO khachhang (Hoten, SoDT, Email, MatKhau) VALUES ('$name', '$sdt', '$email', '$password')";
        mysqli_query($ketnoi, $sql);
        
        $sql_user = "SELECT * FROM khachhang WHERE Email='$email'";
        $result_user = mysqli_query($ketnoi, $sql_user);
        $user = mysqli_fetch_array($result_user);
        
        $_SESSION['user_id'] = $user['MaKH'];
        $_SESSION['name'] = $name;

        // Xóa session tạm
        unset($_SESSION['temp_user']);
        unset($_SESSION['old_name']);
        unset($_SESSION['old_sdt']);
        unset($_SESSION['old_email']);
        unset($_SESSION['old_password']);
        unset($_SESSION['old_password_confirm']);

        echo "<script>alert('Đăng ký thành công!'); window.location.href='trangchu.php';</script>";
        exit();
    } else {
        $error = "❌ Mã xác nhận không đúng. Vui lòng thử lại.";
    }
}


$time_left = 200 - (time() - $_SESSION['temp_user']['created_at']);
if ($time_left < 0) $time_left = 0;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .timer {
            font-size: 2rem;
            font-weight: bold;
            color: #dc3545;
        }
        .timer.expired {
            color: #6c757d;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="card shadow p-4" style="width: 450px;">
        <h4 class="mb-3 text-center">Nhập mã xác nhận</h4>
        
        <!-- Đồng hồ đếm ngược -->
        <div class="text-center mb-3">
            <p class="mb-1">Mã hết hạn sau:</p>
            <div class="timer" id="timer"><?php echo $time_left; ?></div>
        </div>

        <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

        <form method="post" id="verifyForm">
            <input type="text" name="code" id="codeInput" class="form-control mb-3 text-center fs-4" 
                   placeholder="Nhập mã 6 số" maxlength="6" required 
                   pattern="[0-9]{6}" inputmode="numeric">
            <button type="submit" name="verify_code" class="btn btn-primary w-100 mb-2" id="verifyBtn">
                Xác nhận
            </button>
        </form>

        <form method="post">
            <button type="submit" name="resend_code" class="btn btn-outline-secondary w-100" id="resendBtn">
                Gửi lại mã
            </button>
        </form>

        <a href="sign_up.php" class="text-center d-block mt-3">← Quay lại đăng ký</a>
    </div>

    <script>
        let timeLeft = <?php echo $time_left; ?>;
        const timerElement = document.getElementById('timer');
        const verifyBtn = document.getElementById('verifyBtn');
        const resendBtn = document.getElementById('resendBtn');
        const codeInput = document.getElementById('codeInput');

        // Cập nhật đồng hồ đếm ngược
        const countdown = setInterval(() => {
            timeLeft--;
            timerElement.textContent = timeLeft;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerElement.textContent = "Hết hạn";
                timerElement.classList.add('expired');
                verifyBtn.disabled = true;
                codeInput.disabled = true;
                
                // Hiển thị thông báo
                const alert = document.createElement('div');
                alert.className = 'alert alert-warning mt-2';
                alert.textContent = '⏰ Mã đã hết hạn. Vui lòng nhấn "Gửi lại mã"';
                timerElement.parentElement.appendChild(alert);
            }
        }, 1000);

        // Ngăn spam nút gửi lại
        let canResend = true;
        resendBtn.addEventListener('click', function(e) {
            if (!canResend) {
                e.preventDefault();
                return;
            }
            
            canResend = false;
            resendBtn.disabled = true;
            resendBtn.textContent = 'Đang gửi...';
            
            setTimeout(() => {
                canResend = true;
                resendBtn.disabled = false;
                resendBtn.textContent = 'Gửi lại mã';
            }, 5000); // Chặn 5 giây
        });
    </script>
</body>

</html>