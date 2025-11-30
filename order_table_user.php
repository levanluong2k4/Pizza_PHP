<?php
session_start();
require "includes/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: trangchu.php");
    exit();
}

$id = $_SESSION['user_id'];

// Query lấy đầy đủ thông tin đặt bàn
$sql = "SELECT 
    datban.MaDatBan,
    datban.NgayTao,
    datban.NgayGio,
    datban.TrangThaiDatBan,
    datban.TrangThaiThanhToan,
    datban.LoaiDatBan,
    datban.Tongtien,
    datban.HoTen,
    datban.SDT,
    datban.GhiChu,
    phongtiec.TenPhong,
    phongtiec.SucChua,
    banan.SoBan,
    banan.KhuVuc,
    combo.Tencombo,
    GROUP_CONCAT(DISTINCT sanpham_size.Anh SEPARATOR ',') as DanhSachAnh
FROM datban
LEFT JOIN chitietdatban ON datban.MaDatBan = chitietdatban.MaDatBan
LEFT JOIN sanpham_size ON chitietdatban.MaSP = sanpham_size.MaSP 
    AND chitietdatban.MaSize = sanpham_size.MaSize
LEFT JOIN phongtiec ON datban.MaPhong = phongtiec.MaPhong
LEFT JOIN banan ON datban.MaBan = banan.MaBan
LEFT JOIN combo ON datban.MaCombo = combo.MaCombo
WHERE datban.MaKH = ?
GROUP BY datban.MaDatBan
ORDER BY datban.NgayTao DESC";

$stmt = $ketnoi->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
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
                                     " data-order="da_dat">
                        Đã đặt bàn
                    </button></li>

                <li class="nav-item nav-order"> <button class="nav-link tab-link inner-order
                                     " data-order="da_xac_nhan">
                        Đã xác nhận
                    </button></li>
                <li class="nav-item nav-order"> <button class="nav-link tab-link inner-order
                                     " data-order="dang_su_dung">
                        Đang sử dụng
                    </button></li>
                <li class="nav-item nav-order"> <button class="nav-link tab-link inner-order
                                     " data-order="hoan_thanh">
                        Hoàn thành
                    </button></li>
                <li class="nav-item nav-order"> <button class="nav-link tab-link inner-order
                                     " data-order="da_huy">
                        Đã hủy bàn
                    </button></li>

            </ul>


            <main>
                <div id="order-list">
                  
   <?php 
if ($result->num_rows > 0):
    while ($value = $result->fetch_assoc()): 
        $danhSachAnh = !empty($value['DanhSachAnh']) ? explode(',', $value['DanhSachAnh']) : [];
        
        // Format trạng thái
        $trangThaiText = [
            'da_dat' => 'Đã đặt bàn',
            'da_xac_nhan' => 'Đã xác nhận',
            'dang_su_dung' => 'Đang sử dụng',
            'hoan_thanh' => 'Hoàn thành',
            'da_huy' => 'Đã hủy'
        ];
?>

