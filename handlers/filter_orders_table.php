<?php
session_start();
require "../includes/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$id = $_SESSION['user_id'];
$trangthai = $_POST["trangthaidatban"] ?? "all";

// Xây dựng câu SQL với đầy đủ thông tin
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
WHERE datban.MaKH = ?";

// Thêm điều kiện lọc trạng thái
if ($trangthai != "all" && strtolower($trangthai) != "all") {
    $sql .= " AND datban.TrangThaiDatBan = ?";
}

$sql .= " GROUP BY datban.MaDatBan ORDER BY datban.NgayTao DESC";

// Prepared statement
$stmt = $ketnoi->prepare($sql);
if ($trangthai != "all" && strtolower($trangthai) != "all") {
    $stmt->bind_param("is", $id, $trangthai);
} else {
    $stmt->bind_param("i", $id);
}

$stmt->execute();
$result = $stmt->get_result();

// Format trạng thái
$trangThaiText = [
    'da_dat' => 'Đã đặt bàn',
    'da_xac_nhan' => 'Đã xác nhận',
    'dang_su_dung' => 'Đang sử dụng',
    'hoan_thanh' => 'Hoàn thành',
    'da_huy' => 'Đã hủy'
];

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
        $danhSachAnh = !empty($row['DanhSachAnh']) ? explode(',', $row['DanhSachAnh']) : [];
?>

