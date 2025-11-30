<?php
session_start();
require '../includes/db_connect.php';

$madatban = intval($_GET['id'] ?? 0);
$resultCode = intval($_GET['resultCode'] ?? 0);
$vnp_TransactionStatus = intval($_GET['vnp_TransactionStatus'] ?? 0);
$phuongthuc=$_GET['thanhtoan'] ?? '';

if(!isset($_GET['id'])){
    header('Location: ../trangchu.php');
    exit;
}

if($phuongthuc=='momo' ){
    if($resultCode!=0){
        $sql_update="UPDATE `datban` SET `TrangThaiThanhToan`='chuathanhtoan' WHERE MaDatBan=$madatban";
        mysqli_query($ketnoi,$sql_update);
    }
}
if($phuongthuc=='vnpay'){
    if($vnp_TransactionStatus!=0){
        $sql_update="UPDATE `datban` SET `TrangThaiThanhToan`='chuathanhtoan' WHERE MaDatBan=$madatban";
        mysqli_query($ketnoi,$sql_update);
    }
}

// Lấy thông tin đặt bàn
$sql = "SELECT db.*, 
        ba.SoBan, ba.KhuVuc,
        pt.TenPhong, pt.SucChua,
        c.Tencombo, c.giamgia as GiaCombo, c.Anh as AnhCombo
        FROM datban db
        LEFT JOIN banan ba ON db.MaBan = ba.MaBan
        LEFT JOIN phongtiec pt ON db.MaPhong = pt.MaPhong
        LEFT JOIN combo c ON db.MaCombo = c.MaCombo
        WHERE db.MaDatBan = ?";

$stmt = $ketnoi->prepare($sql);
$stmt->bind_param("i", $madatban);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

