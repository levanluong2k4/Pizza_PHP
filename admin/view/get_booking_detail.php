<?php

require_once __DIR__ . '/../../includes/db_connect.php';

$selected_month = $_GET['month'] ?? date('Y-m');
$month_start = $selected_month . '-01';
$month_end = date('Y-m-t', strtotime($month_start));

// Lấy thống kê đơn đặt theo ngày trong tháng
$sql_calendar = "SELECT 
    DATE(NgayGio) as ngay,
    COUNT(*) as so_don,
    SUM(CASE WHEN MaBan IS NOT NULL THEN 1 ELSE 0 END) as so_ban,
    SUM(CASE WHEN MaPhong IS NOT NULL THEN 1 ELSE 0 END) as so_phong
FROM datban
WHERE DATE(NgayGio) BETWEEN ? AND ?
    AND TrangThaiDatBan IN ('da_dat', 'da_xac_nhan', 'dang_su_dung')
GROUP BY DATE(NgayGio)";

$stmt_calendar = $ketnoi->prepare($sql_calendar);
$stmt_calendar->bind_param("ss", $month_start, $month_end);
$stmt_calendar->execute();
$result_calendar = $stmt_calendar->get_result();
$booking_stats = [];
while ($row = $result_calendar->fetch_assoc()) {
    $booking_stats[$row['ngay']] = $row;
}
$stmt_calendar->close();

// Hàm tạo lịch
function generateCalendar($month, $booking_stats, $selected_date) {
    $first_day = strtotime($month . '-01');
    $days_in_month = date('t', $first_day);
    $first_weekday = date('w', $first_day); // 0 = Chủ nhật
    
    $calendar = '<div class="calendar-grid">';
    
    // Header ngày trong tuần
    $weekdays = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
    foreach ($weekdays as $day) {
        $calendar .= '<div class="calendar-header">' . $day . '</div>';
    }
    
    // Ô trống cho các ngày trước ngày 1
    for ($i = 0; $i < $first_weekday; $i++) {
        $calendar .= '<div class="calendar-day empty"></div>';
    }
    
    // Các ngày trong tháng
    for ($day = 1; $day <= $days_in_month; $day++) {
        $current_date = date('Y-m-d', strtotime($month . '-' . sprintf('%02d', $day)));
        $is_today = ($current_date == date('Y-m-d'));
        $is_selected = ($current_date == $selected_date);
        $has_booking = isset($booking_stats[$current_date]);
        
        $class = 'calendar-day';
        if ($is_today) $class .= ' today';
        if ($is_selected) $class .= ' selected';
        if ($has_booking) $class .= ' has-booking';
        
        $calendar .= '<a href="?date=' . $current_date . '&month=' . $month . '" class="' . $class . '">';
        $calendar .= '<div class="day-number">' . $day . '</div>';
        
        if ($has_booking) {
            $stats = $booking_stats[$current_date];
            $calendar .= '<div class="booking-badge">';
            $calendar .= '<span class="total">' . $stats['so_don'] . '</span>';
            if ($stats['so_ban'] > 0) {
                $calendar .= '<span class="detail">🪑' . $stats['so_ban'] . '</span>';
            }
            if ($stats['so_phong'] > 0) {
                $calendar .= '<span class="detail">🚪' . $stats['so_phong'] . '</span>';
            }
            $calendar .= '</div>';
        }
        
        $calendar .= '</a>';
    }
    
    $calendar .= '</div>';
    return $calendar;
}



$madatban = intval($_GET['madatban'] ?? 0);
$loai = $_GET['loai'] ?? 'thuong';

if ($madatban <= 0) {
    echo '<div class="alert alert-danger">Mã đặt bàn không hợp lệ</div>';
    exit;
}

// Lấy thông tin đặt bàn
$sql = "SELECT db.*, 
        ba.SoBan, ba.SoGhe, ba.KhuVuc,
        pt.TenPhong, pt.SucChua,
        c.Tencombo, c.giamgia
        FROM datban db
        LEFT JOIN banan ba ON db.MaBan = ba.MaBan
        LEFT JOIN phongtiec pt ON db.MaPhong = pt.MaPhong
        LEFT JOIN combo c ON db.MaCombo = c.MaCombo
        WHERE db.MaDatBan = ?";

