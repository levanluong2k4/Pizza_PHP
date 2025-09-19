<?php
$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

// Lấy sản phẩm
if (isset($_POST['maloai'])) {
    $maloai = intval($_POST['maloai']);
    $sql_sp = "SELECT * FROM sanpham WHERE MaLoai = $maloai";
} else {
    $sql_sp = "SELECT * FROM sanpham";
}
$sanpham_rs = mysqli_query($ketnoi, $sql_sp);

// Lấy loại sản phẩm
$sql_loai = "SELECT * FROM loaisanpham";
$loai_rs = mysqli_query($ketnoi, $sql_loai);





?>




<!doctype html>
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
    <link rel="stylesheet" href="bai6.css">
    <link rel="stylesheet" href="basic.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <header class="bg-icon">
        <?php 

include 'navbar/navbar.php';

?>
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="text-danger"> The Pizza Company - Pizza phong vị ý </h1>
                <p class="text-success">The PIZZA Company thuộc sở hữu của tập đoàn
                    Minor Food Group ,tự hào cung cấp cho khách hàng gần 20 <br> loại
                    bánh pizza thơm ngon với nhân bánh dày đặc trưng nổi bật và phô mai
                    hảo hạn...</p>
            </div>
        </div>
        <div class="row my-3">
            <div class="container">
                <div class="col-12 container">
                    <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="./img/Pic_Slide_01.png" class="d-block w-100" alt="...">
                            </div>
                            <div class="carousel-item">
                                <img src="./img/Pic_Slide_02.png" class="d-block w-100" alt="...">
                            </div>
                            <div class="carousel-item">
                                <img src="./img/Pic_Slide_03.png" class="d-block w-100" alt="...">
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row  ">
            <div class=" container  bg-success py-3 d-flex px-5">
                <div class="col-5  text-start  animate__animated animate__pulse animate__infinite ">
                    <h3 class="text-warning mb-0">🍕 Khuyến mãi - Combo đặc biệt 🍕</h3>
                </div>
                <div class="col-7 text-end inner-nav-tab">
                    <ul class="nav justify-content-end">
                        <?php foreach($loai_rs as $value): ?>
                        <?php if ($value["TenLoai"] == "Pizza") { ?>
                        <li class="nav-item">
                            <span class="nav-link active tab-link" data-loai="<?php echo $value["MaLoai"]; ?>">
                                <?php echo $value["TenLoai"]; ?>
                            </span>
                        </li>
                        <?php } elseif ($value["TenLoai"] == "Thức uống" 
                    || $value["TenLoai"] == "GÀ NGON VĨBE" 
                    || $value["TenLoai"] == "Khuyến mãi, Combo") {
            continue;
        } else { ?>
                        <li class="nav-item">
                            <span class="nav-link tab-link" data-loai="<?php echo $value["MaLoai"]; ?>">
                                <?php echo $value["TenLoai"]; ?>
                            </span>
                        </li>
                        <?php } ?>
                        <?php endforeach; ?>
                    </ul>

                </div>
            </div>
        </div>
        <div class=" row ">
            <div class=" inner-card py-3" id="product-list">

                <?php foreach ($sanpham_rs as $sp): ?>
                <div class="col-lg-4 col-6 wow animate__bounceInLeft">
                    <div class="inner-items text-center">
                        <div class="card border-0 bg-transparent">
                            <img src="./<?php echo $sp["Anh"] ?>" class="card-img-top mx-auto"
                                alt="<?php echo $sp["TenSP"] ?>">
                            <div class="card-body">
                                <p class="card-text text-success m-0" style="font-weight: 600;">
                                    <?php echo $sp["TenSP"] ?></p>
                                <p>Giá chỉ từ <span class="text-danger" style="font-size:20px; font-weight:600;">129000
                                        đ</span></p>
                                <button type="submit" class="inner-btn">Mua ngay</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>
        <div class="row ">
            <div class="col-12 bg-success text-center py-3 animate__animated animate__pulse animate__infinite">
                <h3 class="text-warning mb-0">🍕 Khuyến mãi - Combo đặc biệt 🍕</h3>
            </div>
        </div>
    </header>

    <main>

    </main>
    <footer>
        <!-- place footer here -->
    </footer>
    <!-- jQuery (phải load trước slick) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <!-- Slick Carousel JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <script src="./js/wow.min.js"> </script>


    <script>
    new WOW().init();
    </script>
    <script>
    $('.inner-card').slick({
        infinite: true,
        dots: true,
        customPaging: function(slider, i) {
            return '<button>' + (i + 1) + '</button>'; // hiển thị số 1,2,3...
        },
        slidesToShow: 3,
        slidesToScroll: 3,
        prevArrow: '<button class="slick-prev "><i class="fa fa-chevron-left bg-global"></i></button>',
        nextArrow: '<button class="slick-next me-5"><i class="fa fa-chevron-right bg-global"></i></button>',
        responsive: [{
                breakpoint: 992,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });
    </script>
    <script>
    $(document).ready(function() {
        $(".tab-link").on("click", function() {
            var maloai = $(this).data("loai");

            $.post("ajax_sanpham.php", {
                maloai: maloai
            }, function(data) {
                $("#product-list").html(data);
            });

            // đổi active menu
            $(".tab-link").removeClass("active");
            $(this).addClass("active");
        });
    });
    </script>



</body>

</html>