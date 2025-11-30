<?php
session_start();
require __DIR__ . '/../../includes/db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /unitop/backend/lesson/school/project_pizza/sign_in.php');
    exit;
}

// Lấy ngày hiện tại hoặc ngày được chọn
$selected_date = $_GET['date'] ?? date('Y-m-d');

// Lấy danh sách tất cả bàn với trạng thái đặt
$sql_ban = "SELECT 
    ba.*,
    db.MaDatBan,
    db.HoTen,
    db.SDT,
    db.NgayGio,
    db.GhiChu,
    db.TrangThaiDatBan
FROM banan ba
LEFT JOIN datban db ON ba.MaBan = db.MaBan 
    AND DATE(db.NgayGio) = ?
    AND db.TrangThaiDatBan IN ('da_dat', 'da_xac_nhan', 'dang_su_dung')
ORDER BY ba.SoBan";

$stmt_ban = $ketnoi->prepare($sql_ban);
$stmt_ban->bind_param("s", $selected_date);
$stmt_ban->execute();
$result_ban = $stmt_ban->get_result();
$ban_list = [];
while ($row = $result_ban->fetch_assoc()) {
    $ban_list[] = $row;
}
$stmt_ban->close();

// Lấy danh sách tất cả phòng với trạng thái đặt
$sql_phong = "SELECT 
    pt.*,
    db.MaDatBan,
    db.HoTen,
    db.SDT,
    db.NgayGio,
    db.MaCombo,
 
    db.GhiChu,
    db.TrangThaiDatBan,
    c.Tencombo,
    c.giamgia
FROM phongtiec pt
LEFT JOIN datban db ON pt.MaPhong = db.MaPhong 
    AND DATE(db.NgayGio) = ?
    AND db.TrangThaiDatBan IN ('da_dat', 'da_xac_nhan', 'dang_su_dung')
LEFT JOIN combo c ON db.MaCombo = c.MaCombo
ORDER BY pt.SoPhong";

$stmt_phong = $ketnoi->prepare($sql_phong);
$stmt_phong->bind_param("s", $selected_date);
$stmt_phong->execute();
$result_phong = $stmt_phong->get_result();
$phong_list = [];
while ($row = $result_phong->fetch_assoc()) {
    $phong_list[] = $row;
}
$stmt_phong->close();

