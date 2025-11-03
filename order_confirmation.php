<?php 

$order_id=$_GET['order_id'] ?? null;

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
    <link rel="stylesheet" href="css/pizza.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/order_success.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


</head>

<body>
    <header class="bg-icon">
        <?php include 'components/navbar.php'; ?>


    <div class="row">
         
  <div class="order-success" role="status" aria-live="polite"  >
    <div class="success-badge" style="padding: ; " aria-hidden="true">

      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle cx="12" cy="12" r="11" stroke="rgba(34,197,94,0.25)" stroke-width="2"/>
        <path d="M7.5 12.5l2.5 2.5 6-7" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>

    <h1>Đặt hàng thành công ✅</h1>
    <p class="lead">Cảm ơn bạn! Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.</p>

    <?php if ($order_id): ?>
      <div class="order-info">
        <div class="chip">Mã đơn hàng: <strong>#<?=htmlspecialchars($order_id)?></strong></div>
        <div class="chip">Trạng thái: Đang xử lý</div>
      </div>
    <?php else: ?>
      <div class="order-info">
        <div class="chip">Mã đơn hàng: <strong>Không xác định</strong></div>
      </div>
    <?php endif; ?>

    <div class="actions">
      <a class="btn btn-primary" href="trangchu.php">Tiếp tục mua sắm</a>
      <a class="btn btn-ghost" href="order_history.php">Xem lịch sử đơn hàng</a>
    </div>

    <p class="small-note">Bạn sẽ nhận được email / SMS xác nhận trong vài phút. Nếu cần hỗ trợ, vui lòng liên hệ hotline hoặc trả lời email này.</p>
  </div>
        </div>

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