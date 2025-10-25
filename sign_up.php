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
    <link rel="stylesheet" href="css/bai6.css">
    <link rel="stylesheet" href="css/basic.css">
     <link rel="stylesheet" href="css/sign_up.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
      <header class="bg-icon">
        <?php include 'navbar/navbar.php'; ?>

    </header>

  <main class="container my-5 form-sign_up">
<!-- From Uiverse.io by micaelgomestavares --> 
<form class="form" action="handlers/process_sign_up.php" method="post">
    <p class="title">Register </p>
    <p class="message">Signup now and get full access to our app. </p>
        <div class="row">
        <label  class="col-6 pe-0">
            <input class="input" type="text" name="name" placeholder="" required="" value="<?php echo isset($_SESSION['old_name'])? (htmlspecialchars($_SESSION['old_name'])):''?>">
            <span>name</span>
        </label>

        <label class="col-6 p">
            <input class="input" type="number" name="sdt" placeholder="" required="" value="<?php echo isset($_SESSION['old_sdt'])? (htmlspecialchars($_SESSION['old_sdt'])):''?>">
            <span>Số điện thoại</span>
        </label>
    </div>  
            
    <label>
        <input class="input" type="email"name="email" placeholder="" required="" value="<?php echo isset($_SESSION['old_email'])? (htmlspecialchars($_SESSION['old_email'])):''?>">
        <span>Email</span>
              <?php 
    if (isset($_SESSION['error']) && $_SESSION['error'] === 'password_already_exists') {
        echo '<span style="color: red; font-size: 0.8em;">Email đã tồn tại !Vui lòng chọn email khác.</span>';
    unset($_SESSION['error']);
    }
    ?>
    </label> 
        
    <label>
        <input class="input" type="password" name="password" placeholder="" required="" value="<?php echo isset($_SESSION['old_password'])? (htmlspecialchars($_SESSION['old_password'])):''?>">
        <span>Password</span>
          <?php 
    if (isset($_SESSION['error']) && $_SESSION['error'] === 'password_mismatch') {
        echo '<span style="color: red; font-size: 0.8em;">Mật khẩu không trùng khớp.</span>';
    unset($_SESSION['error']);
    }
    ?>
    </label>
    <label>
        <input class="input" type="password" name="password_confirm" placeholder="" required="" value="<?php echo isset($_SESSION['old_password_confirm'])? (htmlspecialchars($_SESSION['old_password_confirm'])):''?>" >
        <span>Confirm password</span>
    </label>
    <button class="submit">Submit</button>
    <p class="signin">Already have an acount ? <a href="#">Signin</a> </p>
</form>



    </main>


     <?php include 'footer.php'; ?>

    <!-- jQuery (phải load trước slick) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <!-- Slick Carousel JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <script src="./js/wow.min.js"></script>
      <script src="js/cart.js"></script>
</body>
</html>