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

    <style>
    /* CSS cho modal size selection */
    .size-options .form-check {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .size-options .form-check:hover {
        border-color: #28a745;
        background-color: #f8f9fa;
    }

    .size-options .form-check-input:checked+.form-check-label {
        color: #28a745;
        font-weight: bold;
    }

    .size-options .form-check-input:checked~* {
        border-color: #28a745;
    }

    .selected-info {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #28a745;
    }

    .modal-lg {
        max-width: 800px;
    }

    .cart-count {
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
        position: absolute;
        top: -5px;
        right: -5px;
    }
    </style>
</head>

<body>
    <header class="bg-icon">
        <?php include 'navbar/navbar.php'; ?>

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

        <div class="row">
            <div class="container bg-success py-3 d-flex px-5">
                <div class="col-5 text-start animate__animated animate__pulse animate__infinite">
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

        <div class="row">
            <div class="inner-card py-3" id="product-list">
                <?php foreach ($sanpham_rs as $sp): ?>
                <div class="col-lg-4 col-6 wow animate__bounceInLeft">
                    <div class="inner-items text-center">
                        <div class="card border-0 bg-transparent">
                            <img src="./<?php echo $sp["Anh"] ?>" class="card-img-top mx-auto"
                                alt="<?php echo $sp["TenSP"] ?>">
                            <div class="card-body">
                                <p class="card-text text-success m-0" style="font-weight: 600;">
                                    <?php echo $sp["TenSP"] ?></p>
                                <button type="button" class="inner-btn" data-masp="<?php echo $sp["MaSP"]; ?>">
                                    Mua ngay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 bg-success text-center py-3 animate__animated animate__pulse animate__infinite">
                <h3 class="text-warning mb-0">🍕 Khuyến mãi - Combo đặc biệt 🍕</h3>
            </div>
        </div>
    </header>

    <main></main>
    <footer></footer>

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
    $(document).ready(function() {
        // Xử lý tab switching
        $(".tab-link").on("click", function() {
            var maloai = $(this).data("loai");

            $.post("ajax_sanpham.php", {
                maloai: maloai
            }, function(data) {
                $("#product-list").html(data);
            });

            $(".tab-link").removeClass("active");
            $(this).addClass("active");
        });

        // Xử lý khi nhấn nút "Mua ngay"
        $(document).on('click', '.inner-btn', function() {
            var productCard = $(this).closest('.inner-items');
            var productImg = productCard.find('img').attr('src');
            var productName = productCard.find('.card-text').text();
            var maSP = $(this).data('masp');

            // Gọi AJAX để lấy thông tin size và giá
            $.post("get_product_sizes.php", {
                masp: maSP
            }, function(data) {
                var sizes = JSON.parse(data);
                if (sizes.length > 0) {
                    showSizeModal(productName, productImg, sizes, maSP);
                } else {
                    alert('Sản phẩm này hiện tại chưa có thông tin size!');
                }
            });
        });

        // Hiển thị modal chọn size
        function showSizeModal(productName, productImg, sizes, maSP) {
            var modalHTML = `
                <div class="modal fade" id="sizeModal" tabindex="-1" aria-labelledby="sizeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="sizeModalLabel">Chọn size cho ${productName}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-5">
                                        <img src="${productImg}" class="img-fluid rounded" alt="${productName}">
                                        <h6 class="mt-3 text-success">${productName}</h6>
                                    </div>
                                    <div class="col-md-7">
                                        <h6>Chọn size:</h6>
                                        <div class="size-options">
                                            ${generateSizeOptions(sizes)}
                                        </div>
                                        <div class="mt-3">
                                            <div class="selected-info" style="display:none;">
                                                <h6>Size đã chọn: <span id="selectedSize"></span></h6>
                                                <h5 class="text-danger">Giá: <span id="selectedPrice"></span> VNĐ</h5>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label for="quantity" class="form-label">Số lượng:</label>
                                            <div class="input-group" style="width: 150px;">
                                                <button class="btn btn-outline-secondary" type="button" id="decreaseBtn">-</button>
                                                <input type="number" class="form-control text-center" id="quantity" value="1" min="1">
                                                <button class="btn btn-outline-secondary" type="button" id="increaseBtn">+</button>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <h5>Tổng tiền: <span id="totalPrice" class="text-danger">0 VNĐ</span></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                <button type="button" class="btn btn-success" id="addToCartBtn" data-masp="${maSP}" disabled>Thêm vào giỏ hàng</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Xóa modal cũ nếu có
            $('#sizeModal').remove();

            // Thêm modal vào body
            $('body').append(modalHTML);

            // Hiển thị modal
            $('#sizeModal').modal('show');

            // Xử lý sự kiện trong modal
            setupModalEvents();
        }

        // Tạo HTML cho các tùy chọn size
        function generateSizeOptions(sizes) {
            var html = '';
            sizes.forEach(function(size, index) {
                html += `
                    <div class="form-check mb-2">
                        <input class="form-check-input size-radio" type="radio" name="sizeOption" 
                               id="size${index}" value="${size.MaSize}" data-price="${size.Gia}" data-size-name="${size.TenSize}">
                        <label class="form-check-label" for="size${index}">
                            <strong>${size.TenSize}</strong> - ${Number(size.Gia).toLocaleString()} VNĐ
                        </label>
                    </div>
                `;
            });
            return html;
        }

        // Thiết lập các sự kiện trong modal
        function setupModalEvents() {
            // Khi chọn size
            $(document).on('change', '.size-radio', function() {
                var selectedPrice = $(this).data('price');
                var selectedSizeName = $(this).data('size-name');
                var quantity = parseInt($('#quantity').val());

                $('#selectedSize').text(selectedSizeName);
                $('#selectedPrice').text(Number(selectedPrice).toLocaleString());
                $('#totalPrice').text(Number(selectedPrice * quantity).toLocaleString() + ' VNĐ');

                $('.selected-info').show();
                $('#addToCartBtn').prop('disabled', false);
            });

            // Tăng số lượng
            $(document).on('click', '#increaseBtn', function() {
                var quantity = parseInt($('#quantity').val());
                $('#quantity').val(quantity + 1);
                updateTotalPrice();
            });

            // Giảm số lượng
            $(document).on('click', '#decreaseBtn', function() {
                var quantity = parseInt($('#quantity').val());
                if (quantity > 1) {
                    $('#quantity').val(quantity - 1);
                    updateTotalPrice();
                }
            });

            // Khi thay đổi số lượng bằng tay
            $(document).on('change', '#quantity', function() {
                var quantity = parseInt($(this).val());
                if (quantity < 1) {
                    $(this).val(1);
                }
                updateTotalPrice();
            });

            // Thêm vào giỏ hàng
            $(document).on('click', '#addToCartBtn', function() {
                var selectedSize = $('.size-radio:checked');
                var maSP = $(this).data('masp');

                if (selectedSize.length > 0) {
                    var productData = {
                        masp: maSP,
                        masize: selectedSize.val(),
                        quantity: $('#quantity').val(),
                        price: selectedSize.data('price')
                    };

                    // Gọi AJAX để thêm vào giỏ hàng
                    addToCart(productData);
                }
            });
        }

        // Cập nhật tổng tiền
        function updateTotalPrice() {
            var selectedSize = $('.size-radio:checked');
            if (selectedSize.length > 0) {
                var price = selectedSize.data('price');
                var quantity = parseInt($('#quantity').val());
                var total = price * quantity;
                $('#totalPrice').text(Number(total).toLocaleString() + ' VNĐ');
            }
        }

        // Thêm vào giỏ hàng
        function addToCart(productData) {
            $.post("add_to_cart.php", productData, function(response) {
                try {
                    var result = JSON.parse(response);
                    if (result.success) {
                        $('#sizeModal').modal('hide');
                        alert('Đã thêm sản phẩm vào giỏ hàng!');
                        // Cập nhật số lượng giỏ hàng nếu có
                        updateCartCount();
                    } else {
                        alert('Có lỗi xảy ra: ' + result.message);
                    }
                } catch (e) {
                    alert('Có lỗi xảy ra khi xử lý phản hồi từ server');
                }
            }).fail(function() {
                alert('Không thể kết nối tới server. Vui lòng thử lại!');
            });
        }

        // Cập nhật số lượng giỏ hàng
        function updateCartCount() {
            $.get("get_cart_count.php", function(count) {
                $('.cart-count').text(count);
            });
        }

        // Load cart count khi trang được tải
        updateCartCount();
    });
    </script>

</body>

</html>