<?php 

require "includes/query_products.php";

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
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
<link rel="stylesheet" type="text/css" href="slick/slick-theme.css" />

    
    <!-- CSS -->
    <link rel="stylesheet" href="css/bai6.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body>
    <header class="bg-icon">
        <?php include 'components/navbar.php'; ?>

        <div class="row">
            <div class="col-12 text-center">
                <h1 class="text-danger"> The Pizza Company - Pizza phong vị
                    ý </h1>
                <p class="text-success">The PIZZA Company thuộc sở hữu của
                    tập đoàn
                    Minor Food Group ,tự hào cung cấp cho khách hàng gần 20
                    <br> loại
                    bánh pizza thơm ngon với nhân bánh dày đặc trưng nổi bật
                    và phô mai
                    hảo hạn...
                </p>
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

        <div class="row">
            <div class="container bg-success py-3 d-flex px-5">
                <div class="col-5 text-start animate__animated animate__pulse animate__infinite">
                    <h3 class="text-warning mb-0">🍕 Khuyến mãi - Combo đặc
                        biệt 🍕</h3>
                </div>
                <div class="col-7 text-end inner-nav-tab">
                    <ul class="nav justify-content-end">

                        <?php foreach($loai_rs as $value): ?>

                        <?php if ($value["TenLoai"] == "Đồ Uống"
                            || $value["TenLoai"] == "Tráng Miệng"
                            || $value["TenLoai"] == "Salad" || $value["TenLoai"]
                            == "Mỳ Ý - Pasta" || $value["TenLoai"] == "Khai Vị")
                            {
                            continue;
                            } else { ?>
                        <li class="nav-item">
                            <a class="nav-link tab-link <?php if($value["MaLoai"]==$maloai)
                                    echo "active" ; ?> " href="?maloai=<?php echo $value["MaLoai"];
                                    ?>">
                                <?php echo $value["TenLoai"]; ?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="inner-card py-3" id="product-list">
                <?php foreach ($sanpham_rs as $sp): ?>
                    <?php include "components/product_card.php"; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- khai vị  -->

        <div class="row">
            <div class="container bg-success py-3 d-flex px-5">
                <div class=" text-start animate__animated animate__pulse animate__infinite">

                    <h3 class="text-warning mb-0"> 🍔 Món Khai vị 🍔</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="inner-card py-3" id="product-drink">
                <?php foreach ($view_khaivi as $sp): ?>
              <?php include "components/product_card.php"; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- thức uống -->

        <div class="row">
            <div class="col-12 bg-success text-center py-3 animate__animated animate__pulse animate__infinite">
                <h3 class="text-warning mb-0">⋆｡°✩🍸 Thức uống🍸⋆｡°✩</h3>
            </div>
        </div>
        <div class="row">
            <div class="inner-card py-3" id="product-drink">
                <?php foreach ($view_thucuong as $sp): ?>
               <?php include "components/product_card.php"; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Modal chọn size -->
        <?php require "includes/modal_size.php" ?>

    </header>

    <?php require "includes/toast_cart.php"?>

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
    new WOW().init();
    </script>

    <script>
    $('.inner-card').slick({
        infinite: true,
        dots: true,
        customPaging: function(slider, i) {
            return '<button>' + (i + 1) + '</button>';
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
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('id')) {

            const modal = new bootstrap.Modal(document.getElementById('sizeModal'));
            modal.show();
        }
    });
    document.addEventListener("DOMContentLoaded", function() {
        const radios = document.querySelectorAll('.size-radio');
        const quantityInput = document.getElementById('quantity');
        const totalPriceSpan = document.getElementById('totalPrice');
        const selectedInfo = document.querySelector('.selected-info');
        const selectedSizeSpan = document.getElementById('selectedSize');
        const selectedPriceSpan = document.getElementById('selectedPrice');
        const decreaseBtn = document.getElementById('decreaseBtn');
        const increaseBtn = document.getElementById('increaseBtn');

        function updateTotal() {
            const selected = document.querySelector('.size-radio:checked');
            const quantity = parseInt(quantityInput.value) || 1;

            // Nếu chưa chọn size, reset lại hiển thị
            if (!selected) {
                totalPriceSpan.textContent = "0 VNĐ";
                selectedInfo.style.display = 'none';
                addToCartBtn.disabled = true;
                return;
            }

            const name = selected.dataset.name;
            const price = parseFloat(selected.dataset.price);

            // Nếu giá không hợp lệ => ngăn lỗi NaN
            if (isNaN(price)) {
                totalPriceSpan.textContent = "0 VNĐ";
                return;
            }

            const total = price * quantity;

            selectedSizeSpan.textContent = name;
            selectedPriceSpan.textContent = price.toLocaleString('vi-VN');
            totalPriceSpan.textContent = total.toLocaleString('vi-VN') + " VNĐ";
            selectedInfo.style.display = 'block';
            addToCartBtn.disabled = false;
            const sizeId = selected.value;
            const productId = "<?php echo $sp_info['MaSP']; ?>";
            addToCartBtn.href = `./cart/add_to_cart.php?id=${productId}&masize=${sizeId}&soluong=${quantity}`;
        }

        radios.forEach(radio => radio.addEventListener('change', updateTotal));
        quantityInput.addEventListener('input', updateTotal);
        decreaseBtn.addEventListener('click', () => {
            let current = parseInt(quantityInput.value);
            if (current > 1) {
                quantityInput.value = current - 1;
                updateTotal();
            }
        });
        increaseBtn.addEventListener('click', () => {
            quantityInput.value = parseInt(quantityInput.value) + 1;
            updateTotal();
        });
        fetch(`./cart/add_to_cart.php?id=${maSP}&masize=${maSize}&soluong=${quantity}`)
            .then(response => response.text())
            .then(total => {
                document.querySelector(".cart-count").textContent = total;
                alert("Đã thêm sản phẩm vào giỏ hàng!");
            });
    });
    </script>
    <script src="js/search.js"></script>

</body>
</html>