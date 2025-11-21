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

// if (!$booking) {
//     $_SESSION['error'] = "Không tìm thấy thông tin đặt bàn";
//     header('Location: index.php');
//     exit;
// }

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
    <link rel="stylesheet" href="../css/pizza.css">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/sign_up.css">
    <link rel="stylesheet" href="../css/datban.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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
         <?php if ($booking['TrangThaiThanhToan'] == 'chuathanhtoan'): ?>
    
<!-- Thêm thông báo đếm ngược -->
<div class="alert alert-warning mt-3" id="countdown-alert">
    <i class="fas fa-clock me-2"></i>
    <strong>Lưu ý:</strong> Vui lòng thanh toán trong vòng 
    <span id="countdown-timer" class="fw-bold text-danger"></span>
    <br>
    <small>Sau thời gian này, đơn đặt bàn sẽ tự động bị hủy.</small>
</div>

<style>
#countdown-alert {
    border-left: 4px solid #ff6b6b;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}
</style>

<script>
// Tính thời gian còn lại từ NgayTao
const createdTime = new Date('<?php echo $booking['NgayTao']; ?>').getTime();
const expiryTime = createdTime + (5 * 60 * 1000); // Thêm 5 phút

function updateCountdown() {
    const now = new Date().getTime();
    const timeLeft = expiryTime - now;
    
    if (timeLeft <= 0) {
        document.getElementById('countdown-timer').innerHTML = 'HẾT THỜI GIAN';
        document.getElementById('countdown-alert').classList.remove('alert-warning');
        document.getElementById('countdown-alert').classList.add('alert-danger');
        
        // Gọi AJAX để xóa đơn
        $.ajax({
            url: 'auto_cancel_booking.php',
            method: 'POST',
            data: {
                booking_id: <?php echo $madatban; ?>,
                action: 'cancel_unpaid'
            },
            success: function() {
                alert('Đơn đặt bàn đã bị hủy do không thanh toán đúng hạn.');
                window.location.href = '../index.php';
            }
        });
        
        return;
    }
    
    const minutes = Math.floor(timeLeft / 60000);
    const seconds = Math.floor((timeLeft % 60000) / 1000);
    
    document.getElementById('countdown-timer').innerHTML = 
        minutes + ' phút ' + (seconds < 10 ? '0' : '') + seconds + ' giây';
    
    // Đổi màu khi còn dưới 1 phút
    if (minutes < 1) {
        document.getElementById('countdown-timer').style.color = '#ff0000';
        document.getElementById('countdown-timer').style.fontSize = '1.2em';
    }
}

// Cập nhật mỗi giây
setInterval(updateCountdown, 1000);
updateCountdown();
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
            <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($booking['NgayTao'])); ?></span>
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
                <i class="fas fa-calendar-alt me-2"></i>Ngày giờ đến:
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
                <i class="fas fa-tags me-2"></i>Giá combo:
            </span>
            <span class="info-value text-success fw-bold">
                <?php echo number_format($booking['GiaCombo'], 0, ',', '.'); ?> VNĐ
            </span>
        </div>
           <div class="info-row">
            <span class="info-label">
                <i class="fas fa-tags me-2"></i>Trạng thái thanh toán
            </span>
            <span class="info-value text-success fw-bold">
                <?php echo $booking['TrangThaiThanhToan'] ?>
            </span>
        </div>
        
        <?php if ($booking['TienCoc'] > 0): ?>
        <div class="info-row">
            <span class="info-label">
                <i class="fas fa-money-bill-wave me-2"></i>Tiền cọc:
            </span>
            <span class="info-value text-warning fw-bold">
                <?php echo number_format($booking['TienCoc'], 0, ',', '.'); ?> VNĐ
            </span>
        </div>
        <?php endif; ?>
        
        <!-- Hiển thị chi tiết combo -->
        <?php if (!empty($combo_details)): ?>
        <div class="combo-items">
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
                <i class="fas fa-hourglass-half me-2"></i>Trạng thái:
            </span>
            <span class="info-value">
                <span class="status-badge status-pending">Chờ xác nhận</span>
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
    
    <div class="note-section">
        <h6 class="mb-3">
            <i class="fas fa-bell me-2"></i>
            Lưu ý quan trọng:
        </h6>
        <ul class="mb-0">
            <li>Quý khách vui lòng đến đúng giờ đã đặt</li>
            <li>Nhân viên sẽ liên hệ để xác nhận đơn đặt bàn trong thời gian sớm nhất</li>
            <li>Nếu cần thay đổi hoặc hủy đặt bàn, vui lòng liên hệ hotline: <strong>1900 xxxx</strong></li>
            <?php if ($booking['LoaiDatBan'] == 'tiec'): ?>
            <li>Đối với bàn tiệc, quý khách vui lòng thanh toán tiền cọc để giữ phòng</li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div class="action-buttons">
        <a href="index.php" class="btn btn-success btn-action">
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