// Nếu là đặt tiệc, lấy chi tiết combo
$combo_details = [];
if ($booking['LoaiDatBan'] == 'tiec' && $booking['MaCombo']) {
    $sql_detail = "SELECT ct.*, sp.TenSP, sp.Anh, s.TenSize, sps.Gia
                   FROM chitietcombo ct
                   INNER JOIN sanpham sp ON ct.MaSP = sp.MaSP
                   INNER JOIN size s ON ct.MaSize = s.MaSize
                   INNER JOIN sanpham_size sps ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize
                   WHERE ct.MaCombo = ?";
    $stmt_detail = $ketnoi->prepare($sql_detail);
    $stmt_detail->bind_param("i", $booking['MaCombo']);
    $stmt_detail->execute();
    $result_detail = $stmt_detail->get_result();
    while ($row = $result_detail->fetch_assoc()) {
        $combo_details[] = $row;
    }
    $stmt_detail->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Pizza - Đặt bàn thành công</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/pizza.css">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/sign_up.css">
    <link rel="stylesheet" href="../css/datban.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>

<body>

    <header class="bg-icon pt-2">
        <?php include '../components/navbar.php'; ?>
    </header>

<div class="success-container">
    <div class="success-header">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Đặt bàn thành công!</h2>
        <div class="booking-code">#<?php echo $madatban; ?></div>
        <p class="mb-0">Vui lòng lưu lại mã đặt bàn để tra cứu</p>
        
        <?php if ($booking['TrangThaiThanhToan'] == 'chuathanhtoan' && $booking['LoaiDatBan'] == 'tiec'): ?>
        <!-- Thông báo đếm ngược -->
        <div class="alert alert-warning mt-3" id="countdown-alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Cảnh báo:</strong> Vui lòng thanh toán trong vòng 
            <span id="countdown-timer" class="fw-bold text-danger"></span>
            <br>
            <small>Hệ thống sẽ tự động hủy đơn nếu không thanh toán đúng hạn.</small>
            <br>
            <small class="text-muted">
                <i class="fas fa-clock me-1"></i>
                Thời gian tạo: <?php echo date('d/m/Y H:i:s', strtotime($booking['NgayTao'])); ?>
            </small>
        </div>

        <style>
        #countdown-alert {
            border-left: 4px solid #ff6b6b;
            animation: pulse 2s infinite;
            background: linear-gradient(135deg, #fff3cd 0%, #ffe4a8 100%);
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.85; }
        }

        #countdown-timer {
            font-size: 1.3em;
            padding: 2px 8px;
            background: rgba(255, 0, 0, 0.1);
            border-radius: 4px;
        }

        .expired-alert {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c2c7 100%) !important;
            border-left: 4px solid #dc3545 !important;
        }
        </style>

        <script>
        // Tính thời gian còn lại từ NgayTao
        const createdTime = new Date('<?php echo $booking['NgayTao']; ?>').getTime();
        const expiryTime = createdTime + (5 * 60 * 1000); // 5 phút

        function updateCountdown() {
            const now = new Date().getTime();
            const timeLeft = expiryTime - now;
            
            if (timeLeft <= 0) {
                // HẾT THỜI GIAN - Chỉ thông báo, MySQL Event sẽ tự động hủy
                document.getElementById('countdown-timer').innerHTML = 'ĐÃ HẾT HẠN';
                document.getElementById('countdown-alert').classList.remove('alert-warning');
                document.getElementById('countdown-alert').classList.add('alert-danger', 'expired-alert');
                
                // Hiển thị thông báo
                alert('⚠️ Đơn đặt bàn đã hết hạn thanh toán!\n\nHệ thống sẽ tự động hủy đơn này.\nVui lòng đặt bàn lại nếu cần.');
                
                // Chuyển về trang chủ
                window.location.href = '../trangchu.php';
                return;
            }
            
            // Tính phút và giây
            const minutes = Math.floor(timeLeft / 60000);
            const seconds = Math.floor((timeLeft % 60000) / 1000);
            
            // Hiển thị countdown
            document.getElementById('countdown-timer').innerHTML = 
                minutes + ' phút ' + (seconds < 10 ? '0' : '') + seconds + ' giây';
            
            // Đổi màu khi còn dưới 1 phút
            if (minutes < 1) {
                document.getElementById('countdown-timer').style.color = '#dc3545';
                document.getElementById('countdown-timer').style.fontSize = '1.4em';
                document.getElementById('countdown-timer').style.fontWeight = 'bold';
                
                // Thêm hiệu ứng nhấp nháy
                if (seconds % 2 === 0) {
                    document.getElementById('countdown-alert').style.borderColor = '#dc3545';
                } else {
                    document.getElementById('countdown-alert').style.borderColor = '#ff6b6b';
                }
            }
            
            // Cảnh báo khi còn 1 phút
            if (minutes === 0 && seconds === 59) {
                if (confirm('⏰ Chỉ còn 1 phút để thanh toán!\n\nBạn có muốn tiếp tục thanh toán không?')) {
                    // Có thể thêm logic chuyển đến trang thanh toán
                    console.log('User muốn thanh toán');
                }
            }
        }

        // Cập nhật mỗi giây
        const countdownInterval = setInterval(updateCountdown, 1000);
        updateCountdown(); // Chạy ngay lập tức

        // Dọn dẹp khi rời trang
        window.addEventListener('beforeunload', function() {
            clearInterval(countdownInterval);
        });
        </script>
        <?php endif; ?>
    </div>
    
    <div class="info-section">
        <h5 class="mb-4">
            <i class="fas fa-info-circle me-2"></i>
            Thông tin đặt bàn
        </h5>

        <div class="info-row">
            <span class="info-label">
                <i class="fas fa-calendar-alt me-2"></i>Ngày tạo:
            </span>
            <span class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($booking['NgayTao'])); ?></span>
        </div>
        
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
                <i class="fas fa-calendar-check me-2"></i>Ngày giờ đến:
            </span>
            <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($booking['NgayGio'])); ?></span>
        </div>
        
        <div class="info-row">
            <span class="info-label">
                <i class="fas fa-<?php echo $booking['LoaiDatBan'] == 'tiec' ? 'door-open' : 'chair'; ?> me-2"></i>
                <?php echo $booking['LoaiDatBan'] == 'tiec' ? 'Phòng tiệc:' : 'Bàn:'; ?>
            </span>
            <span class="info-value">
                <?php 
                if ($booking['LoaiDatBan'] == 'tiec') {
                    echo $booking['TenPhong'] . ' (Sức chứa: ' . $booking['SucChua'] . ' người)';
                } else {
                    echo 'Bàn số ' . $booking['SoBan'] . ' - ' . $booking['KhuVuc'];
                }
                ?>
            </span>
        </div>
        
        <?php if ($booking['LoaiDatBan'] == 'tiec' && $booking['MaCombo']): ?>
        <div class="info-row">
            <span class="info-label">
                <i class="fas fa-box-open me-2"></i>Combo:
            </span>
            <span class="info-value"><?php echo htmlspecialchars($booking['Tencombo']); ?></span>
        </div>
        
        <div class="info-row">
            <span class="info-label">
                <i class="fas fa-money-bill-wave me-2"></i>Tổng tiền:
            </span>
            <span class="info-value text-success fw-bold">
                <?php echo number_format($booking['Tongtien'], 0, ',', '.'); ?> VNĐ
            </span>
        </div>
        
        <div class="info-row">
            <span class="info-label">
                <i class="fas fa-credit-card me-2"></i>Trạng thái thanh toán:
            </span>
            <span class="info-value">
                <span class="badge <?php echo $booking['TrangThaiThanhToan'] == 'chuathanhtoan' ? 'bg-warning text-dark' : 'bg-success'; ?>">
                    <?php echo $booking['TrangThaiThanhToan'] == 'chuathanhtoan' ? 'Chưa thanh toán' : 'Đã thanh toán'; ?>
                </span>
            </span>
        </div>
        
        <!-- Hiển thị chi tiết combo -->
        <?php if (!empty($combo_details)): ?>
        <div class="combo-items mt-3">
            <h6 class="mb-3">
                <i class="fas fa-list me-2"></i>
                Sản phẩm trong combo:
            </h6>
            <?php foreach ($combo_details as $item): ?>
            <div class="combo-item">
                <img src="../<?php echo $item['Anh']; ?>" alt="<?php echo $item['TenSP']; ?>">
                <div class="flex-grow-1">
                    <div class="fw-bold"><?php echo $item['TenSP']; ?></div>
                    <small class="text-muted">
                        Size: <?php echo $item['TenSize']; ?> | 
                        Số lượng: <?php echo $item['SoLuong']; ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
        <div class="info-row">
            <span class="info-label">
                <i class="fas fa-hourglass-half me-2"></i>Trạng thái đơn:
            </span>
            <span class="info-value">
                <span class="status-badge status-pending">Chờ xác nhận</span>
            </span>
        </div>
        
        <?php if (!empty($booking['GhiChu'])): ?>
        <div class="info-row">
            <span class="info-label">
                <i class="fas fa-comment-alt me-2"></i>Ghi chú: 
            </span>
            <span class="info-value"><?php echo htmlspecialchars($booking['GhiChu']); ?></span>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="note-section">
        <h6 class="mb-3">
            <i class="fas fa-bell me-2"></i>
            Lưu ý quan trọng:
        </h6>
        <ul class="mb-0">
            <li>Quý khách vui lòng đến đúng giờ đã đặt</li>
            <li>Nhân viên sẽ liên hệ để xác nhận đơn đặt bàn trong thời gian sớm nhất</li>
            <li>Nếu cần thay đổi hoặc hủy đặt bàn, vui lòng liên hệ hotline: <strong>1900 xxxx</strong></li>
            <?php if ($booking['LoaiDatBan'] == 'tiec' && $booking['TrangThaiThanhToan'] == 'chuathanhtoan'): ?>
            <li class="text-danger fw-bold">
                <i class="fas fa-exclamation-circle me-1"></i>
                Đơn đặt tiệc cần thanh toán trong vòng 5 phút để giữ chỗ
            </li>
            <?php elseif ($booking['LoaiDatBan'] == 'tiec'): ?>
            <li>Cảm ơn quý khách đã thanh toán. Chúng tôi sẽ chuẩn bị tốt nhất cho tiệc của bạn!</li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div class="action-buttons">
        <a href="../trangchu.php" class="btn btn-success btn-action">
            <i class="fas fa-home me-2"></i>
            Về trang chủ
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary btn-action">
            <i class="fas fa-print me-2"></i>
            In phiếu đặt bàn
        </button>
        <a href="tra-cuu-dat-ban.php" class="btn btn-outline-secondary btn-action">
            <i class="fas fa-search me-2"></i>
            Tra cứu đặt bàn
        </a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>