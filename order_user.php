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
    
    <style>
        /* Tracking styles */
        .tracking-container {
            padding: 15px 0;
            margin: 10px 0;
        }
        
        .tracking-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            padding: 0 10px;
        }
        
        .tracking-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 10%;
            width: 80%;
            height: 3px;
            background: #e0e0e0;
            z-index: 1;
        }
        
        .tracking-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            width: 20%;
        }
        
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #e0e0e0;
            margin-bottom: 8px;
            font-size: 18px;
            color: #999;
        }
        
        .step-icon.active {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }
        
        .step-icon.completed {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }
        
        .step-icon.cancelled {
            border-color: #dc3545;
            background: #dc3545;
            color: white;
        }
        
        .step-text {
            font-size: 12px;
            text-align: center;
            color: #666;
            max-width: 80px;
        }
        
        .step-text.active {
            color: #28a745;
            font-weight: bold;
        }
        
        .step-text.completed {
            color: #28a745;
        }
        
        .step-text.cancelled {
            color: #dc3545;
        }
        
        .tracking-line.active {
            background: #28a745 !important;
        }
        
        .tracking-line.cancelled {
            background: #dc3545 !important;
        }
        
        /* Status-specific tracking */
        .tracking-choxuly .step-1 .step-icon,
        .tracking-chogiao .step-1 .step-icon,
        .tracking-danggiao .step-1 .step-icon,
        .tracking-giaothanhcong .step-1 .step-icon {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .tracking-chogiao .step-2 .step-icon,
        .tracking-danggiao .step-2 .step-icon,
        .tracking-giaothanhcong .step-2 .step-icon {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .tracking-danggiao .step-3 .step-icon,
        .tracking-giaothanhcong .step-3 .step-icon {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .tracking-giaothanhcong .step-4 .step-icon {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .tracking-huy .step-1 .step-icon {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        /* Progress line color */
        .tracking-chogiao .step-1 .step-icon::after,
        .tracking-danggiao .step-1 .step-icon::after,
        .tracking-giaothanhcong .step-1 .step-icon::after,
        .tracking-danggiao .step-2 .step-icon::after,
        .tracking-giaothanhcong .step-2 .step-icon::after,
        .tracking-giaothanhcong .step-3 .step-icon::after {
          content: '';
    position: absolute;
    top: 35%;
    left: 57%;
    width: calc(135% + -3px);
    height: 3px;
    background: #28a745;
    z-index: 1;
    transform: translateY(-50%);
        }
        
        .tracking-huy .step-1 .step-icon::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: calc(100% + 10px);
            height: 3px;
            background: #dc3545;
            z-index: 1;
            transform: translateY(-50%);
        }
        
        .tracking-step:last-child .step-icon::after {
            display: none;
        }
        
   
        
        .order-item {
            transition: all 0.3s ease;
        }
        
        .order-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-cancel_order, .btn-pay_order {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-cancel_order {
            background: #dc3545;
            color: white;
        }
        
        .btn-cancel_order:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-pay_order {
            background: #28a745;
            color: white;
        }
        
        .btn-pay_order:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .img-thumbnail {
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
        }
    </style>
</head>

<body>
    <header class="bg-icon">
        <?php include 'components/navbar.php'; ?>

        <div class="form-order">
            <ul class="nav justify-content-center bg-global rounded-0  " style="margin-top:100px;">
                <li class="nav-item nav-order">
                    <button class="nav-link tab-link inner-order active" data-order="all">
                        Tất cả
                    </button>
                </li>
                <li class="nav-item nav-order">
                    <button class="nav-link tab-link inner-order" data-order="Chờ xử lý">
                        Chờ xử lý
                    </button>
                </li>
                <li class="nav-item nav-order">
                    <button class="nav-link tab-link inner-order" data-order="Chờ giao">
                        Chờ giao
                    </button>
                </li>
                <li class="nav-item nav-order">
                    <button class="nav-link tab-link inner-order" data-order="Đang giao">
                        Đang giao
                    </button>
                </li>
                <li class="nav-item nav-order">
                    <button class="nav-link tab-link inner-order" data-order="Giao thành công">
                        Giao thành công
                    </button>
                </li>
                <li class="nav-item nav-order">
                    <button class="nav-link tab-link inner-order" data-order="Hủy đơn">
                        Hủy đơn
                    </button>
                </li>
            </ul>

            <main>
                <div id="order-list">
                    <?php 
                    if (mysqli_num_rows($result) > 0):
                    while ($value = mysqli_fetch_assoc($result)): 
                        $danhSachAnh = explode(',', $value['DanhSachAnh']);
                        
                        // Xác định class tracking dựa trên trạng thái
                        $trackingClass = '';
                        switch($value['trangthai']) {
                            case 'Chờ xử lý':
                                $trackingClass = 'tracking-choxuly';
                                break;
                            case 'Chờ giao':
                                $trackingClass = 'tracking-chogiao';
                                break;
                            case 'Đang giao':
                                $trackingClass = 'tracking-danggiao';
                                break;
                            case 'Giao thành công':
                                $trackingClass = 'tracking-giaothanhcong';
                                break;
                            case 'Hủy đơn':
                                $trackingClass = 'tracking-huy';
                                break;
                            default:
                                $trackingClass = '';
                        }
                    ?>
                    <div class="order-item mb-3 p-3 pb-0 border rounded">
                        <div class="d-flex justify-content-between mb-2">
                            <b>Đơn hàng #<?php echo $value["MaDHcode"]; ?></b>
                            <a href="detail_order_user.php?madon=<?php echo $value["MaDH"]; ?>">Xem chi tiết ></a>
                        </div>

                        <!-- Tracking Section -->
                        <div class="order-status-tracking <?php echo $trackingClass; ?>">
                            <div class="tracking-container">
                                <div class="tracking-steps">
                                    <!-- Step 1: Chờ xử lý -->
                                    <div class="tracking-step step-1">
                                        <div class="step-icon <?php echo in_array($value['trangthai'], ['Chờ xử lý', 'Chờ giao', 'Đang giao', 'Giao thành công', 'Hủy đơn']) ? 'active' : ''; ?>">
                                            <img src="./img/hinhthuc_2.png" alt="" width="30" height="30">
                                        </div>
                                        <div class="step-text <?php echo in_array($value['trangthai'], ['Chờ xử lý', 'Chờ giao', 'Đang giao', 'Giao thành công', 'Hủy đơn']) ? 'active' : ''; ?>">
                                            Chờ xử lý
                                        </div>
                                    </div>
                                    
                                    <!-- Step 2: Chuẩn bị hàng -->
                                    <div class="tracking-step step-2">
                                        <div class="step-icon <?php echo in_array($value['trangthai'], ['Chờ giao', 'Đang giao', 'Giao thành công']) ? 'active' : ''; ?>">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div class="step-text <?php echo in_array($value['trangthai'], ['Chờ giao', 'Đang giao', 'Giao thành công']) ? 'active' : ''; ?>">
                                            Soạn hàng
                                        </div>
                                    </div>
                                    
                                    <!-- Step 3: Đang giao -->
                                    <div class="tracking-step step-3">
                                        <div class="step-icon <?php echo in_array($value['trangthai'], ['Đang giao', 'Giao thành công']) ? 'active' : ''; ?>">
                                            <img src="./img/hinhthuc_1.png" alt="" width="30" height="30">
                                        </div>
                                        <div class="step-text <?php echo in_array($value['trangthai'], ['Đang giao', 'Giao thành công']) ? 'active' : ''; ?>">
                                            Đang giao
                                        </div>
                                    </div>
                                    
                                    <!-- Step 4: Giao hàng -->
                                    <div class="tracking-step step-4">
                                        <div class="step-icon <?php echo $value['trangthai'] == 'Giao thành công' ? 'active' : ''; ?>">
                                            <i class="fas fa-home"></i>
                                        </div>
                                        <div class="step-text <?php echo $value['trangthai'] == 'Giao thành công' ? 'active' : ''; ?>">
                                            Đã giao
                                        </div>
                                    </div>
                                    
                                    <!-- Step 5: Hủy (nếu có) -->
                                    <?php if($value['trangthai'] == 'Hủy đơn'): ?>
                                    <div class="tracking-step step-5">
                                        <div class="step-icon cancelled">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                        <div class="step-text cancelled">
                                            Đã hủy
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Status description -->
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <?php 
                                        switch($value['trangthai']) {
                                            case 'Chờ xử lý':
                                                echo 'Đơn hàng đang được xác nhận bởi cửa hàng';
                                                break;
                                            case 'Chờ giao':
                                                echo 'Đơn hàng đang được chuẩn bị và đóng gói';
                                                break;
                                            case 'Đang giao':
                                                echo 'Đơn hàng đang trên đường giao đến bạn';
                                                break;
                                            case 'Giao thành công':
                                                echo 'Đơn hàng đã được giao thành công';
                                                break;
                                            case 'Hủy đơn':
                                                echo 'Đơn hàng đã bị hủy';
                                                break;
                                            default:
                                                echo 'Đang cập nhật trạng thái';
                                        }
                                        ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <p class="mb-0"><b>Thời gian đặt hàng:</b> <?php echo date('d/m/Y H:i', strtotime($value["ngaydat"])); ?></p>
                            </div>
                            <div>
                                <p class="mb-0">
                                    <span class="badge 
                                        <?php 
                                        switch($value['trangthai']) {
                                            case 'Chờ xử lý': echo 'bg-warning'; break;
                                            case 'Chờ giao': echo 'bg-info'; break;
                                            case 'Đang giao': echo 'bg-primary'; break;
                                            case 'Giao thành công': echo 'bg-success'; break;
                                            case 'Hủy đơn': echo 'bg-danger'; break;
                                            default: echo 'bg-secondary';
                                        }
                                        ?> text-white fw-bolder fs-6">
                                        <?php echo $value["trangthai"]; ?>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <div><strong>Tổng tiền:</strong></div>
                            <div class="text-danger fw-bold">
                                <?php echo number_format($value["TongTien"], 0, ',', '.'); ?>₫
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <div><strong>Phương thức thanh toán:</strong></div>
                            <div>
                                <div class="fw-bold"><?php echo $value["phuongthucthanhtoan"] ?></div>
                                <?php if($value["trangthaithanhtoan"]=="chuathanhtoan"): ?>
                                <div class="text-danger fw-bold">Chưa thanh toán</div>
                                <?php else :?>
                                <div class="text-success fw-bold">Đã thanh toán</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-3">
                            <?php 
                            $count = 0;
                            foreach ($danhSachAnh as $anh):
                                if ($count >= 3) break;
                                if(!empty(trim($anh))):
                            ?>
                            <img src="./<?php echo trim($anh); ?>" alt="Product" class="img-thumbnail"
                                style="width: 80px; height: 80px; object-fit: contain;">
                            <?php 
                                $count++;
                                endif;
                            endforeach;
                            
                            if (count($danhSachAnh) > 3):
                            ?>
                            <div class="d-flex align-items-center justify-content-center img-thumbnail"
                                style="width: 80px; height: 80px; background: #f8f9fa;">
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

                                <div class="d-flex justify-content-around align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <input class="form-check-input me-2" type="radio" name="transfer_method"
                                            id="momo_<?php echo $value['MaDH']; ?>" value="momo" checked>
                                        <label class="form-check-label" for="momo_<?php echo $value['MaDH']; ?>">
                                            <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png"
                                                alt="MoMo" style="height: 50px; vertical-align: middle;">
                                            Thanh toán qua MoMo
                                        </label>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <input class="form-check-input me-2" type="radio" name="transfer_method"
                                            id="vnpay_<?php echo $value['MaDH']; ?>" value="vnpay">
                                        <label class="form-check-label" for="vnpay_<?php echo $value['MaDH']; ?>">
                                            <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png"
                                                alt="VNPay" style="height: 50px; vertical-align: middle;">
                                            Thanh toán qua VNPay
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="d-flex gap-2">
                                <?php 
                                // TH1: Đơn chờ xử lý + chưa thanh toán → Hiện 2 nút: Hủy đơn + Thanh toán
                                if ($value["trangthai"] == "Chờ xử lý" && $value["trangthaithanhtoan"] == "chuathanhtoan") { 
                                ?>
                                <button data-order_id="<?php echo $value["MaDH"]; ?>"
                                    class="col-6 btn-cancel_order">Hủy đơn hàng</button>
                                <button type="submit" name="btn_pay_order" class="col-6 btn-pay_order">Thanh toán</button>
                                
                                <?php 
                                // TH2: Đơn chờ xử lý + đã thanh toán → Chỉ cho phép Hủy đơn
                                } else if ($value["trangthai"] == "Chờ xử lý" && $value["trangthaithanhtoan"] == "dathanhtoan") { 
                                ?>
                                <button data-order_id="<?php echo $value["MaDH"]; ?>" class="col-12 btn-cancel_order">Hủy đơn hàng</button>
                                
                                <?php 
                                // TH3: Đơn không phải chờ xử lý + chưa thanh toán → Chỉ hiển thị "Thanh toán"
                                } else if ($value["trangthai"] != "Chờ xử lý" && $value["trangthaithanhtoan"] == "chuathanhtoan") { 
                                ?>
                                <button type="submit" name="btn_pay_order" class="col-12 btn-pay_order">Thanh toán</button>
                                <?php } ?>
                            </div>
                        </form>
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
                    
                    // Thêm hiệu ứng fade in
                    $("#order-list").hide().fadeIn(500);
                },
                error: function(xhr, status, error) {
                    console.log("error:", error);
                }
            });
        });
        
        // Hiệu ứng hover cho nút hủy đơn
        $(document).on('mouseenter', '.btn-cancel_order', function() {
            $(this).css('transform', 'translateY(-2px)');
        }).on('mouseleave', '.btn-cancel_order', function() {
            $(this).css('transform', 'translateY(0)');
        });
        
        // Hiệu ứng hover cho nút thanh toán
        $(document).on('mouseenter', '.btn-pay_order', function() {
            $(this).css('transform', 'translateY(-2px)');
        }).on('mouseleave', '.btn-pay_order', function() {
            $(this).css('transform', 'translateY(0)');
        });
    });
    </script>

    <script src="./js/cancel_order.js"></script>
    <script src="./js/wow.min.js"></script>
</body>
</html>