$stmt = $ketnoi->prepare($sql);
$stmt->bind_param("i", $madatban);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo '<div class="alert alert-danger">Không tìm thấy đơn đặt bàn</div>';
    exit;
}

// Lấy chi tiết sản phẩm nếu là tiệc
$chitiet_list = [];
$tongtien_sanpham = 0;

if ($loai == 'tiec' && $booking['MaPhong']) {
    $sql_chitiet = "SELECT ct.*, sp.TenSP, s.TenSize
                    FROM chitietdatban ct
                    INNER JOIN sanpham sp ON sp.MaSP = ct.MaSP
                    INNER JOIN size s ON ct.MaSize = s.MaSize
                    WHERE ct.MaDatBan = ?";
    $stmt = $ketnoi->prepare($sql_chitiet);
    $stmt->bind_param("i", $madatban);
    $stmt->execute();
    $chitiet_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    foreach ($chitiet_list as $item) {
        $tongtien_sanpham += $item['ThanhTien'];
    }
}

// Tính giảm giá
$giamgia_amount = 0;
if ($booking['giamgia'] && $tongtien_sanpham > 0) {
    $giamgia_amount = ($tongtien_sanpham * $booking['giamgia']) / 100;
}
$tongtien_cuoi = $tongtien_sanpham - $giamgia_amount;

// Mapping trạng thái
$trangThai = [
    'da_dat' => ['text' => 'Đã đặt', 'class' => 'warning', 'icon' => 'clock'],
    'da_xac_nhan' => ['text' => 'Đã xác nhận', 'class' => 'info', 'icon' => 'check-circle'],
    'dang_su_dung' => ['text' => 'Đang sử dụng', 'class' => 'primary', 'icon' => 'concierge-bell'],
    'hoan_thanh' => ['text' => 'Hoàn thành', 'class' => 'success', 'icon' => 'check-double'],
    'da_huy' => ['text' => 'Đã hủy', 'class' => 'danger', 'icon' => 'times-circle']
];

$status = $trangThai[$booking['TrangThaiDatBan']] ?? ['text' => 'Không xác định', 'class' => 'secondary', 'icon' => 'question'];
?>



<!-- Thông tin đặt bàn -->
<div class="info-section">
    <h6><i class="fas fa-info-circle me-2"></i>Thông tin chung</h6>
    
    <div class="info-row">
        <span class="info-label">Mã đặt bàn:</span>
        <span class="info-value"><strong>#<?php echo $madatban; ?></strong></span>
    </div>
    
    <div class="info-row">
        <span class="info-label">Loại:</span>
        <span class="info-value">
            <strong><?php echo $loai == 'tiec' ? 'Bàn tiệc' : 'Bàn thường'; ?></strong>
        </span>
    </div>
    
    <div class="info-row">
        <span class="info-label">Trạng thái:</span>
        <span class="info-value">
            <span class="badge bg-<?php echo $status['class']; ?>">
                <i class="fas fa-<?php echo $status['icon']; ?> me-1"></i>
                <?php echo $status['text']; ?>
            </span>
        </span>
    </div>
    
    <?php if ($booking['MaBan']): ?>
    <div class="info-row">
        <span class="info-label">Bàn số:</span>
        <span class="info-value">
            <strong>Bàn <?php echo $booking['SoBan']; ?></strong>
            (<?php echo $booking['SoGhe']; ?> ghế - <?php echo $booking['KhuVuc']; ?>)
        </span>
    </div>
    <?php endif; ?>
    
    <?php if ($booking['MaPhong']): ?>
    <div class="info-row">
        <span class="info-label">Phòng:</span>
        <span class="info-value">
            <strong><?php echo $booking['TenPhong']; ?></strong>
            (Sức chứa: <?php echo $booking['SucChua']; ?> người)
        </span>
    </div>
    <?php endif; ?>
</div>

