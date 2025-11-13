<?php
session_start();
require "includes/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: trangchu.php");
    exit();
}

$madon = $_GET['madon'];
$id = $_SESSION['user_id'];

// Lấy thông tin đơn hàng
$sql_order = "SELECT * FROM donhang WHERE MaDH='$madon' AND MaKH='$id'";
$result_order = mysqli_query($ketnoi, $sql_order);
$order_info = mysqli_fetch_assoc($result_order);

if (!$order_info) {
    header("Location: order_user.php");
    exit();
}

// Lấy chi tiết sản phẩm trong đơn hàng
$sql_detail_order = "SELECT 
    sp.TenSP,
    s.TenSize,
    sps.Anh,
    sps.Gia,
    ctdh.SoLuong,
    ctdh.ThanhTien
FROM chitietdonhang ctdh
INNER JOIN sanpham sp ON ctdh.MaSP = sp.MaSP
INNER JOIN size s ON ctdh.MaSize = s.MaSize
INNER JOIN sanpham_size sps ON ctdh.MaSP = sps.MaSP AND ctdh.MaSize = sps.MaSize
WHERE ctdh.MaDH='$madon'
ORDER BY ctdh.ThanhTien DESC";

$result_details = mysqli_query($ketnoi, $sql_detail_order);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Chi tiết đơn hàng - Pizza</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="./css/animate.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />

    <link rel="stylesheet" href="css/pizza.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/sign_up.css">
    <link rel="stylesheet" href="css/detail_order.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

</head>

<body>
    <header class="bg-icon" style="padding: 1px;">
        <?php include 'components/navbar.php'; ?>


        
    <div class="detail-order-container">
       

        <!-- Thông tin đơn hàng -->
        <div class="order-header bg-global">
             <a href="order_user.php" class="btn-back">
           <i class="fa-solid fa-chevron-left" style="color: #dfe4ec;"></i>
        </a>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-2">Đơn hàng #<?php echo $order_info['MaDH']; ?></h3>
                    <p class="mb-0">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order_info['ngaydat'])); ?></p>
                </div>
                <div>
                    <span class="order-status"><?php echo $order_info['trangthai']; ?></span>
                </div>
            </div>
        </div>

        <div class="order-info-box">
            <h5 class="mb-3"><i class="fas fa-info-circle"></i> Thông tin giao hàng</h5>
            <div class="info-row">
                <span class="info-label">Người nhận:</span>
                <span class="info-value"><?php echo $order_info['Tennguoinhan']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Số điện thoại:</span>
                <span class="info-value"><?php echo $order_info['sdtnguoinhan']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Địa chỉ:</span>
                <span class="info-value"><?php echo $order_info['diachinguoinhan']; ?></span>
            </div>
            <?php if(!empty($order_info['GhiChu'])): ?>
            <div class="info-row">
                <span class="info-label">Ghi chú:</span>
                <span class="info-value"><?php echo $order_info['GhiChu']; ?></span>
            </div>
            <?php endif; ?>
        </div>

    <!-- Danh sách sản phẩm -->
<h5 class="mb-3"><i class="fas fa-shopping-cart"></i> Sản phẩm đã đặt</h5>
<div class="">
    <?php while ($product = mysqli_fetch_assoc($result_details)): ?>
    <div class="product-item">
        <div class="d-flex align-items-center">
            <img src="./<?php echo $product['Anh']; ?>" alt="<?php echo $product['TenSP']; ?>" class="product-img">

            <div class="product-info">
                <div class="product-name"><?php echo $product['TenSP']; ?></div>
                <div class="product-size">Size: <?php echo $product['TenSize']; ?></div>
              
                <div class="product-price"><?php echo number_format($product['Gia'], 0, ',', '.'); ?>₫
                </div>
            </div>

            <div class="product-total">
                <?php echo number_format($product['ThanhTien'], 0, ',', '.'); ?>₫
                <div class="product-size" style="margin-top:10px">
                    Số lượng: <?php echo $product['SoLuong']; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

        <!-- Tổng kết -->
        <div class="summary-box">
            <div class="summary-row">
                <span>Tạm tính:</span>
                <span><?php echo number_format($order_info['TongTien'], 0, ',', '.'); ?>₫</span>
            </div>
            <div class="summary-row">
                <span>Phí vận chuyển:</span>
                <span>0₫</span>
            </div>
            <div class="summary-row summary-total">
                <span>Tổng cộng:</span>
                <span><?php echo number_format($order_info['TongTien'], 0, ',', '.'); ?>₫</span>
            </div>
        </div>

        <?php if($order_info['trangthai'] == 'Chờ xử lý'): ?>
        <div class="action-buttons-fixed">
            <button  data-order_id="<?php echo $order_info["MaDH"]; ?>" class="btn-cancel btn-cancel_order" >
                <i class="fas fa-times-circle"></i> Hủy đơn hàng
            </button>
            <button class="btn-payment" onclick="payOrder('<?php echo $madon; ?>')">
                <i class="fas fa-credit-card"></i> Thanh toán
            </button>
        </div>
        <?php endif; ?>
    </div>
    </header>


   

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="./js/wow.min.js"></script>

  <script src="./js/cancel_order.js"></script>
</body>

</html>