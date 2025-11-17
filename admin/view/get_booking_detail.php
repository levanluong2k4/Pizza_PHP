<?php
session_start();
require __DIR__ . '/../../includes/db_connect.php';

// if (!isset($_SESSION['admin'])) {
//     echo '<div class="alert alert-danger">Không có quyền truy cập</div>';
//     exit;
// }

$madatban = intval($_GET['madatban'] ?? 0);
$loai = $_GET['loai'] ?? 'thuong';

if ($madatban <= 0) {
    echo '<div class="alert alert-danger">Mã đặt bàn không hợp lệ</div>';
    exit;
}

// Lấy thông tin đặt bàn
if ($loai == 'thuong') {
    $sql = "SELECT db.*, ba.SoBan, ba.SoGhe, ba.KhuVuc
            FROM datban db
            INNER JOIN banan ba ON db.MaBan = ba.MaBan
            WHERE db.MaDatBan = ?";
} else {
    $sql = "SELECT db.*, pt.TenPhong, pt.SucChua, 
                   c.Tencombo, c.giamgia, c.Anh as AnhCombo
            FROM datban db
            INNER JOIN phongtiec pt ON db.MaPhong = pt.MaPhong
            LEFT JOIN combo c ON db.MaCombo = c.MaCombo
            WHERE db.MaDatBan = ?";
}

$stmt = $ketnoi->prepare($sql);
$stmt->bind_param("i", $madatban);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo '<div class="alert alert-danger">Không tìm thấy thông tin đặt bàn</div>';
    exit;
}

// Nếu là bàn tiệc và có combo, lấy chi tiết combo
$combo_details = [];
if ($loai == 'tiec' && $booking['MaCombo']) {
    $sql_combo = "SELECT ct.*, sp.TenSP, sp.Anh, s.TenSize, sps.Gia
                  FROM chitietcombo ct
                  INNER JOIN sanpham sp ON ct.MaSP = sp.MaSP
                  INNER JOIN size s ON ct.MaSize = s.MaSize
                  INNER JOIN sanpham_size sps ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize
                  WHERE ct.MaCombo = ?";
    $stmt_combo = $ketnoi->prepare($sql_combo);
    $stmt_combo->bind_param("i", $booking['MaCombo']);
    $stmt_combo->execute();
    $result_combo = $stmt_combo->get_result();
    while ($row = $result_combo->fetch_assoc()) {
        $combo_details[] = $row;
    }
    $stmt_combo->close();
}

// Badge trạng thái
$status_badges = [
    'da_dat' => '<span class="badge bg-warning">Chờ xác nhận</span>',
    'da_xac_nhan' => '<span class="badge bg-info">Đã xác nhận</span>',
    'dang_su_dung' => '<span class="badge bg-success">Đang sử dụng</span>',
    'hoan_thanh' => '<span class="badge bg-secondary">Hoàn thành</span>',
    'da_huy' => '<span class="badge bg-danger">Đã hủy</span>'
];
?>

