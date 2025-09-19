<?php
$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

// Lấy sản phẩm theo maloai
if (isset($_POST['maloai'])) {
    $maloai = intval($_POST['maloai']);
    $sql_sp = "SELECT * FROM sanpham WHERE MaLoai = $maloai";
} else {
    $sql_sp = "SELECT * FROM sanpham";
}
$sanpham_rs = mysqli_query($ketnoi, $sql_sp);
?>




<div class="inner-list1 py-3">
    <?php foreach ($sanpham_rs as $sp): ?>
    <div class="col-lg-4 col-6 wow animate__bounceInLeft">
        <div class="inner-items text-center">
            <div class="card border-0 bg-transparent">
                <img src="./<?php echo $sp["Anh"] ?>" class="card-img-top mx-auto" alt="<?php echo $sp["TenSP"] ?>">
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

<script>
$('.inner-list1').slick({
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