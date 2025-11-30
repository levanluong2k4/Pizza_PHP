<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customer_id <= 0) {
    header('Location: list_customer.php');
    exit;
}

// Lấy thông tin khách hàng
$customer_sql = "SELECT * FROM khachhang WHERE MaKH = $customer_id";
$customer = $conn->query($customer_sql)->fetch_assoc();

if (!$customer) {
    header('Location: list_customer.php');
    exit;
}

// Thống kê đơn hàng
$orders_sql = "SELECT 
    dh.*,
    COUNT(ctdh.id) as so_san_pham
FROM donhang dh
LEFT JOIN chitietdonhang ctdh ON dh.MaDH = ctdh.MaDH
WHERE dh.MaKH = $customer_id
GROUP BY dh.MaDH
ORDER BY dh.ngaydat DESC";
$orders_result = $conn->query($orders_sql);

// Thống kê đặt bàn
$bookings_sql = "SELECT * FROM datban WHERE MaKH = $customer_id ORDER BY NgayGio DESC";
$bookings_result = $conn->query($bookings_sql);

// Tổng số đơn hàng và chi tiêu
$stats_sql = "SELECT 
    COUNT(MaDH) as tong_don,
    COALESCE(SUM(TongTien), 0) as tong_tien,
    AVG(TongTien) as trung_binh,
    MIN(ngaydat) as lan_dau,
    MAX(ngaydat) as lan_cuoi
FROM donhang 
WHERE MaKH = $customer_id";
$stats = $conn->query($stats_sql)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(90deg, #28a745, #66bb6a);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
        }
        .stat-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
        }
        .stat-box h4 {
            color: #28a745;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php include '../../navbar_admin.php'; ?>

    <div class="container-fluid mt-4">
        <div class="mb-3">
            <a href="list_customer.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>

        <div class="row">
            <!-- Thông tin khách hàng -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Thông tin khách hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px; font-size: 2rem;">
                                <i class="fas fa-user"></i>
                            </div>
                            <h4 class="mt-2"><?php echo htmlspecialchars($customer['HoTen']); ?></h4>
                            <p class="text-muted">ID: <?php echo $customer['MaKH']; ?></p>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <span class="info-label"><i class="fas fa-envelope"></i> Email:</span><br>
                            <?php echo htmlspecialchars($customer['Email']); ?>
                        </div>

                        <div class="mb-2">
                            <span class="info-label"><i class="fas fa-phone"></i> Số điện thoại:</span><br>
                            <?php echo htmlspecialchars($customer['SoDT'] ?: 'Chưa cập nhật'); ?>
                        </div>

                        <div class="mb-2">
                            <span class="info-label"><i class="fas fa-map-marker-alt"></i> Địa chỉ:</span><br>
                            <?php 
                            $address = array_filter([
                                $customer['sonha'],
                                $customer['xaphuong'],
                                $customer['huyenquan'],
                                $customer['tinhthanhpho']
                            ]);
                            echo htmlspecialchars(implode(', ', $address) ?: 'Chưa cập nhật');
                            ?>
                        </div>

                        <div class="mb-2">
                            <span class="info-label"><i class="fas fa-calendar"></i> Ngày đăng ký:</span><br>
                            <?php echo date('d/m/Y H:i', strtotime($customer['ngaytao'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Thống kê -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê</h5>
                    </div>
                    <div class="card-body">
                        <div class="stat-box">
                            <h4><?php echo $stats['tong_don']; ?></h4>
                            <p class="mb-0 text-muted">Tổng đơn hàng</p>
                        </div>
                        <div class="stat-box">
                            <h4 class="text-success"><?php echo number_format($stats['tong_tien'], 0, ',', '.'); ?>đ</h4>
                            <p class="mb-0 text-muted">Tổng chi tiêu</p>
                        </div>
                        <div class="stat-box">
                            <h4><?php echo number_format($stats['trung_binh'], 0, ',', '.'); ?>đ</h4>
                            <p class="mb-0 text-muted">Giá trị đơn TB</p>
                        </div>
                        <?php if ($stats['lan_dau']): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-shopping-cart"></i> Lần đầu: <?php echo date('d/m/Y', strtotime($stats['lan_dau'])); ?><br>
                                <i class="fas fa-clock"></i> Lần cuối: <?php echo date('d/m/Y', strtotime($stats['lan_cuoi'])); ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Lịch sử đơn hàng và đặt bàn -->
            <div class="col-md-8">
                <!-- Đơn hàng -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shopping-bag"></i> Lịch sử đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($orders_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Ngày đặt</th>
                                            <th>Số SP</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                            <th>Thanh toán</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($order = $orders_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo $order['MaDHcode']; ?></strong></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($order['ngaydat'])); ?></td>
                                                <td><?php echo $order['so_san_pham']; ?> SP</td>
                                                <td><strong class="text-success"><?php echo number_format($order['TongTien'], 0, ',', '.'); ?>đ</strong></td>
                                                <td>
                                                    <?php
                                                    $status_colors = [
                                                        'Chờ xử lý' => 'warning',
                                                        'Đang giao' => 'info',
                                                        'Giao thành công' => 'success',
                                                        'Hủy đơn' => 'danger'
                                                    ];
                                                    $color = $status_colors[$order['trangthai']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>"><?php echo $order['trangthai']; ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($order['trangthaithanhtoan'] == 'dathanhtoan'): ?>
                                                        <span class="badge bg-success">Đã thanh toán</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Chưa thanh toán</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted">Chưa có đơn hàng nào</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Đặt bàn -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Lịch sử đặt bàn</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($bookings_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mã đặt</th>
                                            <th>Ngày giờ</th>
                                            <th>Loại</th>
                                            <th>Bàn/Phòng</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($booking = $bookings_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong>#<?php echo $booking['MaDatBan']; ?></strong></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($booking['NgayGio'])); ?></td>
                                                <td>
                                                    <?php if ($booking['LoaiDatBan'] == 'tiec'): ?>
                                                        <span class="badge bg-purple">Tiệc</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">Thường</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if ($booking['LoaiDatBan'] == 'tiec') {
                                                        echo "Phòng " . $booking['MaPhong'];
                                                    } else {
                                                        echo "Bàn " . $booking['MaBan'];
                                                    }
                                                    ?>
                                                </td>
                                                <td><strong><?php echo number_format($booking['Tongtien'], 0, ',', '.'); ?>đ</strong></td>
                                                <td>
                                                    <?php
                                                    $booking_status_colors = [
                                                        'da_dat' => 'warning',
                                                        'da_xac_nhan' => 'info',
                                                        'dang_su_dung' => 'primary',
                                                        'thanh_cong' => 'success',
                                                        'da_huy' => 'danger'
                                                    ];
                                                    $b_color = $booking_status_colors[$booking['TrangThaiDatBan']] ?? 'secondary';
                                                    $status_text = [
                                                        'da_dat' => 'Đã đặt',
                                                        'da_xac_nhan' => 'Đã xác nhận',
                                                        'dang_su_dung' => 'Đang sử dụng',
                                                        'thanh_cong' => 'Thành công',
                                                        'da_huy' => 'Đã hủy'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $b_color; ?>">
                                                        <?php echo $status_text[$booking['TrangThaiDatBan']]; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted">Chưa có lịch đặt bàn nào</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>