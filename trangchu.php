<?php session_start();

$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");


// lấy thức uống
$thucuong="SELECT sanpham.MaSP, 
       sanpham.TenSP, 
       sanpham.MoTa, 
       sanpham.Anh, 
       sanpham.MaLoai, 
       sanpham.NgayThem
FROM sanpham, loaisanpham
WHERE sanpham.MaLoai = loaisanpham.MaLoai
  AND loaisanpham.TenLoai = 'Thức uống';
";
$view_thucuong=mysqli_query($ketnoi,$thucuong);


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







// Xử lý thêm sản phẩm vào giỏ hàng



if (isset($_GET['id'])) {
$id=$_GET['id'] ?? '';
$sqlsize = "SELECT ss.MaSize, s.TenSize, ss.Gia , ss.Anh 
            FROM sanpham_size ss 
            INNER JOIN size s ON ss.MaSize = s.MaSize 
            WHERE ss.MaSP = $id 
            ORDER BY s.MaSize";

$sanphamsize = mysqli_query($ketnoi, $sqlsize);
$sizes = array();
if ($sanphamsize && mysqli_num_rows($sanphamsize) > 0
) {
    while ($row = mysqli_fetch_assoc($sanphamsize)) {
        $sizes[] = $row;
    }

}

  

    // Lấy thông tin sản phẩm
    $sql_sp_info = "SELECT * FROM sanpham WHERE MaSP = $id";
    $sp_info_result = mysqli_query($ketnoi, $sql_sp_info);
    $sp_info = mysqli_fetch_assoc($sp_info_result);

}

   



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
    <link rel="stylesheet" href="css/bai6.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


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
                                <a href="?id=<?php echo $sp["MaSP"]; ?>"  class="inner-btn" data-masp="<?php echo $sp["MaSP"]; ?>">
                                    Mua ngay
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 bg-success text-center py-3 animate__animated animate__pulse animate__infinite">
                <h3 class="text-warning mb-0">⋆｡°✩🍸 Thức uống🍸⋆｡°✩</h3>
            </div>
        </div>
        <div class="row">
            <div class="inner-card py-3" id="product-drink">
                <?php foreach ($view_thucuong as $sp): ?>
                <div class="col-lg-4 col-6 wow animate__bounceInLeft">
                    <div class="inner-items text-center">
                        <div class="card border-0 bg-transparent">
                            <img src="./<?php echo $sp["Anh"] ?>" class="card-img-top mx-auto"
                                alt="<?php echo $sp["TenSP"] ?>">
                            <div class="card-body">
                                <p class="card-text text-success m-0" style="font-weight: 600;">
                                    <?php echo $sp["TenSP"] ?></p>
                                <a href="?id=<?php echo $sp["MaSP"]; ?>" class="inner-btn" ?>
                                    Mua ngay
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>





        <?php if (isset($sp_info)): ?>
        <!-- Modal chọn size -->
        <div class="modal fade" id="sizeModal" tabindex="-1" aria-labelledby="sizeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sizeModalLabel">
                            Chọn size cho <?php echo htmlspecialchars($sp_info['TenSP']); ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-5">
                                <img src="./<?php echo htmlspecialchars($sp_info['Anh']); ?>" class="img-fluid rounded"
                                    alt="<?php echo htmlspecialchars($sp_info['TenSP']); ?>">
                                <h6 class="mt-3 text-success text-center">
                                    <?php echo htmlspecialchars($sp_info['TenSP']); ?>
                                </h6>
                            </div>

                            <div class="col-md-7">
                                <h6>Chọn size:</h6>
                                <div class="size-options">
                                    <?php foreach ($sizes as $size): ?>
                                    <div class="form-check">
                                        <input class="form-check-input size-radio" type="radio" name="size"
                                            id="size-<?php echo $size['MaSize']; ?>"
                                            value="<?php echo $size['MaSize']; ?>"
                                            data-name="<?php echo htmlspecialchars($size['TenSize']); ?>"
                                            data-price="<?php echo floatval($size['Gia']); ?>">
                                        <label class="form-check-label" for="size-<?php echo $size['MaSize']; ?>">
                                            <img src="./<?php echo $size['Anh']; ?>" alt="" height="30px" class="me-2">
                                            <?php echo $size['TenSize']; ?> - <?php echo number_format($size['Gia']); ?>
                                            VNĐ
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
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
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="decreaseBtn">-</button>
                                        <input type="number" class="form-control text-center" id="quantity" value="1"
                                            min="1">
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="increaseBtn">+</button>
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
                        <a href="#" class="btn btn-success" id="addToCartBtn"
                             disabled >Thêm vào giỏ hàng</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </header>
  
    <?php if(isset($_SESSION['cart_message']) && $_SESSION['cart_message'] == 'success'): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="cartToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <i class="fa fa-shopping-cart me-2"></i>
            <strong class="me-auto">Giỏ hàng</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <i class="fa fa-check-circle text-success"></i> Đã thêm sản phẩm vào giỏ hàng thành công!
        </div>
    </div>
</div>

<script>
    setTimeout(function() {
        var toastEl = document.getElementById('cartToast');
        if(toastEl) {
            var toast = new bootstrap.Toast(toastEl);
            toast.hide();
        }
    }, 3000); // Tự động ẩn sau 3 giây
</script>
<?php 
    unset($_SESSION['cart_message']); 
endif; 
?>


    <?php include 'footer.php'; ?>

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