<div class="booking-detail-content">
    <!-- Thông tin cơ bản -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Thông tin đặt bàn #<?php echo $madatban; ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">
                    <i class="fas fa-user me-2"></i>Họ và tên:
                </span>
                <span class="info-value"><?php echo htmlspecialchars($booking['HoTen']); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">
                    <i class="fas fa-phone me-2"></i>Số điện thoại:
                </span>
                <span class="info-value"><?php echo htmlspecialchars($booking['SDT']); ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">
                    <i class="fas fa-calendar-alt me-2"></i>Ngày giờ đến:
                </span>
                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($booking['NgayGio'])); ?></span>
            </div>
            
            <?php if ($loai == 'thuong'): ?>
            <div class="info-row">
                <span class="info-label">
                    <i class="fas fa-chair me-2"></i>Bàn:
                </span>
                <span class="info-value">
                    Bàn số <?php echo $booking['SoBan']; ?> 
                    (<?php echo $booking['SoGhe']; ?> ghế - <?php echo $booking['KhuVuc']; ?>)
                </span>
            </div>
            <?php else: ?>
            <div class="info-row">
                <span class="info-label">
                    <i class="fas fa-door-open me-2"></i>Phòng:
                </span>
                <span class="info-value">
                    <?php echo $booking['TenPhong']; ?> 
                    (Sức chứa: <?php echo $booking['SucChua']; ?> người)
                </span>
            </div>
            <?php endif; ?>
            
            <div class="info-row">
                <span class="info-label">
                    <i class="fas fa-flag me-2"></i>Trạng thái:
                </span>
                <span class="info-value">
                    <?php echo $status_badges[$booking['TrangThaiDatBan']] ?? $booking['TrangThaiDatBan']; ?>
                </span>
            </div>
            
            <?php if (!empty($booking['GhiChu'])): ?>
            <div class="info-row">
                <span class="info-label">
                    <i class="fas fa-comment me-2"></i>Ghi chú:
                </span>
                <span class="info-value"><?php echo htmlspecialchars($booking['GhiChu']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Thông tin combo (nếu có) -->
    <?php if ($loai == 'tiec' && $booking['MaCombo']): ?>
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">
                <i class="fas fa-box-open me-2"></i>
                Thông tin combo
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <?php if ($booking['AnhCombo']): ?>
                <div class="col-md-4">
                    <img src="../<?php echo $booking['AnhCombo']; ?>" 
                         class="img-fluid rounded" 
                         alt="<?php echo $booking['Tencombo']; ?>">
                </div>
                <?php endif; ?>
                
                <div class="col-md-<?php echo $booking['AnhCombo'] ? '8' : '12'; ?>">
                    <h5><?php echo htmlspecialchars($booking['Tencombo']); ?></h5>
                    
                    <?php if (!empty($combo_details)): 
                        $total_combo = 0;
                        foreach ($combo_details as $item) {
                            $total_combo += $item['Gia'] * $item['SoLuong'];
                        }
                    ?>
                    <div class="mt-3">
                        <h6>Chi tiết sản phẩm:</h6>
                        <?php foreach ($combo_details as $item): ?>
                        <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                            <img src="../<?php echo $item['Anh']; ?>" 
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" 
                                 class="me-3">
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?php echo $item['TenSP']; ?></div>
                                <small class="text-muted">
                                    Size: <?php echo $item['TenSize']; ?> | 
                                    SL: <?php echo $item['SoLuong']; ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <?php echo number_format($item['Gia'] * $item['SoLuong'], 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Tổng giá trị:</strong>
                                <span style="text-decoration: line-through;">
                                    <?php echo number_format($total_combo, 0, ',', '.'); ?> VNĐ
                                </span>
                            </div>
                            <div class="d-flex justify-content-between text-success">
                                <strong>Giá combo (Giảm <?php echo $booking['giamgia']; ?>%):</strong>
                                <strong><?php echo number_format($total_combo - ($total_combo * $booking['giamgia'] / 100), 0, ',', '.'); ?> VNĐ</strong>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($booking['TienCoc'] > 0): ?>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        <strong>Tiền cọc:</strong> 
                        <?php echo number_format($booking['TienCoc'], 0, ',', '.'); ?> VNĐ
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Nút hành động -->
    <div class="action-buttons">
        <?php if ($booking['TrangThaiDatBan'] == 'da_dat'): ?>
        <button class="btn btn-success" onclick="updateStatus(<?php echo $madatban; ?>, 'da_xac_nhan')">
            <i class="fas fa-check me-2"></i>
            Xác nhận đặt bàn
        </button>
        <?php endif; ?>
        
        <?php if ($booking['TrangThaiDatBan'] == 'da_xac_nhan'): ?>
        <button class="btn btn-primary" onclick="updateStatus(<?php echo $madatban; ?>, 'dang_su_dung')">
            <i class="fas fa-play me-2"></i>
            Khách đã đến
        </button>
        <?php endif; ?>
        
        <?php if ($booking['TrangThaiDatBan'] == 'dang_su_dung'): ?>
        <button class="btn btn-secondary" onclick="updateStatus(<?php echo $madatban; ?>, 'hoan_thanh')">
            <i class="fas fa-check-circle me-2"></i>
            Hoàn thành
        </button>
        <?php endif; ?>
        
        <?php if (in_array($booking['TrangThaiDatBan'], ['da_dat', 'da_xac_nhan'])): ?>
        <button class="btn btn-danger" onclick="cancelBooking(<?php echo $madatban; ?>)">
            <i class="fas fa-times me-2"></i>
            Hủy đặt bàn
        </button>
        <?php endif; ?>
        
        <a href="edit_booking.php?id=<?php echo $madatban; ?>" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>
            Chỉnh sửa
        </a>
        
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>
            Đóng
        </button>
    </div>
</div>

<style>
.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #666;
}

.info-value {
    color: #333;
    text-align: right;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 20px;
}
</style>