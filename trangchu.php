<?php 

require "includes/query_products.php";

?>

<!doctype html>
<html lang="en">

<head>
    <title>Pizza</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    
    <!-- Animate CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Slick Carousel CSS - CHỈ GIỮ LẠI 2 DÒNG NÀY -->
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/pizza.css">
    <link rel="stylesheet" href="css/basic.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>

<body>
    <header class="bg-icon pt-4">
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
            <div class="container bg-navbar py-3 d-flex px-5">
                <div class=" d-flex align-items-center  col-5 text-start animate__animated animate__pulse animate__infinite">
                     <img src="./img/pizza.png" alt=""style="width: 100px; height: auto; margin-right: 10px;"> 
                    <h3 class="text-warning mb-0 inner-title inner-title">
                      PIZZA </h3>

                        <img src="./img/pizza.png" alt="" style="width: 100px; height: auto; margin-left: 10px;"> 
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
                            <button  class="nav-link tab-link btn-category <?php if($value["MaLoai"]==$maloai)
                                    echo "active" ; ?> " data-id="<?php echo $value["MaLoai"];
                                    ?>">
                                <?php echo $value["TenLoai"]; ?>
                            </button>
                        </li>
                        <?php } ?>
                        <?php endforeach; ?>
                    </ul>
             

                </div>
            </div>
        </div>

        <div class="row">
            <div class="inner-card py-3 slider-pizza" id="product-list">
                <?php foreach ($sanpham_rs as $sp): ?>
                    <?php include "components/product_card.php"; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- khai vị  -->

        <div class="row">
            <div class="container position-relative inner-img bg-navbar py-3 d-flex px-5">
                <div class=" text-start animate__animated animate__pulse animate__infinite">

                    <h3 class="text-warning mb-0 inner-title"> 🍔 Món Khai vị 🍔</h3>
                   
                </div>
                 <div class="position-absolute top-0 end-0">
                        <img src="./img/pizza1.png" alt="" >
                </div>
            </div>
        </div>
        <div class="row">
            <div class="inner-card py-3 slider-khaivi" id="product-drink">
                <?php foreach ($view_khaivi as $sp): ?>
              <?php include "components/product_card.php"; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- thức uống -->

        <div class="row">
            <div class="col-12 bg-navbar text-center py-3 animate__animated animate__pulse animate__infinite">
                <h3 class="text-warning mb-0 inner-title">⋆｡°✩🍸 Thức uống🍸⋆｡°✩</h3>
            </div>
        </div>
        <div class="row">
            <div class="inner-card py-3 slider-drink" id="product-drink">
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

   <!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

<!-- Slick Carousel JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<!-- WOW.js -->
<script src="./js/wow.min.js"></script>
<script>new WOW().init();</script>

<!-- Search JS -->
<script src="js/search.js"></script>

<script>
// ==================== SLICK CAROUSEL ====================
function initProductSlick() {
  // Init cho tất cả .inner-card (pizza, khai vị, thức uống)
  $('.inner-card').each(function() {
    if ($(this).hasClass('slick-initialized')) {
      $(this).slick('unslick');
    }
    
    $(this).slick({
      infinite: true,
      dots: true,
      customPaging: function(slider, i) {
        return '<button>' + (i + 1) + '</button>';
      },
      slidesToShow: 3,
      slidesToScroll: 3,
      prevArrow: '<button class="slick-prev"><i class="fa fa-chevron-left bg-global"></i></button>',
      nextArrow: '<button class="slick-next me-5"><i class="fa fa-chevron-right bg-global"></i></button>',
      responsive: [{
        breakpoint: 992,
        settings: { slidesToShow: 2 }
      }, {
        breakpoint: 768,
        settings: { slidesToShow: 1 }
      }]
    });
  });
}

