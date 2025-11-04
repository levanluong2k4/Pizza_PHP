<?php


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$user_id =$_SESSION['user_id']?? null;

$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

if (isset($_COOKIE['remember']) && !empty($_COOKIE['remember'])) {
    $token = mysqli_real_escape_string($ketnoi, $_COOKIE['remember']);
    $sql = "SELECT * FROM khachhang WHERE token='$token'";
    $result = mysqli_query($ketnoi, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            $_SESSION['user_id'] = $row['MaKH'];
            $_SESSION['name'] = $row['HoTen'];
        }
    } else {
        
        setcookie('remember', '', time() - 3600, '/');
    }
}

$count = 0;

if (isset($_SESSION['user_id']) ) {

    // Nếu vẫn còn session cart thì merge vào database trước khi xóa
if (!empty($_SESSION['cart'])) {
    // Kiểm tra giỏ hàng tồn tại chưa
    $sql_cart = "SELECT * FROM giohang WHERE MaKH='$user_id'";
    $result = mysqli_query($ketnoi, $sql_cart);
    if (mysqli_num_rows($result) == 0) {
        mysqli_query($ketnoi, "
        INSERT INTO `giohang`( `MaKH`) VALUES ('$user_id')
        ");
    }

    // Lấy CartID
    $sql_cart_id = "SELECT CartID FROM giohang WHERE MaKH='$user_id'";
   
    $result_cart_id = mysqli_query($ketnoi, $sql_cart_id);
  
    $cart_row = mysqli_fetch_assoc($result_cart_id);
    $cart_id = $cart_row['CartID'];


    // Lưu các item từ session vào DB
    foreach ($_SESSION['cart'] as $key => $item) {
       $maSP_s = $item['masp'];

        $maSize_s = $item['size_id'];
        $soLuong_s = $item['quantity'];

        $sql_check = "SELECT * FROM chitietgiohang 
                      WHERE CartID='$cart_id' AND MaSP='$maSP_s' AND MaSize='$maSize_s'";
        $check_result = mysqli_query($ketnoi, $sql_check);

        if (mysqli_num_rows($check_result) == 0) {
            $sql_insert_detail = "INSERT INTO chitietgiohang(CartID, MaSP, MaSize, Quantity)
                                  VALUES('$cart_id', '$maSP_s', '$maSize_s', '$soLuong_s')";
            mysqli_query($ketnoi, $sql_insert_detail);
        } else {
            $sql_update_detail = "UPDATE chitietgiohang 
                                  SET Quantity = Quantity + '$soLuong_s' 
                                  WHERE CartID='$cart_id' AND MaSP='$maSP_s' AND MaSize='$maSize_s'";
            mysqli_query($ketnoi, $sql_update_detail);
        }
    }

   
    unset($_SESSION['cart']);
}



   
    $sql = "SELECT SUM(Quantity) AS cnt
            FROM chitietgiohang
            WHERE CartID = (SELECT CartID FROM giohang WHERE MaKH = '$user_id')";
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


$sqlloaisp="SELECT * FROM loaisanpham";
$loaisp=mysqli_query($ketnoi,$sqlloaisp);






?>
<style>
.list-group-item-action {
    width: 65%;
}
</style>

<nav class="inner-navbar   navbar navbar-expand-lg ">
    <div class="container-fluid justify-content-lg-around">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="d-flex flex-column align-align-items-end">
            <a class=" inner-logo me-lg-3" href="trangchu.php"><img src="./img/logo.png" alt="logo"></a>
                <?php if (isset($_SESSION['user_id'])): ?>
               <p class="m-0">
        <span class=" fw-bolder me-2" style="color:#1F6C11">Xin chào, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
            </p>
        <?php endif; ?>
        </div>

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
            <form method="get" action="research.php" class="d-flex    inner-search col-md-12" role="search ">
                <input name="search" class="form-control me-2 border-0" type="search" placeholder="Search"
                    aria-label="Search" id="search-box"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                    autocomplete="off">
                <div id="search-result" class="list-group position-absolute col-sm-12 col-lg-6 top-sm-9 top-lg-8"></div>
                <button class="btn btn-outline-light border-start rounded-0" id="search-btn" type="submit" disabled>
                    <i class="fa-solid fa-magnifying-glass text-dark-emphasis fw-bold"></i>
                </button>
            </form>
        </div>

        <div class="collapse navbar-collapse text-end " id="navbarSupportedContent">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0 bg-global listmenu ms-lg-auto  ">

                <li class="nav-item dropdown col-md-12 col-lg-3  text-md-center ">
                    <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Thực đơn
                    </a>
                    <ul class="dropdown-menu bg-global p-0 text-md-center text-lg-start scrollable-menu">
                        <?php foreach($loaisp as $value): ?>
                        <li class="dropdown-item">
                            <a href="research.php?category_id=<?php echo $value['MaLoai'] ?>">
                                <?php echo $value['TenLoai'] ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
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
          

                <ul class="navbar-nav me-auto mb-2 mb-lg-0 bg-global listmenu ms-lg-auto">

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Nếu đã đăng nhập -->

                    <li class="nav-item dropdown col-12 col-md-12 text-center text-md-center">
                        <a class="nav-link dropdown-toggle text-warning p-0" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="./img/user.png" alt="" height="30px">
                        </a>
                        <ul class="dropdown-menu bg-global p-0 text-md-center text-lg-center">
                            <li class="dropdown-item"><a href="#">Thông tin cá nhân</a></li>

                            <li class="dropdown-item"><a href="handlers/process_sign_out.php">Đăng xuất</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
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
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li class="dropdown-item"><a href="#">Quên mật khẩu</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                </ul>

         

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