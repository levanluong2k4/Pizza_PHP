<?php
session_start();
require 'includes/db_connect.php';

// Xử lý tìm kiếm
$orders = [];
$bookings = [];
$search_phone = "";
$search_type = "orders"; // Mặc định tìm đơn hàng
$is_searching = false;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_phone = trim($_GET['search']);
    $search_type = isset($_GET['type']) ? $_GET['type'] : 'orders';
    $is_searching = true;
    
    if ($search_type === 'orders') {
        // Tìm kiếm đơn hàng theo số điện thoại
        $stmt = $ketnoi->prepare("SELECT MaDH, TongTien, ngaydat, trangthai, sdtnguoinhan, Tennguoinhan, diachinguoinhan 
                                FROM donhang 
                                WHERE is_guest = 1 AND sdtnguoinhan = ?
                                ORDER BY ngaydat DESC");
        $stmt->bind_param("s", $search_phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        // Tìm kiếm đơn đặt bàn theo số điện thoại
        $stmt = $ketnoi->prepare("SELECT MaDatBan, MaBan, MaPhong, MaCombo, HoTen, SDT, NgayGio, LoaiDatBan, GhiChu, TrangThaiDatBan, NgayTao
                                FROM datban 
                                WHERE SDT = ?
                                ORDER BY NgayGio DESC");
        $stmt->bind_param("s", $search_phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} else {
    // Hiển thị tất cả đơn hàng guest (ẩn thông tin)
    $result = $ketnoi->query("SELECT MaDH, TongTien, ngaydat, trangthai, sdtnguoinhan, Tennguoinhan, diachinguoinhan 
                            FROM donhang 
                            WHERE is_guest = 1 
                            ORDER BY ngaydat DESC 
                            LIMIT 50");
    $orders = $result->fetch_all(MYSQLI_ASSOC);
}

// Hàm ẩn số điện thoại
function hide_phone($phone) {
    if (strlen($phone) >= 3) {
        return str_repeat('*', strlen($phone) - 3) . substr($phone, -3);
    }
    return $phone;
}

// Hàm ẩn địa chỉ - chỉ hiện từ "Tỉnh" trở về sau
function hide_address($address) {
    $pos = stripos($address, 'Tỉnh');
    
    if ($pos !== false) {
        $hidden_part = str_repeat('*', $pos);
        $visible_part = substr($address, $pos);
        return $hidden_part . $visible_part;
    }
    
    $pos = stripos($address, 'Thành phố');
    if ($pos !== false) {
        $hidden_part = str_repeat('*', $pos);
        $visible_part = substr($address, $pos);
        return $hidden_part . $visible_part;
    }
    
    return str_repeat('*', min(strlen($address), 30));
}

// Hàm hiển thị trạng thái đơn hàng
function get_status_badge($status) {
    $badges = [
        'Chờ xử lý' => 'bg-warning text-dark',
        'Đang chuẩn bị' => 'bg-info text-white',
        'Đang giao' => 'bg-primary text-white',
        'Đã giao' => 'bg-success text-white',
        'Đã hủy' => 'bg-danger text-white'
    ];
    
    $class = $badges[$status] ?? 'bg-secondary text-white';
    return "<span class='badge $class'>$status</span>";
}

// Hàm hiển thị trạng thái đặt bàn
function get_booking_status_badge($status) {
    $badges = [
        'chuathanhtoan' => ['label' => 'Chưa thanh toán', 'class' => 'bg-warning text-dark'],
        'dathanhtoan' => ['label' => 'Đã thanh toán', 'class' => 'bg-success text-white'],
        'dahuy' => ['label' => 'Đã hủy', 'class' => 'bg-danger text-white'],
        'dahoanthanh' => ['label' => 'Đã hoàn thành', 'class' => 'bg-info text-white']
    ];
    
    $badge = $badges[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-secondary text-white'];
    return "<span class='badge {$badge['class']}'>{$badge['label']}</span>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu đơn hàng & đặt bàn - Pizza</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/pizza.css">
    <link rel="stylesheet" href="css/basic.css">
    
    <style>
        .order-card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .search-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 3rem 0;
            color: white;
        }
        .hidden-info {
            color: #6c757d;
            font-style: italic;
        }
        .type-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .type-selector .btn {
            flex: 1;
            padding: 1rem;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .type-selector .btn:not(.active) {
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
        }
        .type-selector .btn.active {
            background: white;
            color: #667eea;
            border: 2px solid white;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header class="bg-icon">
        <?php include 'components/navbar.php'; ?>
    </header>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="text-center mb-4">
                        <i class="fas fa-search me-2"></i>Tra cứu đơn hàng & đặt bàn
                    </h2>
                    <p class="text-center mb-4">Nhập số điện thoại để kiểm tra trạng thái</p>
                    
                    <form method="GET" action="" id="searchForm">
                        <!-- Type Selector -->
                        <div class="type-selector">
                            <button type="button" class="btn <?php echo $search_type === 'orders' ? 'active' : ''; ?>" 
                                    onclick="selectType('orders')">
                                <i class="fas fa-shopping-cart me-2"></i>Đơn hàng
                            </button>
                            <button type="button" class="btn <?php echo $search_type === 'bookings' ? 'active' : ''; ?>" 
                                    onclick="selectType('bookings')">
                                <i class="fas fa-calendar-check me-2"></i>Đặt bàn
                            </button>
                        </div>
                        
                        <input type="hidden" name="type" id="searchType" value="<?php echo $search_type; ?>">
                        
                        <div class="input-group input-group-lg shadow">
                            <input 
                                type="text" 
                                name="search" 
                                class="form-control" 
                                placeholder="Nhập số điện thoại (VD: 0912345678)"
                                value="<?php echo htmlspecialchars($search_phone); ?>"
                                pattern="[0-9]{10,11}"
                                required>
                            <button class="btn btn-light" type="submit">
                                <i class="fas fa-search me-2"></i>Tìm kiếm
                            </button>
                        </div>
                        <small class="text-white d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Nhập đúng số điện thoại bạn đã đặt
                        </small>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    <section class="py-5">
        <div class="container">
            <?php if ($is_searching): ?>
                <h4 class="mb-4">
                    <i class="fas fa-<?php echo $search_type === 'orders' ? 'receipt' : 'calendar-alt'; ?> me-2"></i>
                    Kết quả tìm kiếm <?php echo $search_type === 'orders' ? 'đơn hàng' : 'đặt bàn'; ?> 
                    cho số: <?php echo htmlspecialchars($search_phone); ?>
                </h4>
                
                <?php if ($search_type === 'orders' && empty($orders)): ?>
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Không tìm thấy đơn hàng nào với số điện thoại này!
                    </div>
                <?php elseif ($search_type === 'bookings' && empty($bookings)): ?>
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Không tìm thấy đơn đặt bàn nào với số điện thoại này!
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <h4 class="mb-4">
                    <i class="fas fa-list me-2"></i>
                    Danh sách đơn hàng gần đây
                </h4>
                <div class="alert alert-info">
                    <i class="fas fa-shield-alt me-2"></i>
                    Thông tin chi tiết được ẩn để bảo mật. Vui lòng nhập số điện thoại để xem đầy đủ.
                </div>
            <?php endif; ?>

            <div class="row">
                <?php if ($search_type === 'orders' && !empty($orders)): ?>
                    <!-- Hiển thị đơn hàng -->
                    <?php foreach ($orders as $order): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card order-card h-100 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-shopping-bag me-2"></i>
                                        Mã đơn: #<?php echo $order['MaDH']; ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">Tên người nhận:</small>
                                        <p class="mb-0 fw-bold"><?php echo htmlspecialchars($order['Tennguoinhan']); ?></p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Số điện thoại:</small>
                                        <p class="mb-0 <?php echo $is_searching ? 'fw-bold' : 'hidden-info'; ?>">
                                            <?php 
                                            echo $is_searching 
                                                ? htmlspecialchars($order['sdtnguoinhan']) 
                                                : hide_phone($order['sdtnguoinhan']); 
                                            ?>
                                        </p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Địa chỉ:</small>
                                        <p class="mb-0 <?php echo $is_searching ? '' : 'hidden-info'; ?>">
                                            <?php 
                                            echo $is_searching 
                                                ? htmlspecialchars($order['diachinguoinhan']) 
                                                : hide_address($order['diachinguoinhan']); 
                                            ?>
                                        </p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Ngày đặt:</small>
                                        <p class="mb-0">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($order['ngaydat'])); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Trạng thái:</small>
                                        <p class="mb-0">
                                            <?php echo get_status_badge($order['trangthai']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="border-top pt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted">Tổng tiền:</span>
                                            <h5 class="mb-0 text-danger">
                                                <?php echo number_format($order['TongTien'], 0, ',', '.'); ?>đ
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                <?php elseif ($search_type === 'bookings' && !empty($bookings)): ?>
                    <!-- Hiển thị đơn đặt bàn -->
                    <?php foreach ($bookings as $booking): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card order-card h-100 shadow-sm border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-calendar-check me-2"></i>
                                        Mã đặt bàn: #<?php echo $booking['MaDatBan']; ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">Tên khách hàng:</small>
                                        <p class="mb-0 fw-bold"><?php echo htmlspecialchars($booking['HoTen']); ?></p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Số điện thoại:</small>
                                        <p class="mb-0 fw-bold">
                                            <?php echo htmlspecialchars($booking['SDT']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Thời gian đặt:</small>
                                        <p class="mb-0">
                                            <i class="far fa-clock me-1"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($booking['NgayGio'])); ?>
                                        </p>
                                    </div>
                                    
                                    <?php if ($booking['MaBan']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">Bàn số:</small>
                                        <p class="mb-0">
                                            <i class="fas fa-chair me-1"></i>
                                            Bàn #<?php echo $booking['MaBan']; ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking['MaPhong']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">Phòng:</small>
                                        <p class="mb-0">
                                            <i class="fas fa-door-open me-1"></i>
                                            Phòng #<?php echo $booking['MaPhong']; ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking['MaCombo']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">Combo:</small>
                                        <p class="mb-0">
                                            <i class="fas fa-utensils me-1"></i>
                                            Combo #<?php echo $booking['MaCombo']; ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Loại đặt bàn:</small>
                                        <p class="mb-0">
                                            <span class="badge bg-info">
                                                <?php echo $booking['LoaiDatBan'] === 'thuong' ? 'Thường' : 'Đặt trước'; ?>
                                            </span>
                                        </p>
                                    </div>
                                    
                                    <?php if ($booking['GhiChu']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">Ghi chú:</small>
                                        <p class="mb-0 small"><?php echo htmlspecialchars($booking['GhiChu']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="border-top pt-3">
                                        <small class="text-muted">Trạng thái:</small>
                                        <p class="mb-0">
                                            <?php echo get_booking_status_badge($booking['TrangThaiDatBan']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                <?php elseif (!$is_searching && !empty($orders)): ?>
                    <!-- Hiển thị danh sách đơn hàng mặc định (ẩn thông tin) -->
                    <?php foreach ($orders as $order): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card order-card h-100 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-shopping-bag me-2"></i>
                                        Mã đơn: #<?php echo $order['MaDH']; ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">Tên người nhận:</small>
                                        <p class="mb-0 fw-bold"><?php echo htmlspecialchars($order['Tennguoinhan']); ?></p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Số điện thoại:</small>
                                        <p class="mb-0 hidden-info">
                                            <?php echo hide_phone($order['sdtnguoinhan']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Địa chỉ:</small>
                                        <p class="mb-0 hidden-info">
                                            <?php echo hide_address($order['diachinguoinhan']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Ngày đặt:</small>
                                        <p class="mb-0">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($order['ngaydat'])); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Trạng thái:</small>
                                        <p class="mb-0">
                                            <?php echo get_status_badge($order['trangthai']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="border-top pt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted">Tổng tiền:</span>
                                            <h5 class="mb-0 text-danger">
                                                <?php echo number_format($order['TongTien'], 0, ',', '.'); ?>đ
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    
    <script>
        function selectType(type) {
            document.getElementById('searchType').value = type;
            
            // Update button states
            const buttons = document.querySelectorAll('.type-selector .btn');
            buttons.forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.btn').classList.add('active');
        }
    </script>
</body>
</html>