// ==================== MODAL SIZE ====================
function updateModal(data) {
  console.log(" Updating modal with:", data);
  
  // Kiểm tra dữ liệu
  if (!data || !data.product) {
    console.error(" Invalid data:", data);
    alert("Không thể tải thông tin sản phẩm!");
    return;
  }
  
  // Cập nhật tiêu đề modal
  $('#sizeModalLabel').text('Chọn size cho ' + data.product.TenSP);
  
  // Cập nhật hình ảnh (bỏ ./ nếu đường dẫn đã có)
  let imagePath = data.product.Anh;
  if (!imagePath.startsWith('./') && !imagePath.startsWith('http')) {
    imagePath = './' + imagePath;
  }
  $('.product-image').attr('src', imagePath).attr('alt', data.product.TenSP);
  
  // Cập nhật tên và mô tả
  $('.product-name').text(data.product.TenSP);
  $('.product-description').text(data.product.MoTa || '');
  
  // Cập nhật sizes
  let sizeHTML = '';
  if (data.sizes && data.sizes.length > 0) {
    data.sizes.forEach(function(size) {
      let sizeImagePath = size.Anh;
      if (!sizeImagePath.startsWith('./') && !sizeImagePath.startsWith('http')) {
        sizeImagePath = './' + sizeImagePath;
      }
      
      sizeHTML += `
        <div class="form-check">
          <input class="form-check-input size-radio" type="radio" 
                 name="size" id="size-${size.MaSize}"
                 value="${size.MaSize}" 
                 data-name="${size.TenSize}" 
                 data-price="${size.Gia}">
          <label class="form-check-label" for="size-${size.MaSize}">
            <img src="${sizeImagePath}" alt="" height="30px" class="me-2">
            ${size.TenSize} - ${parseInt(size.Gia).toLocaleString('vi-VN')} VNĐ
          </label>
        </div>
      `;
    });
  } else {
    sizeHTML = '<p class="text-danger">Sản phẩm này hiện chưa có size.</p>';
  }
  
  $('.size-container').html(sizeHTML);
  
  // Reset
  $('#quantity').val(1);
  $('#totalPrice').text('0 VNĐ');
  $('.selected-info').hide();
  $('#addToCartBtn').attr('disabled', true);
  
  // Lưu product ID
  $('#sizeModal').data('product-id', data.product.MaSP);
}

function updateTotal() {
  const selected = document.querySelector('.size-radio:checked');
  const quantityInput = document.getElementById('quantity');
  const quantity = parseInt(quantityInput.value) || 1;
  const addToCartBtn = document.getElementById('addToCartBtn');

  if (!selected) {
    document.getElementById('totalPrice').textContent = "0 VNĐ";
    document.querySelector('.selected-info').style.display = 'none';
    addToCartBtn.setAttribute('disabled', true);
    return;
  }

  const name = selected.dataset.name;
  const price = parseFloat(selected.dataset.price);
  const total = price * quantity;

  document.getElementById('selectedSize').textContent = name;
  document.getElementById('selectedPrice').textContent = price.toLocaleString('vi-VN');
  document.getElementById('totalPrice').textContent = total.toLocaleString('vi-VN') + " VNĐ";
  document.querySelector('.selected-info').style.display = 'block';

  const sizeId = selected.value;
  const productId = $('#sizeModal').data('product-id');

  // Không gán href, chỉ lưu vào data
  addToCartBtn.removeAttribute('disabled');
  addToCartBtn.dataset.productId = productId;
  addToCartBtn.dataset.sizeId = sizeId;
  addToCartBtn.dataset.quantity = quantity;
}