<div class="order-item mb-3 p-3 pb-0 border rounded">
    <!-- Header: Mã đơn + Ngày tạo + Trạng thái -->
    <div class="d-flex justify-content-between mb-2">
        <div class="border-bottom">
            <b class="fs-4">Đơn đặt bàn #<?php echo $row["MaDatBan"]; ?></b>
            <br>
            <small class="text-muted">
                <i class="far fa-calendar me-1"></i>
                Tạo lúc: <?php echo date('d/m/Y H:i:s', strtotime($row["NgayTao"])); ?>
            </small>
        </div>
        <div class="text-end">
            <span class="badge text-success fw-bolder fs-6">
                <?php echo $trangThaiText[$row["TrangThaiDatBan"]] ?? $row["TrangThaiDatBan"]; ?>
            </span>
            <br>
            <a href="detail_order_user.php?madatban=<?php echo $row["MaDatBan"]; ?>">
                Xem chi tiết <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Thông tin người đặt -->
    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="fas fa-user me-2"></i>Người đặt:</strong></div>
        <div><?php echo htmlspecialchars($row["HoTen"]); ?></div>
    </div>

    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="fas fa-phone me-2"></i>Số điện thoại:</strong></div>
        <div><?php echo htmlspecialchars($row["SDT"]); ?></div>
    </div>

    <!-- Ngày giờ đến -->
    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="far fa-calendar-check me-2"></i>Ngày giờ đến:</strong></div>
        <div class="text-primary fw-bold">
            <?php echo date('d/m/Y H:i', strtotime($row["NgayGio"])); ?>
        </div>
    </div>

    <!-- Loại đặt bàn: Phòng tiệc hoặc Bàn -->
    <div class="d-flex justify-content-between mb-2">
        <div>
            <strong>
                <i class="fas fa-<?php echo $row['LoaiDatBan'] == 'tiec' ? 'door-open' : 'chair'; ?> me-2"></i>
                <?php echo $row['LoaiDatBan'] == 'tiec' ? 'Phòng tiệc:' : 'Bàn:'; ?>
            </strong>
        </div>
        <div>
            <?php 
            if ($row['LoaiDatBan'] == 'tiec') {
                echo ($row['TenPhong'] ?? 'Chưa xác định');
                if (!empty($row['SucChua'])) {
                    echo ' <span class="text-muted">(' . $row['SucChua'] . ' người)</span>';
                }
            } else {
                echo 'Bàn số ' . ($row['SoBan'] ?? 'N/A');
                if (!empty($row['KhuVuc'])) {
                    echo ' - ' . $row['KhuVuc'];
                }
            }
            ?>
        </div>
    </div>

    <!-- Combo (nếu là tiệc) -->
    <?php if ($row['LoaiDatBan'] == 'tiec' && !empty($row['Tencombo'])): ?>
    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="fas fa-box-open me-2"></i>Combo:</strong></div>
        <div><?php echo htmlspecialchars($row['Tencombo']); ?></div>
    </div>
    <?php endif; ?>

    <!-- Tổng tiền -->
    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="fas fa-money-bill-wave me-2"></i>Tổng tiền:</strong></div>
        <div class="text-danger fw-bold fs-5">
            <?php echo number_format($row["Tongtien"], 0, ',', '.'); ?>₫
        </div>
    </div>

    <!-- Trạng thái thanh toán -->
    <div class="d-flex justify-content-between mb-2">
        <div><strong><i class="fas fa-credit-card me-2"></i>Thanh toán:</strong></div>
        <div>
            <?php if($row["TrangThaiThanhToan"] == "chuathanhtoan"): ?>
                <span class="text-danger fw-bold">
                    <i class="fas fa-exclamation-circle"></i> Chưa thanh toán
                </span>
            <?php elseif($row["TrangThaiThanhToan"] == "da_coc"): ?>
                <span class="text-warning fw-bold">
                    <i class="fas fa-money-check-alt"></i> Đã cọc
                </span>
            <?php else: ?>
                <span class="text-success fw-bold">
                    <i class="fas fa-check-circle"></i> Đã thanh toán
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ghi chú (nếu có) -->
    <?php if (!empty($row['GhiChu'])): ?>
    <div class="alert alert-info mb-2">
        <i class="fas fa-sticky-note me-2"></i>
        <strong>Ghi chú:</strong> <?php echo htmlspecialchars($row['GhiChu']); ?>
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
        <input type="hidden" name="order_table_id" value="<?php echo $row["MaDatBan"]; ?>">
        
        <?php if($row["TrangThaiThanhToan"] == "chuathanhtoan" && $row["TrangThaiDatBan"] != "da_huy"): ?>
        <div class="form-section">
            <div class="form-section-title fw-bold">
                <i class="fa-solid fa-credit-card"></i>
                Thanh toán trực tuyến
            </div>

            <div class="d-flex justify-content-around align-items-center mb-2">
                <div class="d-flex align-items-center">
                    <input class="form-check-input me-2" type="radio" name="transfer_method"
                           id="momo_<?php echo $row["MaDatBan"]; ?>" value="momo" checked>
                    <label class="form-check-label" for="momo_<?php echo $row["MaDatBan"]; ?>">
                        <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png"
                             alt="MoMo" style="height: 50px; vertical-align: middle;">
                        Thanh toán qua MoMo
                    </label>
                </div>
                <div class="d-flex align-items-center">
                    <input class="form-check-input me-2" type="radio" name="transfer_method"
                           id="vnpay_<?php echo $row["MaDatBan"]; ?>" value="vnpay">
                    <label class="form-check-label" for="vnpay_<?php echo $row["MaDatBan"]; ?>">
                        <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png"
                             alt="VNPay" style="height: 50px; vertical-align: middle;">
                        Thanh toán qua VNPay
                    </label>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="align-content-end d-flex p-0">
            <?php if ($row["TrangThaiDatBan"] == "da_dat"): ?>
                <?php if ($row["TrangThaiThanhToan"] == "chuathanhtoan"): ?>
                    <!-- Chưa thanh toán: 2 nút -->
                    <button type="button" data-order_table_id="<?php echo $row["MaDatBan"]; ?>"
                            class="col-6 btn-cancel_order">
                        <i class="fas fa-times me-1"></i>Hủy đơn
                    </button>
                    <button type="submit" name="btn_pay_order" class="col-6 btn-pay_order">
                        <i class="fas fa-credit-card me-1"></i>Thanh toán
                    </button>
                <?php else: ?>
                    <!-- Đã thanh toán: chỉ nút hủy -->
                    <button type="button" data-order_table_id="<?php echo $row["MaDatBan"]; ?>"
                            class="col-12 btn-cancel_order">
                        <i class="fas fa-times me-1"></i>Hủy đơn hàng
                    </button>
                <?php endif; ?>
            <?php elseif ($row["TrangThaiThanhToan"] == "chuathanhtoan"): ?>
                <!-- Không phải da_dat nhưng chưa thanh toán -->
                <button type="submit" name="btn_pay_order" class="col-12 btn-pay_order">
                    <i class="fas fa-credit-card me-1"></i>Thanh toán
                </button>
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
    <p class="text-muted fs-5">Không có đơn đặt bàn nào với trạng thái này.</p>
</div>
<?php endif; ?>