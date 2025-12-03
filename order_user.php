<?php
session_start();
require "includes/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: trangchu.php");
    exit();
}

$id = $_SESSION['user_id'];
// Khi mới vào trang, luôn hiển thị tất cả đơn hàng
   $sql = "SELECT 
        donhang.MaDH,
        donhang.MaDHcode,
        donhang.ngaydat,
        donhang.trangthai,
        donhang.phuongthucthanhtoan,
        donhang.trangthaithanhtoan,
        donhang.is_guest,
        donhang.TongTien,
        GROUP_CONCAT(DISTINCT sanpham_size.Anh SEPARATOR ',') as DanhSachAnh
    FROM donhang, chitietdonhang, sanpham_size, khachhang
    WHERE donhang.MaDH = chitietdonhang.MaDH
        AND chitietdonhang.MaSP = sanpham_size.MaSP
        AND chitietdonhang.MaSize = sanpham_size.MaSize
        AND donhang.MaKH = khachhang.MaKH
        AND donhang.MaKH = '$id'
    GROUP BY donhang.MaDH
    ORDER BY donhang.ngaydat DESC";
$result = mysqli_query($ketnoi, $sql);
?>




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
    <link rel="stylesheet" href="css/pizza.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/sign_up.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <header class="bg-icon">
        <?php include 'components/navbar.php'; ?>

        <div class="form-order">
            <ul class="nav justify-content-center bg-global rounded-0  " style="margin-top:100px;">




                <li class="nav-item nav-order">
                    <button class="nav-link tab-link inner-order active
                                     " data-order="all">
                        Tất cả
                    </button>
                </li>
                <li class="nav-item nav-order"><button class="nav-link tab-link  inner-order
                                     " data-order="chờ xử lý">
                        Chờ xử lý
                    </button></li>
                <li class="nav-item nav-order"> <button class="nav-link tab-link inner-order
                                     " data-order="Chờ giao">
                        Chờ giao
                    </button></li>
                <li class="nav-item nav-order"> <button class="nav-link tab-link inner-order
                                     " data-order="Đang giao">
                        Đang giao
                    </button></li>
                <li class="nav-item nav-order"> <button class="nav-link tab-link inner-order
                                     " data-order="Giao thành công">
                        Giao thành công
                    </button></li>
                <li class="nav-item nav-order"> <button class="nav-link tab-link inner-order
                                     " data-order="Hủy đơn">
                        Hủy đơn
                    </button></li>
            </ul>


            <main>
                <div id="order-list">
                    <?php 
            if (mysqli_num_rows($result) > 0):
            while ($value = mysqli_fetch_assoc($result)): 
                $danhSachAnh = explode(',', $value['DanhSachAnh']);
            ?>

                    <div class="order-item mb-3 p-3 pb-0 border rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    <b>Đơn hàng #<?php echo $value["MaDHcode"]; ?></b>
                                    <a href="detail_order_user.php?madon=<?php echo $value["MaDH"]; ?>">Xem chi tiết ></a>
                                </div>

                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <p class="mb-0"> <b>thời gian đặt
                                        hàng:</b><?php echo  date('d/m/Y H:i', strtotime($value["ngaydat"])); ?></p>
                            </div>
                            <div>
                                <p class="mb-0"><span
                                        class="badge text-success fw-bolder  fs-6"><?php echo $value["trangthai"]; ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <div><strong>Tổng tiền:</strong></div>
                            <div class="text-danger fw-bold">
                                <?php echo number_format($value["TongTien"], 0, ',', '.'); ?>₫</div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <div><strong>Phương thức thanh toán:</strong></div>
                            <div>
                                <div class=" fw-bold"><?php echo $value["phuongthucthanhtoan"] ?></div>
                                <?php if($value["trangthaithanhtoan"]=="chuathanhtoan"): ?>
                                <div class="text-danger fw-bold"><?php  echo "Chưa thanh toán" ?></div>
                                <?php else :?>
                                <div class="text-success fw-bold"><?php  echo "Đã thanh toán" ?></div>
                                <?php endif; ?>
                            </div>
                        </div>


                        <div class="d-flex gap-2">
                            <?php 
                    $count = 0;
                    foreach ($danhSachAnh as $anh):
                      if ($count >= 3) break;
                    ?>
                            <img src="./<?php echo trim($anh); ?>" alt="Product" class="img-thumbnail"
                                style="width: 80px; height: 80px; object-fit: contain;">
                            <?php 
                          $count++;
                        endforeach;
                        
                        // Hiển thị số lượng sản phẩm còn lại
                        if (count($danhSachAnh) > 3):
                        ?>
                            <div class="d-flex align-items-center justify-content-center img-thumbnail"
                                style="width: 80px; height: 80px;">
                                <span>+<?php echo (count($danhSachAnh) - 3); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>


                        <form action="./handlers/pay_order.php" method="post">
                            <input type="hidden" name="order_id" value="<?php echo $value["MaDHcode"]; ?>">
                            <?php if($value["trangthaithanhtoan"] == "chuathanhtoan"): ?>

                            <div class="form-section">
                                <div class="form-section-title fw-bold">
                                    <i class="fa-solid fa-credit-card"></i>
                                    Thanh toán trực tuyến
                                </div>

                                <div class=" d-flex justify-content-around align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <input class="form-check-input me-2" type="radio" name="transfer_method"
                                            id="momo" value="momo" checked>
                                        <label class="form-check-label" for="momo">
                                            <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png"
                                                alt="MoMo" style="height: 50px; vertical-align: middle;">Thanh toán qua
                                            MoMo
                                        </label>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <input class="form-check-input me-2" type="radio" name="transfer_method"
                                            id="vnpay" value="vnpay">
                                        <label class="form-check-label" for="vnpay">
                                            <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png"
                                                alt="VNPay" style="height: 50px; vertical-align: middle;">Thanh toán qua
                                            VNPay
                                        </label>
                                    </div>

                                </div>

                            </div>
                            <?php endif; ?>

                            <div class="align-content-end d-flex p-0">

                                <?php 
                    // TH1: Đơn chờ xử lý + chưa thanh toán → Hiện 2 nút: Hủy đơn + Thanh toán
                    if ($value["trangthai"] == "Chờ xử lý" && $value["trangthaithanhtoan"] != "chuathanhtoan") { 
                    ?>
                                                <button data-order_id="<?php echo $value["MaDH"]; ?>"
                                                    class="col-12 btn-cancel_order">Hủy đơn hàng</button>


                                                      <?php 
                          // TH2: Đơn chờ xử lý + đã thanh toán → Chỉ cho phép Hủy đơn
                          } else if ($value["trangthai"] == "Chờ xử lý" && $value["trangthaithanhtoan"] != "dathanhtoan") { 
                          ?>
                                                      <button data-order_id="<?php echo $value["MaDH"]; ?>" class="col-6 btn-cancel_order">Hủy
                                                          đơn hàng</button>
                                                      <button type="submit" name="btn_pay_order" class="col-6 btn-pay_order">Thanh
                                                          toán</button>
                                                      <?php 
                          // TH3: Đơn không phải chờ xử lý + chưa thanh toán → Chỉ hiển thị "Thanh toán"
                          } else if ($value["trangthai"] != "Chờ xử lý" && $value["trangthaithanhtoan"] == "chuathanhtoan") { 
                          ?>
                                                      <button type="submit" name="btn_pay_order" class="col-12 btn-pay_order">Thanh
                                                          toán</button>

                                                      <?php } ?>

                        </form>
                    </div>

                </div>

                            <?php 
                  endwhile;
                else:
                ?>
                            <div class="text-center mt-4 p-4">
                                <p class="text-muted">Chưa có đơn hàng nào.</p>
                            </div>
                            <?php endif; ?>
        </div>
        </main>



        </div>





    </header>




    <?php include 'components/footer.php'; ?>

    <!-- jQuery (phải load trước slick) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <!-- Slick Carousel JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <script>
    $(document).ready(function() {
        const btn_order = $(".inner-order");



        // Filter orders
        btn_order.click(function() {
            let trangthai = $(this).data("order");
            btn_order.removeClass("active");
            $(this).addClass("active");

            $.ajax({
                url: "handlers/filter_orders.php",
                type: "POST",
                data: {
                    trangthai: trangthai
                },
                success: function(response) {
                    $("#order-list").html(response);
                },
                error: function(xhr, status, error) {
                    console.log("error:", error);
                }
            });
        });
    });
    </script>

    <script src="./js/cancel_order.js"></script>
    <script src="./js/wow.min.js"></script>
</body>

</html>