// Thống kê
$total_ban_dat = 0;
$total_phong_dat = 0;
foreach ($ban_list as $ban) {
    if ($ban['MaDatBan']) $total_ban_dat++;
}
foreach ($phong_list as $phong) {
    if ($phong['MaDatBan']) $total_phong_dat++;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đặt bàn - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
   <link rel="stylesheet" href="../css/datban.css">
</head>
<body>

 <?php include __DIR__ . '/../navbar_admin.php'; ?>

<div class="header-section">
    <div class="container">
        <h1>
            <i class="fas fa-clipboard-list me-3"></i>
            Quản lý đặt bàn
        </h1>
        <p class="mb-0">Theo dõi và quản lý tình trạng đặt bàn</p>
    </div>
</div>

<div class="container">
    <!-- Date selector -->
    <div class="date-selector">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-day me-2"></i>
                    Chọn ngày xem
                </h5>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <input type="date" class="form-control" name="date" value="<?php echo $selected_date; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="?date=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary">
                        Hôm nay
                    </a>
                </form>
            </div>
        </div>
        
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #28a745;"></div>
                <span>Trống</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #ffc107;"></div>
                <span>Đã đặt</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #dc3545;"></div>
                <span>Đang sử dụng</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #6c757d;"></div>
                <span>Bảo trì</span>
            </div>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stats-card">
                <i class="fas fa-chair text-primary" style="font-size: 48px;"></i>
                <h3><?php echo $total_ban_dat; ?>/<?php echo count($ban_list); ?></h3>
                <p class="text-muted mb-0">Bàn thường đã đặt</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <i class="fas fa-door-open text-success" style="font-size: 48px;"></i>
                <h3><?php echo $total_phong_dat; ?>/<?php echo count($phong_list); ?></h3>
                <p class="text-muted mb-0">Phòng tiệc đã đặt</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <i class="fas fa-calendar-check text-warning" style="font-size: 48px;"></i>
                <h3><?php echo $total_ban_dat + $total_phong_dat; ?></h3>
                <p class="text-muted mb-0">Tổng đơn đặt</p>
            </div>
        </div>
    </div>
    
    <!-- Bàn thường -->
    <div class="section-title">
        <h4>
            <i class="fas fa-chair me-2"></i>
            Bàn thường (<?php echo date('d/m/Y', strtotime($selected_date)); ?>)
        </h4>
    </div>
    
    <div class="tables-grid">
        <?php foreach ($ban_list as $ban): 
            $status_class = 'available';
            $status_text = 'Trống';
            
            if ($ban['TrangThai'] == 'bao_tri') {
                $status_class = 'maintenance';
                $status_text = 'Bảo trì';
            } elseif ($ban['MaDatBan']) {
                if ($ban['TrangThaiDatBan'] == 'dang_su_dung') {
                    $status_class = 'in-use';
                    $status_text = 'Đang dùng';
                } else {
                    $status_class = 'booked';
                    $status_text = 'Đã đặt';
                }
            }
        ?>
        <div class="table-card <?php echo $status_class; ?>" 
             <?php if ($ban['MaDatBan']): ?>
             onclick="showBookingDetail(<?php echo $ban['MaDatBan']; ?>, 'thuong')"
             <?php endif; ?>>
            <div class="table-icon">
                <i class="fas fa-chair"></i>
            </div>
            <div class="table-number">Bàn <?php echo $ban['SoBan']; ?></div>
            <div class="table-info">
                <i class="fas fa-users me-1"></i>
                <?php echo $ban['SoGhe']; ?> ghế
            </div>
            <div class="table-info"><?php echo $ban['KhuVuc']; ?></div>
            
            <?php if ($ban['MaDatBan']): ?>
                <div class="booking-time">
                    <i class="fas fa-clock me-1"></i>
                    <?php echo date('H:i', strtotime($ban['NgayGio'])); ?>
                </div>
                <div class="customer-name">
                    <i class="fas fa-user me-1"></i>
                    <?php echo htmlspecialchars($ban['HoTen']); ?>
                </div>
            <?php endif; ?>
            
            <span class="status-badge"><?php echo $status_text; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Phòng tiệc -->
    <div class="section-title">
        <h4>
            <i class="fas fa-door-open me-2"></i>
            Phòng tiệc (<?php echo date('d/m/Y', strtotime($selected_date)); ?>)
        </h4>
    </div>
    
    <div class="tables-grid">
        <?php foreach ($phong_list as $phong): 
            $status_class = 'available';
            $status_text = 'Trống';
            
            if ($phong['TrangThai'] == 'bao_tri') {
                $status_class = 'maintenance';
                $status_text = 'Bảo trì';
            } elseif ($phong['MaDatBan']) {
                if ($phong['TrangThaiDatBan'] == 'dang_su_dung') {
                    $status_class = 'in-use';
                    $status_text = 'Đang dùng';
                } else {
                    $status_class = 'booked';
                    $status_text = 'Đã đặt';
                }
            }
        ?>
        <div class="table-card <?php echo $status_class; ?>" 
             <?php if ($phong['MaDatBan']): ?>
             onclick="showBookingDetail(<?php echo $phong['MaDatBan']; ?>, 'tiec')"
             <?php endif; ?>>
            <div class="table-icon">
                <i class="fas fa-door-open"></i>
            </div>
            <div class="table-number"><?php echo $phong['TenPhong']; ?></div>
            <div class="table-info">
                <i class="fas fa-users me-1"></i>
                <?php echo $phong['SucChua']; ?> người
            </div>
            
            <?php if ($phong['MaDatBan']): ?>
                <div class="booking-time">
                    <i class="fas fa-clock me-1"></i>
                    <?php echo date('H:i', strtotime($phong['NgayGio'])); ?>
                </div>
                <div class="customer-name">
                    <i class="fas fa-user me-1"></i>
                    <?php echo htmlspecialchars($phong['HoTen']); ?>
                </div>
                <?php if ($phong['Tencombo']): ?>
                <div class="table-info text-success">
                    <i class="fas fa-box-open me-1"></i>
                    <?php echo htmlspecialchars($phong['Tencombo']); ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <span class="status-badge"><?php echo $status_text; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal chi tiết đặt bàn -->
<div class="modal fade" id="bookingDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Chi tiết đặt bàn
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookingDetailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function showBookingDetail(madatban, loai) {
    $('#bookingDetailModal').modal('show');
    
    $.ajax({
        url: 'get_booking_detail.php',
        type: 'GET',
        data: {
            madatban: madatban,
            loai: loai
        },
        success: function(response) {
            $('#bookingDetailContent').html(response);
        },
        error: function() {
            $('#bookingDetailContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Lỗi khi tải dữ liệu
                </div>
            `);
        }
    });
}

function updateStatus(madatban, status) {
    if (!confirm('Xác nhận thay đổi trạng thái?')) return;
    
    $.ajax({
        url: '../process/update_booking_status.php',
        type: 'POST',
        data: {
            madatban: madatban,
            status: status
        },
        success: function(response) {
            const res = JSON.parse(response);
            if (res.success) {
                // Đóng modal trước
                $('#bookingDetailModal').modal('hide');
                
                // Chờ modal đóng xong mới reload
                setTimeout(function() {
                    alert('Cập nhật thành công!');
                    location.reload();
                }, 300);
            } else {
                alert('Lỗi: ' + res.message);
            }
        },
        error: function() {
            alert('Lỗi khi cập nhật');
        }
    });
}

function cancelBooking(madatban) {
    if (!confirm('Xác nhận hủy đặt bàn này?')) return;
    
    $.ajax({
        url: '../process/cancel_booking.php',
        type: 'POST',
        data: {
            madatban: madatban
        },
        success: function(response) {
            const res = JSON.parse(response);
            if (res.success) {
                // Đóng modal trước
                $('#bookingDetailModal').modal('hide');
                
                // Chờ modal đóng xong mới reload
                setTimeout(function() {
                    alert('Đã hủy đặt bàn!');
                    location.reload();
                }, 300);
            } else {
                alert('Lỗi: ' + res.message);
            }
        },
        error: function() {
            alert('Lỗi khi hủy');
        }
    });
}
</script>

</body>
</html>