<?php
session_start();
require 'includes/db_connect.php';

// Xử lý tìm kiếm
$orders = [];
$search_phone = "";
$is_searching = false;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_phone = trim($_GET['search']);
    $is_searching = true;
    
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
    // Tìm vị trí của từ "Tỉnh"
    $pos = stripos($address, 'Tỉnh');
    
    if ($pos !== false) {
        // Ẩn phần trước "Tỉnh", giữ từ "Tỉnh" trở về sau
        $hidden_part = str_repeat('*', $pos);
        $visible_part = substr($address, $pos);
        return $hidden_part . $visible_part;
    }
    
    // Nếu không có "Tỉnh", thử tìm "Thành phố"
    $pos = stripos($address, 'Thành phố');
    if ($pos !== false) {
        $hidden_part = str_repeat('*', $pos);
        $visible_part = substr($address, $pos);
        return $hidden_part . $visible_part;
    }
    
    // Nếu không tìm thấy, ẩn hết
    return str_repeat('*', min(strlen($address), 30));
}

// Hàm hiển thị trạng thái
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu đơn hàng - Pizza</title>
    
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
                        <i class="fas fa-search me-2"></i>Tra cứu đơn hàng
                    </h2>
                    <p class="text-center mb-4">Nhập số điện thoại để kiểm tra trạng thái đơn hàng của bạn</p>
                    
                    <form method="GET" action="" class="mb-4">
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
                            Nhập đúng số điện thoại bạn đã đặt hàng
                        </small>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Orders List -->
    <section class="py-5">
        <div class="container">
            <?php if ($is_searching): ?>
                <h4 class="mb-4">
                    <i class="fas fa-receipt me-2"></i>
                    Kết quả tìm kiếm cho số: <?php echo htmlspecialchars($search_phone); ?>
                </h4>
                
                <?php if (empty($orders)): ?>
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Không tìm thấy đơn hàng nào với số điện thoại này!
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
                <?php if (!empty($orders)): ?>
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
                <?php elseif (!$is_searching): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-box-open me-2"></i>
                            Chưa có đơn hàng nào!
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
</body>
</html>