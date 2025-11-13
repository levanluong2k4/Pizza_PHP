<?php 
require 'includes/db_connect.php';
header('Content-Type: application/json');

$new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    

    
    // Kiểm tra mật khẩu cũ bằng password_verify
   
        if($new_password == $confirm_password){
            if(strlen($new_password) >= 6){
                // Mã hóa mật khẩu mới bằng password_hash
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update_pass = "UPDATE khachhang SET MatKhau = '$hashed_password' WHERE MaKH = '$user_id'";
                
                if(mysqli_query($ketnoi, $sql_update_pass)){
                        echo json_encode([
                        'success' => true,
                       
                        'message' => 'Thay đổi mật khẩu thành công!'
                    ]);
                } else {
                     echo json_encode([
                            'success' => false,
                            'error_type' => 'missing_data',
                            'message' => 'Có lỗi xảy ra. Vui lòng thử lại!'
                        ]);
                }
            } else {
                     echo json_encode([
                    'success' => false,
                    'error_type' => 'short_password',
                    'message' => 'mật khẩu phải ít nhất 6 kí tự'
                ]);
            }
        } else {
                    echo json_encode([
                'success' => false,
                'error_type' => 'not match',
                'message' => 'mật khẩu không khớp!'
            ]);
        }
    


?>











<!DOCTYPE html>
<html lang="en">

<head>
    <title>Pizza</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- animate -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="./css/animate.css">
    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />

    <!-- slick -->
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
    <link rel="stylesheet" type="text/css" href="slick/slick-theme.css" />

    <!-- CSS -->
    <link rel="stylesheet" href="css/pizza.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/sign_up.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <header class="bg-icon">
        <?php include 'components/navbar.php'; ?>
        


    </header>
             <!-- Tab đổi mật khẩu -->
                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <form method="POST" action="">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                      
                                        <div class="mb-3">
                                            <label class="form-label">Mật khẩu mới * (tối thiểu 6 ký tự)</label>
                                            <input type="password" id="password" class="form-control" name="new_password"
                                                minlength="6" required>
                                              <small id="error-new-password" class="text-danger"
                                                style="font-size: 0.8em;"></small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Xác nhận mật khẩu mới *</label>
                                            <input type="password" id="password_confirm" class="form-control" name="confirm_password"
                                                minlength="6" required>
                                        </div>
                                        <div class="text-center mt-4">
                                            <button type="submit" name="change_password" class="btn btn-save">
                                                <i class="fas fa-key"></i> Đổi mật khẩu
                                            </button>
                                           
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>




    <?php include 'components/footer.php'; ?>

    <!-- jQuery (phải load trước slick) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <!-- Slick Carousel JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <script src="./js/wow.min.js"></script>
   

 <script>
    $(document).ready(function() {


    





    // ✅ Đổi selector thành #new_password
    $('.btn-save').on('click', function(e) {
        e.preventDefault();

        const password = $('#password').val().trim();
        const password_confirm = $('#password_confirm').val().trim();

        const btn = $(this);
        
        // Clear error
        $('#error-new-password').text('');
        
        // Validation phía client
        if (!password) {
            $('#error-new-password').text('Vui lòng nhập password');
            return;
        }
        
      

        // Disable button khi đang gửi
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang gửi...');

         $.ajax({
            url: 'forget_password.php',
            method: 'POST',
            data: { confirm_password:password_confirm
                ,new_password:password  },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = 'sign_in.php';
                } else {
                    if (response.error_type === 'short_password' || response.error_type === 'not match') {
                        $('#error-new-password').text(response.message);
                    } else {
                        alert(response.message);
                    }
                    btn.prop('disabled', false).html('<i class="fas fa-envelope"></i> Đổi password');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Lỗi hệ thống hoặc mạng. Vui lòng thử lại!');
                btn.prop('disabled', false).html('<i class="fas fa-envelope"></i> Đổi password');
            }
        });
    });

    // Validation khi người dùng nhập
    $('#new_password').on('input', function() {
        $('#error-new-password').text('');
    });


// 3. LOAD ĐỊA CHỈ CŨ (nếu có)

   


});
 </script>

</html>


