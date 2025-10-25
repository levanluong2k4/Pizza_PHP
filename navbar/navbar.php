<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ( isset($_COOKIE['remember'])) {
    $ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
    mysqli_set_charset($ketnoi, "utf8");

    $token = $_COOKIE['remember'];
    $sql = "SELECT * FROM khachhang WHERE token='$token'";
    $result = mysqli_query($ketnoi, $sql);

   
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['MaKH'];
        $_SESSION['name'] = $row['HoTen'];
   
}
$count = 0;

if (isset($_SESSION['user_id']) ) {
    $userId = mysqli_real_escape_string($ketnoi, $_SESSION['user_id']);
    $sql = "SELECT SUM(Quantity) AS cnt
            FROM chitietgiohang
            WHERE CartID = (SELECT CartID FROM giohang WHERE MaKH = '$userId')";
    $res = mysqli_query($ketnoi, $sql);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        $count = (int)($row['cnt'] ?? 0);
    }
} else {
    // fallback: tính từ session nếu có
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += isset($item['quantity']) ? (int)$item['quantity'] : 0;
        }
    }
}






?>

<nav class="inner-navbar   navbar navbar-expand-lg ">
    <div class="container-fluid justify-content-lg-around">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class=" inner-logo me-lg-3" href="#"><img src="./img/logo.png" alt></a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent1"
            aria-controls="navbarSupportedContent1" aria-expanded="false" aria-label="Toggle navigation">
            <img src="./img/user.png" alt height="30px">
        </button>
        <button class="button_cart navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent2" aria-controls="navbarSupportedContent2" aria-expanded="false"
            aria-label="Toggle navigation">
            <svg viewBox="0 0 16 16" class="bi bi-cart-check" height="24" width="24" xmlns="http://www.w3.org/2000/svg"
                fill="#fff">
                <path
                    d="M11.354 6.354a.5.5 0 0 0-.708-.708L8 8.293 6.854 7.146a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z">
                </path>
                <path
                    d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z">
                </path>
            </svg>

            <div class="count-product bg-warning">
                <span class="cart-count">0</span>
            </div>
        </button>
        <!-- From Uiverse.io by JaydipPrajapati1910 -->
        <!-- add to cart -->

        <div class="bg-global p-1  col-md-12 col-lg-3  rounded-3">
            <form class="d-flex    inner-search col-md-12" role="search ">
                <input class="form-control me-2 border-0" type="search" placeholder="Search" aria-label="Search"
                    id="search-box" autocomplete="off">
                <div id="search-result" class="list-group position-absolute col-sm-12 col-lg-6 top-sm-9 top-lg-8 ">
                </div>
                <button class="btn btn-outline-light border-start rounded-0   " type="submit"><i
                        class="fa-solid fa-magnifying-glass text-dark-emphasis fw-bold "></i></button>
            </form>
        </div>

        <div class="collapse navbar-collapse text-end " id="navbarSupportedContent">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0 bg-global listmenu ms-lg-auto  ">

                <li class="nav-item dropdown col-md-12 col-lg-3  text-md-center ">
                    <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Menu
                    </a>
                    <ul class="dropdown-menu bg-global p-0 text-md-center text-lg-start">
                        <li class="dropdown-item"><a href="#">Action</a></li>
                        <li class="dropdown-item"><a href> Another action</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="dropdown-item"><a href="#">Something else
                                here</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link " aria-current="page" href="#">Đặt hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Liên lạc</a>
                </li>
            </ul>

        </div>

        <div class="collapse navbar-collapse   " id="navbarSupportedContent1">
            <div>
<ul class="navbar-nav me-auto mb-2 mb-lg-0 bg-global listmenu ms-lg-auto">
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Nếu đã đăng nhập -->
        <li class="nav-item d-flex align-items-center">
            <span class="text-white me-2">Xin chào, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
        </li>
        <li class="nav-item dropdown col-12 col-md-12 text-center text-md-center">
            <a class="nav-link dropdown-toggle text-warning p-0" href="#" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <img src="./img/user.png" alt="" height="30px">
            </a>
            <ul class="dropdown-menu bg-global p-0 text-md-center text-lg-center">
                <li class="dropdown-item"><a href="#">Thông tin cá nhân</a></li>
                   
                <li class="dropdown-item"><a href="handlers/process_sign_out.php">Đăng xuất</a></li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-item"><a href="#">Quên mật khẩu</a></li>
            </ul>
        </li>

    <?php else: ?>
        <!-- Nếu chưa đăng nhập -->
        <li class="nav-item dropdown col-12 col-md-12 text-center text-md-center">
            <a class="nav-link dropdown-toggle text-warning p-0" href="#" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <img src="./img/user.png" alt="" height="30px">
            </a>
            <ul class="dropdown-menu bg-global p-0 text-md-center text-lg-center">
                <li class="dropdown-item"><a href="./sign_in.php">Đăng nhập</a></li>
                <li class="dropdown-item"><a href="./sign_up.php">Đăng ký</a></li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-item"><a href="#">Quên mật khẩu</a></li>
            </ul>
        </li>
    <?php endif; ?>
    
</ul>

            </div>

        </div>
        <a href="cart.php" class="button_cart d-md-none d-sm-none d-lg-block">
            <svg viewBox="0 0 16 16" class="bi bi-cart-check" height="24" width="24" xmlns="http://www.w3.org/2000/svg"
                fill="#fff">
                <path
                    d="M11.354 6.354a.5.5 0 0 0-.708-.708L8 8.293 6.854 7.146a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z">
                </path>
                <path
                    d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z">
                </path>
            </svg>
            <div class="count-product ">
                <span class="cart-count"><?php echo $count; ?></span>
            </div>
        </a>

    </div>
</nav>