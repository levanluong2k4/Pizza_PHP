<?php session_start(); ?>

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
<style>
body {
    padding: 0;
}

footer {
    margin-top: 0;
}
</style>

<body>
    <header class="bg-icon">
        <?php include 'components/navbar.php'; ?>

        <main class="container  form-sign_up">
            <!-- From Uiverse.io by micaelgomestavares -->
            <form class="form" action="handlers/process_sign_up.php" method="post">
                <div class="flex align-center mb-3">
                    <a class="  me-lg-3" href="trangchu.php"><img src="./img/logo1.png" alt="logo pizza"
                            style="width: 100px; height:auto;"></a>
                    <h1 class="title">ĐĂNG KÝ </h1>
                </div>

                <p class="message">Đăng ký ngay để có quyền truy cập đầy đủ vào ứng dụng của chúng tôi.</p>
                <div class="row">
                    <label class="col-6 pe-0">
                        <input class="input" type="text" id="name" name="name" required=""
                            value="<?php echo isset($_SESSION['old_name'])? (htmlspecialchars($_SESSION['old_name'])):'' ?>">
                        <span>Tên đăng nhập</span>
                    </label>
            <label class="col-6 p">
                <input class="input" type="text" name="sdt" placeholder="" required
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" 
                    pattern="[0-9]{10}"
                    id="sdt"
                    value="<?php echo isset($_SESSION['old_sdt'])? (htmlspecialchars($_SESSION['old_sdt'])):'' ?>">
                <span>Số điện thoại</span>
                <small id="error-phone" class="text-danger" style="font-size: 0.8em;"></small>
            </label>
                </div>

                <label>
                    <input class="input" type="email" id="email" name="email" required
                        value="<?php echo isset($_SESSION['old_email'])? (htmlspecialchars($_SESSION['old_email'])):''?>">
                    <span>Email</span>
                    <small id="error-email" class="text-danger" style="font-size: 0.8em;"></small>
                </label>

                <label>
                    <input class="input" type="password" id="password" name="password" required
                        value="<?php  if(isset($_SESSION['old_password'])) echo $_SESSION['old_password']  ?>">
                    <span>Mật khẩu</span>
                    <small id="error-password" class="text-danger" style="font-size: 0.8em;"></small>
                </label>

                <label>
                    <input class="input" type="password" id="password_confirm" name="password_confirm" required
                        value="<?php echo isset($_SESSION['old_password_confirm'])? (htmlspecialchars($_SESSION['old_password_confirm'])):''?>">
                    <span>Xác nhận mật khẩu</span>
                    <small id="error-password-confirm" class="text-danger" style="font-size: 0.8em;"></small>
                </label>

                <button class="submit" name="sign_up">Đăng ký</button>
                <p class="signin">Bạn đã có tài khoản ? <a href="sign_in.php">Đăng nhập</a> </p>
            </form>



        </main>

    </header>




    <?php include './components/footer.php'; ?>

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
        $(".form").on("submit", function(e) {
            e.preventDefault();

            // Xóa lỗi cũ
            $("#error-email, #error-password, #error-password-confirm").text("");

            let name = $("#name").val();
            let sdt = $("#sdt").val();
            let email = $("#email").val();
            let password = $("#password").val();
            let password_confirm = $("#password_confirm").val();

            $.ajax({
                url: "handlers/process_sign_up.php",
                type: "POST",
                dataType: "json",
                data: {
                    name: name,
                    sdt: sdt,
                    email: email,
                    password: password,
                    password_confirm: password_confirm,
                },
                success: function(response) {
                    // Nếu có lỗi nhập
                    if (!response.success) {
                        switch (response.error_type) {
                            case "email":
                                $("#error-email").text(response.message);
                                break;
                            case "email_domain":
                              $("#error-email").text(response.message);
                              break;
                          case "email_send":
                              $("#error-email").text(response.message);
                              break;
                            case "email_format":
                                $("#error-email").text(response.message);
                                break;
                            case "email_not_exist":
                                    $("#error-email").text(response.message);
                                    break;
                            case "phone":
                                $("#error-phone").text(response.message);
                                break;
                            case "password_length":
                                $("#error-password").text(response.message);
                                break;
                            case "password_mismatch":
                                $("#error-password-confirm").text(response.message);
                                break;
                            default:
                                console.error("Không xác định được loại lỗi:", response);
                        }
                    } else {
                        // Nếu thành công → chuyển trang
                        window.location.href = "handlers/verify_email.php";
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                },
            });
        });


        document.getElementById('sdt').addEventListener('input', function(e) {
    // Chỉ cho phép số và giới hạn 10 ký tự
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    
    // Hiển thị lỗi nếu chưa đủ 10 số
    const errorPhone = document.getElementById('error-phone');
    if (this.value.length > 0 && this.value.length < 10) {
        errorPhone.textContent = 'Số điện thoại phải có đúng 10 chữ số';
    } else {
        errorPhone.textContent = '';
    }
});
    });



    </script>

</body>

</html>