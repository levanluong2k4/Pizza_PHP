<?php 
session_start(); 

require __DIR__ . '/../../../includes/db_connect.php';

// Kiểm tra đăng nhập
if(!isset($_SESSION['user_id'])&&$_SESSION['role']=='admin'){
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// echo '<pre>';
// print_r($_SESSION);
// print_r($_POST);

// echo '</pre>';
// Lấy thông tin người dùng
$sql_user = "SELECT * FROM `admin` WHERE id='$user_id'";
$result_user = mysqli_query($ketnoi, $sql_user);
$user = mysqli_fetch_array($result_user);

 




$message = '';
$message_type = '';


















// Lưu vào DB khi nhấn Lưu
$saved = false;
$updateMessage = '';







// Xử lý đổi mật khẩu
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])){
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Lấy mật khẩu hiện tại từ database
    $sql_check = "SELECT password FROM admin WHERE id = '$user_id'";
    $result_check = mysqli_query($ketnoi, $sql_check);
    $user_check = mysqli_fetch_assoc($result_check);
    
    // Kiểm tra mật khẩu cũ bằng password_verify
    if(password_verify($old_password, $user_check['password'])){
        if($new_password == $confirm_password){
            if(strlen($new_password) >= 6){
                // Mã hóa mật khẩu mới bằng password_hash
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update_pass = "UPDATE admin SET password = '$hashed_password' WHERE id = '$user_id'";
                
                if(mysqli_query($ketnoi, $sql_update_pass)){
                    $message = 'Đổi mật khẩu thành công!';
                    $message_type = 'success';
                } else {
                    $message = 'Có lỗi xảy ra. Vui lòng thử lại!';
                    $message_type = 'danger';
                }
            } else {
                $message = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
                $message_type = 'danger';
            }
        } else {
            $message = 'Mật khẩu mới không khớp!';
            $message_type = 'danger';
        }
    } else {
        $message = 'Mật khẩu cũ không đúng!';
        $message_type = 'danger';
    }
}


?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Thông tin tài khoản - Pizza</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <!-- Custom CSS -->
    <!-- <link rel="stylesheet" href="/unitop/backend/lesson/school/project_pizza/css/pizza.css">
    <link rel="stylesheet" href="/unitop/backend/lesson/school/project_pizza/css/basic.css"> -->
    <link rel="stylesheet" href="/unitop/backend/lesson/school/project_pizza/css/info_user.css">


</head>

<body>
  <?php include '../../navbar_admin.php'; ?>

    <section class="profile-section bg-icon">
        <div class="container">
           <?php if($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert" id="autoCloseAlert">
    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

            <script>
                // Tự động đóng sau 3 giây
                setTimeout(function() {
                    var alertElement = document.getElementById('autoCloseAlert');
                    if(alertElement) {
                        // Sử dụng Bootstrap's fade out
                        var bsAlert = new bootstrap.Alert(alertElement);
                        bsAlert.close();
                    }
                }, 3000);
            </script>
            <?php endif; ?>

            <div class=" profile-card ">
               

                <div class=" profile-body">
                     <div>
                    
                    <h3 class="mb-1"><i class="fas fa-user"></i>  <?php echo htmlspecialchars($user['ten']); ?></h3>
                    <p class="mb-0"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    
                    <ul class="nav nav-tabs mb-4 justify-content-center" role="tablist">
                       
                    
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#password" type="button">
                                <i class="fas fa-key"></i> Đổi mật khẩu
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#email" type="button">
                                <i class="fas fa-envelope"></i> Đổi Email
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                       
                     

                   

                        <!-- Tab đổi mật khẩu -->
                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <form method="POST" action="">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">Mật khẩu cũ *</label>
                                            <input type="password" class="form-control" name="old_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mật khẩu mới * (tối thiểu 6 ký tự)</label>
                                            <input type="password" class="form-control" name="new_password"
                                                minlength="6" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Xác nhận mật khẩu mới *</label>
                                            <input type="password" class="form-control" name="confirm_password"
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

                        <div class="tab-pane fade" id="email" role="tabpanel">
                            <form method="POST" id="emailForm">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">

                                         <div class="mb-3">
                                            <label class="form-label">Email cũ *</label>
                                      
                                            <input type="email" class="form-control" style="background-color:#b7b7b7" id="old_email" name="old_email"
                                                value="<?php echo $user["email"] ?>" readonly
                                                placeholder="example@gmail.com" >
                                         
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Email mới *</label>
                                      
                                            <input type="email" class="form-control" id="new_email" name="email"
                                                placeholder="example@gmail.com" required>
                                            <small id="error-new-email" class="text-danger"
                                                style="font-size: 0.8em;"></small>
                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="button" id="change_email_btn" class="btn btn-save">
                                                <i class="fas fa-envelope"></i> Đổi email
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
 




    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>



<script>
 

    document.addEventListener('DOMContentLoaded', function() {
        const editBtns = document.querySelectorAll('.edit-btn');
        const saveBtn = document.getElementById('saveBtn');
        let isEditing = false;

        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const field = this.getAttribute('data-field');
                const display = document.getElementById(field + '_display');
                const input = document.getElementById(field + '_input');

                if (!isEditing) {
                    display.style.display = 'none';
                    input.style.display = 'block';
                    input.focus();
                    saveBtn.style.display = 'block';
                    isEditing = true;

                    this.classList.remove('fa-pen-to-square');
                    this.classList.add('fa-check');
                    this.style.color = '#ffc107';
                } else {
                    display.textContent = input.value || (field === 'hoten' ?
                        'Vui lòng nhập tên người nhận' : 'Vui lòng nhập số điện thoại');

                    display.style.display = 'block';
                    input.style.display = 'none';
                    saveBtn.style.display = 'block';
                    isEditing = false;

                    this.classList.remove('fa-check');
                    this.classList.add('fa-pen-to-square');
                    this.style.color = '#30d952';
                }

            });
        });

        saveBtn.addEventListener('click', function() {
            isEditing = false;
        });
    });
</script>
<script>
    
$(document).ready(function() {


    





    // ✅ Đổi selector thành #new_email
    $('#change_email_btn').on('click', function(e) {
        e.preventDefault();

        const email = $('#new_email').val().trim();
        const btn = $(this);
        
        // Clear error
        $('#error-new-email').text('');
        
        // Validation phía client
        if (!email) {
            $('#error-new-email').text('Vui lòng nhập email!');
            return;
        }
        
        // Kiểm tra format email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            $('#error-new-email').text('Email không đúng định dạng!');
            return;
        }

        // Disable button khi đang gửi
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang gửi...');

        $.ajax({
            url: '/unitop/backend/lesson/school/project_pizza/admin/process/change_email.php',
            method: 'POST',
            data: { email: email },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = '/unitop/backend/lesson/school/project_pizza/admin/process/verify_change_email.php';
                } else {
                    if (response.error_type === 'email_format' || response.error_type === 'email') {
                        $('#error-new-email').text(response.message);
                    } else {
                        alert(response.message);
                    }
                    btn.prop('disabled', false).html('<i class="fas fa-envelope"></i> Đổi email');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Lỗi hệ thống hoặc mạng. Vui lòng thử lại!');
                btn.prop('disabled', false).html('<i class="fas fa-envelope"></i> Đổi email');
            }
        });
    });

    // Validation khi người dùng nhập
    $('#new_email').on('input', function() {
        $('#error-new-email').text('');
    });




  


});
</script>


  




</body>

</html>