<!-- Thông tin khách hàng -->
<div class="info-section">
    <h6><i class="fas fa-user me-2"></i>Thông tin khách hàng</h6>
    
    <div class="info-row">
        <span class="info-label">Họ tên:</span>
        <span class="info-value"><strong><?php echo htmlspecialchars($booking['HoTen']); ?></strong></span>
    </div>
    
    <div class="info-row">
        <span class="info-label">Số điện thoại:</span>
        <span class="info-value">
            <a href="tel:<?php echo $booking['SDT']; ?>"><?php echo $booking['SDT']; ?></a>
        </span>
    </div>
    
    <div class="info-row">
        <span class="info-label">Ngày giờ đến:</span>
        <span class="info-value">
            <strong><?php echo date('d/m/Y - H:i', strtotime($booking['NgayGio'])); ?></strong>
        </span>
    </div>
    
    <?php if ($booking['GhiChu']): ?>
    <div class="info-row">
        <span class="info-label">Ghi chú:</span>
        <span class="info-value"><?php echo nl2br(htmlspecialchars($booking['GhiChu'])); ?></span>
    </div>
    <?php endif; ?>
</div>

<!-- Chi tiết combo và sản phẩm (nếu là tiệc) -->

<div class="info-section">
    <h6><i class="fas fa-utensils me-2"></i>Chi tiết đơn hàng</h6>
    
    <?php if ($booking['Tencombo']): ?>
    <div class="info-row">
        <span class="info-label">Combo:</span>
        <span class="info-value">
            <strong><?php echo htmlspecialchars($booking['Tencombo']); ?></strong>
            <span class="badge bg-success ms-2">-<?php echo $booking['giamgia']; ?>%</span>
        </span>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($chitiet_list)): ?>
    <table class="products-table table table-hover">
        <thead>
            <tr>
                <th>STT</th>
                <th>Sản phẩm</th>
                <th>Size</th>
                <th>SL</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($chitiet_list as $index => $item): ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td><strong><?php echo htmlspecialchars($item['TenSP']); ?></strong></td>
                <td><?php echo htmlspecialchars($item['TenSize']); ?></td>
                <td><?php echo $item['SoLuong']; ?></td>
                <td><strong><?php echo number_format($item['ThanhTien']); ?> đ</strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="total-box">
        <div class="total-row">
            <span>Tổng tiền sản phẩm:</span>
            <strong><?php echo number_format($tongtien_sanpham); ?> đ</strong>
        </div>
        
        <?php if ($giamgia_amount > 0): ?>
        <div class="total-row">
            <span>Giảm giá (<?php echo $booking['giamgia']; ?>%):</span>
            <strong>-<?php echo number_format($giamgia_amount); ?> đ</strong>
        </div>
        <?php endif; ?>
        
     
        
        <div class="total-row main">
            <span>Tổng thanh toán:</span>
            <strong><?php echo number_format($tongtien_cuoi); ?> đ</strong>
        </div>
        
      
    </div>
    <?php endif; ?>
</div>


<!-- Nút hành động -->
<div class="action-buttons">
    <a href="edit_booking.php?id=<?php echo $madatban; ?>" class="btn btn-primary">
        <i class="fas fa-edit me-2"></i>Chỉnh sửa
    </a>
    
    <?php if ($booking['TrangThaiDatBan'] == 'da_dat'): ?>
    <button onclick="updateStatus(<?php echo $madatban; ?>, 'da_xac_nhan')" class="btn btn-success">
        <i class="fas fa-check me-2"></i>Xác nhận
    </button>
    <?php endif; ?>
    
    <?php if ($booking['TrangThaiDatBan'] == 'da_xac_nhan'): ?>
    <button onclick="updateStatus(<?php echo $madatban; ?>, 'dang_su_dung')" class="btn btn-info">
        <i class="fas fa-concierge-bell me-2"></i>Bắt đầu phục vụ
    </button>
    <?php endif; ?>
    
    <?php if ($booking['TrangThaiDatBan'] == 'dang_su_dung'): ?>
    <button onclick="updateStatus(<?php echo $madatban; ?>, 'thanh_cong')" class="btn btn-success">
        <i class="fas fa-check-double me-2"></i>Hoàn thành
    </button>
    <?php endif; ?>
    
    <?php if (in_array($booking['TrangThaiDatBan'], ['da_dat', 'da_xac_nhan'])): ?>
    <button onclick="cancelBooking(<?php echo $madatban; ?>)" class="btn btn-danger">
        <i class="fas fa-times me-2"></i>Hủy đặt
    </button>
    <?php endif; ?>
</div>