<div class="order-item mb-3 p-3 pb-0 border rounded">
    <!-- Header: Mã đơn + Ngày tạo + Trạng thái -->
    <div class="d-flex justify-content-between mb-2">
        <div class="border-bottom">
            <b class="fs-4">Đơn đặt bàn #<?php echo $value["MaDatBan"]; ?></b>
            <br>
            <small class="text-muted">
                <i class="far fa-calendar me-1"></i>
                Tạo lúc: <?php echo date('d/m/Y H:i:s', strtotime($value["NgayTao"])); ?>
            </small>
        </div>
        <div class="text-end">
            <span class="badge text-success fw-bolder fs-6">
                <?php echo $trangThaiText[$value["TrangThaiDatBan"]] ?? $value["TrangThaiDatBan"]; ?>
            </span>
            <br>
            <a href="detail_order_user.php?madatban=<?php echo $value["MaDatBan"]; ?>">
                Xem chi tiết <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Thông tin người đặt -->
    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="fas fa-user me-2"></i>Người đặt:</strong></div>
        <div><?php echo htmlspecialchars($value["HoTen"]); ?></div>
    </div>

    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="fas fa-phone me-2"></i>Số điện thoại:</strong></div>
        <div><?php echo htmlspecialchars($value["SDT"]); ?></div>
    </div>

    <!-- Ngày giờ đến (quan trọng!) -->
    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="far fa-calendar-check me-2"></i>Ngày giờ đến:</strong></div>
        <div class="text-primary fw-bold">
            <?php echo date('d/m/Y H:i', strtotime($value["NgayGio"])); ?>
        </div>
    </div>

    <!-- Loại đặt bàn: Phòng tiệc hoặc Bàn -->
    <div class="d-flex justify-content-between mb-2">
        <div>
            <strong>
                <i class="fas fa-<?php echo $value['LoaiDatBan'] == 'tiec' ? 'door-open' : 'chair'; ?> me-2"></i>
                <?php echo $value['LoaiDatBan'] == 'tiec' ? 'Phòng tiệc:' : 'Bàn:'; ?>
            </strong>
        </div>
        <div>
            <?php 
            if ($value['LoaiDatBan'] == 'tiec') {
                echo ($value['TenPhong'] ?? 'Chưa xác định');
                if (!empty($value['SucChua'])) {
                    echo ' <span class="text-muted">(' . $value['SucChua'] . ' người)</span>';
                }
            } else {
                echo 'Bàn số ' . ($value['SoBan'] ?? 'N/A');
                if (!empty($value['KhuVuc'])) {
                    echo ' - ' . $value['KhuVuc'];
                }
            }
            ?>
        </div>
    </div>

    <!-- Combo (nếu là tiệc) -->
    <?php if ($value['LoaiDatBan'] == 'tiec' && !empty($value['Tencombo'])): ?>
    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="fas fa-box-open me-2"></i>Combo:</strong></div>
        <div><?php echo htmlspecialchars($value['Tencombo']); ?></div>
    </div>
    <?php endif; ?>

    <!-- Tổng tiền -->
    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="fas fa-money-bill-wave me-2"></i>Tổng tiền:</strong></div>
        <div class="text-danger fw-bold fs-5">
            <?php echo number_format($value["Tongtien"], 0, ',', '.'); ?>₫
        </div>
    </div>

    <!-- Trạng thái thanh toán -->
    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="fas fa-credit-card me-2"></i>Thanh toán:</strong></div>
        <div>
            <?php if($value["TrangThaiThanhToan"] == "chuathanhtoan"): ?>
                <span class="text-danger fw-bold">
                    <i class="fas fa-exclamation-circle"></i> Chưa thanh toán
                </span>
            <?php else: ?>
                <span class="text-success fw-bold">
                    <i class="fas fa-check-circle"></i> Đã thanh toán
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ghi chú (nếu có) -->
    <?php if (!empty($value['GhiChu'])): ?>
    <div class="alert alert-info mb-2">
        <i class="fas fa-sticky-note me-2"></i>
        <strong>Ghi chú:</strong> <?php echo htmlspecialchars($value['GhiChu']); ?>
    </div>
    <?php endif; ?>

    <!-- Hình ảnh sản phẩm -->
    <div class="d-flex gap-2 mb-2">
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
        
        if (count($danhSachAnh) > 3):
        ?>
            <div class="d-flex align-items-center justify-content-center img-thumbnail"
                 style="width: 80px; height: 80px;">
                <span>+<?php echo (count($danhSachAnh) - 3); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Form thanh toán -->
    <form action="./handlers/pay_order.php" method="post">
        <input type="hidden" name="order_table_id" value="<?php echo $value["MaDatBan"]; ?>">
        
        <?php if($value["TrangThaiThanhToan"] == "chuathanhtoan" && $value["TrangThaiDatBan"] != "da_huy"): ?>
        <div class="form-section">
            <div class="form-section-title fw-bold">
                <i class="fa-solid fa-credit-card"></i>
                Thanh toán trực tuyến
            </div>

            <div class="d-flex justify-content-around align-items-center mb-2">
                <div class="d-flex align-items-center">
                    <input class="form-check-input me-2" type="radio" name="transfer_method"
                           id="momo_<?php echo $value["MaDatBan"]; ?>" value="momo" checked>
                    <label class="form-check-label" for="momo_<?php echo $value["MaDatBan"]; ?>">
                        <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png"
                             alt="MoMo" style="height: 50px; vertical-align: middle;">
                        Thanh toán qua MoMo
                    </label>
                </div>
                <div class="d-flex align-items-center">
                    <input class="form-check-input me-2" type="radio" name="transfer_method"
                           id="vnpay_<?php echo $value["MaDatBan"]; ?>" value="vnpay">
                    <label class="form-check-label" for="vnpay_<?php echo $value["MaDatBan"]; ?>">
                        <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png"
                             alt="VNPay" style="height: 50px; vertical-align: middle;">
                        Thanh toán qua VNPay
                    </label>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="align-content-end d-flex p-0">
            <?php if ($value["TrangThaiDatBan"] == "da_dat"): ?>
                <?php if ($value["TrangThaiThanhToan"] == "chuathanhtoan"): ?>
                    <!-- Chưa thanh toán: 2 nút -->
                    <button type="button" data-order_table_id="<?php echo $value["MaDatBan"]; ?>"
                            class="col-6 btn-cancel_order">
                        <i class="fas fa-times me-1"></i>Hủy đơn
                    </button>
                    <button type="submit" name="btn_pay_order" class="col-6 btn-pay_order">
                        <i class="fas fa-credit-card me-1"></i>Thanh toán
                    </button>
                <?php else: ?>
                    <!-- Đã thanh toán: chỉ nút hủy -->
                    <button type="button" data-order_table_id="<?php echo $value["MaDatBan"]; ?>"
                            class="col-12 btn-cancel_order">
                        <i class="fas fa-times me-1"></i>Hủy đơn hàng
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php 
    endwhile;
else:
?>
<div class="text-center mt-4 p-4">
    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
    <p class="text-muted fs-5">Chưa có đơn đặt bàn nào.</p>
    <a href="datban/index.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Đặt bàn ngay
    </a>
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
            let trangthaidatban = $(this).data("order");
            btn_order.removeClass("active");
            $(this).addClass("active");

            $.ajax({
                url: "handlers/filter_orders_table.php",
                type: "POST",
                data: {
                    trangthaidatban: trangthaidatban
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