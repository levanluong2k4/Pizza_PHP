<?php
// ==========================================
// FILE 1: forget_password.php (Form nhập email)
// ==========================================
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu - Pizza Store</title>
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
        .forgot-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        .forgot-icon {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 20px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="forgot-card">
        <div class="text-center">
            <i class="fas fa-lock forgot-icon"></i>
            <h3 class="mb-3">Quên Mật Khẩu?</h3>
            <p class="text-muted mb-4">Nhập email của bạn để nhận mã xác thực</p>
        </div>

        <div id="message-container"></div>

        <form id="forgotForm">
            <div class="mb-3">
                <label class="form-label">Email *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="example@gmail.com" required>
                </div>
                <small id="error-email" class="text-danger"></small>
            </div>

            <button type="submit" class="btn btn-primary btn-submit w-100">
                <i class="fas fa-paper-plane"></i> Gửi Mã Xác Thực
            </button>

            <div class="text-center mt-3">
                <a href="sign_in.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                </a>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $('#forgotForm').on('submit', function(e) {
            e.preventDefault();
            
            const email = $('#email').val().trim();
            const btn = $(this).find('button[type="submit"]');
            
            $('#error-email').text('');
            $('#message-container').html('');
            
            if (!email) {
                $('#error-email').text('Vui lòng nhập email!');
                return;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                $('#error-email').text('Email không đúng định dạng!');
                return;
            }
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');
            
            $.ajax({
                url: 'handlers/send_reset_code.php',
                method: 'POST',
                data: { email: email },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#message-container').html(`
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle"></i> ${response.message}
                            </div>
                        `);
                        
                        setTimeout(function() {
                            window.location.href = 'handlers/verify_reset_code.php';
                        }, 2000);
                    } else {
                        $('#message-container').html(`
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-triangle"></i> ${response.message}
                            </div>
                        `);
                        btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Gửi Mã Xác Thực');
                    }
                },
                error: function(xhr) {
                    $('#message-container').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> Lỗi hệ thống! Vui lòng thử lại sau.
                        </div>
                    `);
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Gửi Mã Xác Thực');
                }
            });
        });
        
        $('#email').on('input', function() {
            $('#error-email').text('');
        });
    </script>
</body>
</html>