// Sự kiện khi nhấn “Thêm vào giỏ hàng”
document.getElementById('addToCartBtn').addEventListener('click', function () {
  const productId = this.dataset.productId;
  const sizeId = this.dataset.sizeId;
  const quantity = this.dataset.quantity || 1;

  if (!productId || !sizeId) {
    alert('Vui lòng chọn size trước!');
    return;
  }

  fetch(`./cart/add_to_cart.php?id=${productId}&masize=${sizeId}&soluong=${quantity}`)
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        // Hiển thị thông báo
        alert('Đã thêm sản phẩm vào giỏ hàng!');
        // Cập nhật số lượng icon giỏ hàng (nếu có)
        if (data.totalQuantity && document.querySelector('#cart-count')) {
          document.querySelector('#cart-count').textContent = data.totalQuantity;
        }
        // Đóng modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('sizeModal'));
        modal.hide();
      } else {
        alert('Có lỗi xảy ra khi thêm sản phẩm!');
      }
    })
    .catch(err => console.error('Lỗi fetch:', err));
});

// ==================== DOCUMENT READY ====================
$(document).ready(function() {
  console.log(" Initializing...");
  
  // Init Slick lần đầu
  initProductSlick();
  
  // ==================== FILTER CATEGORY ====================
  $(".btn-category").click(function(e) {
    e.preventDefault();
    let categoryId = $(this).data("id");
    console.log(" Category clicked:", categoryId);
    
    $(".btn-category").removeClass("active");
    $(this).addClass("active");

    $.ajax({
      url: "includes/query_products.php",
      method: "GET",
      data: { maloai: categoryId }
    })
    .done(function(data) {
      console.log(" Category data loaded");
      
      // Destroy slick trước
      if ($('#product-list').hasClass('slick-initialized')) {
        $('#product-list').slick('unslick');
      }
      
      // Update HTML
      $("#product-list").html(data);
      
      // Re-init chỉ cho #product-list
      setTimeout(function() {
        $('#product-list').slick({
          infinite: true,
          dots: true,
          customPaging: function(slider, i) {
            return '<button>' + (i + 1) + '</button>';
          },
          slidesToShow: 3,
          slidesToScroll: 3,
          prevArrow: '<button class="slick-prev"><i class="fa fa-chevron-left bg-global"></i></button>',
          nextArrow: '<button class="slick-next me-5"><i class="fa fa-chevron-right bg-global"></i></button>',
          responsive: [{
            breakpoint: 992,
            settings: { slidesToShow: 2 }
          }, {
            breakpoint: 768,
            settings: { slidesToShow: 1 }
          }]
        });
      }, 100);
    })
    .fail(function(xhr, status, error) {
      console.error(" Category request failed:", status, error);
    });
  });
  
  // ==================== MUA NGAY ====================
  $(document).on('click', '.btn-buy', function(e) {
    e.preventDefault();
    let id = $(this).data("masp");
    console.log("🛒 Buy button clicked, product ID:", id);

    $.ajax({
      url: "includes/query_products.php",
      method: "GET",
      data: { id: id },
      dataType: 'json' 
    })
    .done(function(response) {
      console.log(" Product data loaded:", response);
      
      // Update modal
      updateModal(response);
      
      // Lưu product ID vào modal
      document.getElementById('sizeModal').dataset.productId = id;
      
      // Hiển thị modal
      let modal = new bootstrap.Modal(document.getElementById('sizeModal'));
      modal.show();
    })
    .fail(function(xhr, status, error) {
      console.error(" Product request failed:", status, error);
      console.log("Response:", xhr.responseText);
      alert("Có lỗi xảy ra, vui lòng thử lại!");
    });
  });
  
  // ==================== SIZE & QUANTITY ====================
  $(document).on('change', '.size-radio', updateTotal);
  $(document).on('input', '#quantity', updateTotal);
  
  $(document).on('click', '#decreaseBtn', function() {
    let input = document.getElementById('quantity');
    let current = parseInt(input.value);
    if (current > 1) {
      input.value = current - 1;
      updateTotal();
    }
  });
  
  $(document).on('click', '#increaseBtn', function() {
    let input = document.getElementById('quantity');
    input.value = parseInt(input.value) + 1;
    updateTotal();
  });
});
</script>

</body>
</html>




