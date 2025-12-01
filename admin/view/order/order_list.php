<?php
// order_list.php - Danh sách đơn hàng
session_start();
require_once __DIR__ . '/../../../includes/db_connect.php';

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $maDH = $_POST['maDH'];
    $currentStatus = $_POST['current_status'];
    $action = $_POST['action'];
    
    $newStatus = '';
    $canUpdate = true;
    
    // Logic chuyển trạng thái
    switch($action) {
        case 'approve':
            if ($currentStatus === 'Chờ xử lý') {
                $newStatus = 'Chờ giao';
            }
            break;
        case 'shipping':
            if ($currentStatus === 'Chờ giao') {
                $newStatus = 'Đang giao';
            }
            break;
        case 'complete':
            if ($currentStatus === 'Đang giao') {
                $newStatus = 'Giao thành công';
            }
            break;
        case 'cancel':
            // Không thể hủy nếu đang giao
            if ($currentStatus === 'Đang giao') {
                $canUpdate = false;
                $_SESSION['error'] = 'Không thể hủy đơn hàng đang giao!';
            } else {
                $newStatus = 'Hủy đơn';
            }
            break;
    }
    
    if ($canUpdate && $newStatus) {
        $sql = "UPDATE donhang SET trangthai = ? WHERE MaDH = ?";
        $stmt = $ketnoi->prepare($sql);
        $stmt->bind_param("si", $newStatus, $maDH);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Cập nhật trạng thái thành công!';
        } else {
            $_SESSION['error'] = 'Lỗi cập nhật trạng thái!';
        }
    }
    
    header('Location: order_list.php');
    exit;
}

// Lấy danh sách đơn hàng - Sắp xếp theo trạng thái ưu tiên
$sql = "SELECT 
            dh.MaDH,
            dh.MaDHcode,
            dh.ngaydat,
            dh.trangthai,
            dh.TongTien,
            dh.tennguoinhan,
            dh.sdtnguoinhan,
            dh.diachinguoinhan,
            dh.is_guest,
            CASE WHEN dh.is_guest = 1 THEN dh.tennguoinhan ELSE kh.HoTen END as HoTen,
            GROUP_CONCAT(DISTINCT sps.Anh SEPARATOR ',') as DanhSachAnh
        FROM donhang dh
        LEFT JOIN khachhang kh ON dh.MaKH = kh.MaKH
        LEFT JOIN chitietdonhang ct ON dh.MaDH = ct.MaDH
        LEFT JOIN sanpham_size sps ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize
        GROUP BY dh.MaDH
        ORDER BY 
            CASE dh.trangthai
                WHEN 'Chờ xử lý' THEN 1
                WHEN 'Đang giao' THEN 2
                WHEN 'Chờ giao' THEN 3
                WHEN 'Giao thành công' THEN 4
                WHEN 'Hủy đơn' THEN 5
                ELSE 6
            END,
            dh.ngaydat DESC";

$result = $ketnoi->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn đặt hàng</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #4CAF50;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #4CAF50;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-ready {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-shipping {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            margin: 2px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .order-row {
            cursor: pointer;
        }
        
        .price {
            color: #e74c3c;
            font-weight: 600;
        }
        
        .product-images {
            display: flex;
            gap: 5px;
        }
        
        .product-images img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .guest-badge {
            background: #6c757d;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            margin-left: 5px;
        }
    </style>
</head>
<body>

<?php include '../../navbar_admin.php'; ?>
    <div class="container">
        <h1>📦 Quản lý đơn đặt hàng</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✓ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ✗ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Sản phẩm</th>
                    <th>Khách hàng</th>
                    <th>SĐT</th>
                    <th>Địa chỉ</th>
                    <th>Tổng tiền</th>
                    <th>Ngày đặt</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="order-row">
                        <td><strong><?php echo $row['MaDHcode']; ?></strong></td>
                        <td>
                            <div class="product-images">
                                <?php
                                if (!empty($row['DanhSachAnh'])) {
                                    $images = array_unique(explode(',', $row['DanhSachAnh']));
                                    $count = 0;
                                    foreach ($images as $img) {
                                        if ($count >= 3) break;
                                        echo '<img src="/unitop/backend/lesson/school/project_pizza/' . htmlspecialchars(trim($img)) . '" alt="Product">';
                                        $count++;
                                    }
                                    if (count($images) > 3) {
                                        echo '<span style="align-self:center;font-size:12px;color:#666;">+' . (count($images) - 3) . '</span>';
                                    }
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['HoTen'] ?? 'Khách lẻ'); ?>
                            <?php if ($row['is_guest'] == 0): ?>
                                <span class="guest-badge">Khách</span>
                            <?php else: ?>
                                <span class="guest-badge">Khách vãng lai</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['sdtnguoinhan']; ?></td>
                        <td><?php echo htmlspecialchars($row['diachinguoinhan']); ?></td>
                        <td class="price"><?php echo number_format($row['TongTien'], 0, ',', '.'); ?>đ</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['ngaydat'])); ?></td>
                        <td>
                            <?php
                            $statusClass = '';
                            switch($row['trangthai']) {
                                case 'Chờ xử lý':
                                    $statusClass = 'status-pending';
                                    break;
                                case 'Chờ giao':
                                    $statusClass = 'status-ready';
                                    break;
                                case 'Đang giao':
                                    $statusClass = 'status-shipping';
                                    break;
                                case 'Giao thành công':
                                    $statusClass = 'status-completed';
                                    break;
                                case 'Hủy đơn':
                                    $statusClass = 'status-cancelled';
                                    break;
                            }
                            ?>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $row['trangthai']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($row['trangthai'] === 'Chờ xử lý'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="maDH" value="<?php echo $row['MaDH']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $row['trangthai']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" name="update_status" class="btn btn-success">✓ Duyệt</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($row['trangthai'] === 'Chờ giao'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="maDH" value="<?php echo $row['MaDH']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $row['trangthai']; ?>">
                                        <input type="hidden" name="action" value="shipping">
                                        <button type="submit" name="update_status" class="btn btn-info">🚚 Giao hàng</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($row['trangthai'] === 'Đang giao'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="maDH" value="<?php echo $row['MaDH']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $row['trangthai']; ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <button type="submit" name="update_status" class="btn btn-success">✓ Hoàn thành</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($row['trangthai'] !== 'Đang giao' && $row['trangthai'] !== 'Giao thành công' && $row['trangthai'] !== 'Hủy đơn'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="maDH" value="<?php echo $row['MaDH']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $row['trangthai']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" name="update_status" class="btn btn-danger" 
                                                onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">✗ Hủy</button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="order_detail.php?id=<?php echo $row['MaDH']; ?>" class="btn btn-primary">👁 Chi tiết</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

