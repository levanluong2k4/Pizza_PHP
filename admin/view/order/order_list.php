<?php
// order_list.php - Danh sách đơn hàng
session_start();
require_once __DIR__ . '/../../../includes/db_connect.php';

// Thiết lập số đơn hàng mỗi trang
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// Xử lý lọc
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

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
    
    // Giữ lại các tham số lọc khi chuyển hướng
    $query_params = [];
    if ($status_filter) $query_params[] = "status=" . urlencode($status_filter);
    if ($date_from) $query_params[] = "date_from=" . urlencode($date_from);
    if ($date_to) $query_params[] = "date_to=" . urlencode($date_to);
    if ($search) $query_params[] = "search=" . urlencode($search);
    if ($current_page > 1) $query_params[] = "page=" . $current_page;
    
    $redirect_url = "order_list.php";
    if (!empty($query_params)) {
        $redirect_url .= "?" . implode("&", $query_params);
    }
    
    header('Location: ' . $redirect_url);
    exit;
}

// Xây dựng điều kiện WHERE
$where_conditions = [];
$params = [];
$types = "";

if ($status_filter && $status_filter !== 'Tất cả') {
    $where_conditions[] = "dh.trangthai = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($date_from) {
    $where_conditions[] = "DATE(dh.ngaydat) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if ($date_to) {
    $where_conditions[] = "DATE(dh.ngaydat) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

if ($search) {
    $where_conditions[] = "(dh.MaDHcode LIKE ? OR kh.HoTen LIKE ? OR dh.tennguoinhan LIKE ? OR dh.sdtnguoinhan LIKE ?)";
    $search_term = "%" . $search . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ssss";
}

$where_sql = "";
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// Đếm tổng số đơn hàng
$count_sql = "SELECT COUNT(DISTINCT dh.MaDH) as total 
              FROM donhang dh
              LEFT JOIN khachhang kh ON dh.MaKH = kh.MaKH
              $where_sql";
              
$count_stmt = $ketnoi->prepare($count_sql);
if ($params) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Tính tổng số trang
$total_pages = ceil($total_rows / $items_per_page);
if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
}

// Tính offset
$offset = ($current_page - 1) * $items_per_page;

// Lấy danh sách đơn hàng
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
        $where_sql
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
            dh.ngaydat DESC
        LIMIT ? OFFSET ?";

// Thêm limit và offset vào params
$params[] = $items_per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $ketnoi->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
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
            max-width: 1800px;
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
        
        .filter-navbar {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }
        
        .form-control {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.1);
        }
        
        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #45a049;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-reset {
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }
        
        .btn-reset:hover {
            background: #e2e6ea;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
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
        
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            margin: 2px;
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
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
            gap: 10px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background: #f5f5f5;
            border-color: #ccc;
        }
        
        .pagination .active {
            background: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        
        .pagination .disabled {
            color: #999;
            cursor: not-allowed;
            border-color: #eee;
        }
        
        .pagination-info {
            text-align: center;
            margin-top: 10px;
            color: #666;
            font-size: 14px;
        }
        
        .stats-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .total-orders {
            font-weight: 600;
            color: #495057;
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
        
        <!-- Thanh thống kê -->
        <div class="stats-bar">
            <div class="total-orders">
                Tổng số đơn hàng: <strong><?php echo $total_rows; ?></strong>
            </div>
        </div>
        
        <!-- Navbar lọc -->
        <div class="filter-navbar">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="status">Trạng thái</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Tất cả" <?php echo $status_filter === 'Tất cả' || $status_filter === '' ? 'selected' : ''; ?>>Tất cả</option>
                        <option value="Chờ xử lý" <?php echo $status_filter === 'Chờ xử lý' ? 'selected' : ''; ?>>Chờ xử lý</option>
                        <option value="Chờ giao" <?php echo $status_filter === 'Chờ giao' ? 'selected' : ''; ?>>Chờ giao</option>
                        <option value="Đang giao" <?php echo $status_filter === 'Đang giao' ? 'selected' : ''; ?>>Đang giao</option>
                        <option value="Giao thành công" <?php echo $status_filter === 'Giao thành công' ? 'selected' : ''; ?>>Giao thành công</option>
                        <option value="Hủy đơn" <?php echo $status_filter === 'Hủy đơn' ? 'selected' : ''; ?>>Hủy đơn</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_from">Từ ngày</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_to">Đến ngày</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                
                <div class="form-group">
                    <label for="search">Tìm kiếm</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Mã đơn, tên, SĐT..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn ">
                        🔍 Lọc
                    </button>
                    <a href="order_list.php" class="btn btn-reset">
                        ↻ Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Danh sách đơn hàng -->
        <?php if ($result->num_rows > 0): ?>
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
                                            if (!empty(trim($img))) {
                                                echo '<img src="/unitop/backend/lesson/school/project_pizza/' . htmlspecialchars(trim($img)) . '" alt="Product">';
                                                $count++;
                                            }
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
                                <?php if ($row['is_guest'] == 1): ?>
                                    <span class="guest-badge">Khách vãng lai</span>
                                <?php else: ?>
                                    <span class="guest-badge">Khách</span>
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
                                            <button type="submit" name="update_status" class="btn-action btn-success">✓ Duyệt</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['trangthai'] === 'Chờ giao'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="maDH" value="<?php echo $row['MaDH']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $row['trangthai']; ?>">
                                            <input type="hidden" name="action" value="shipping">
                                            <button type="submit" name="update_status" class="btn-action btn-info">🚚 Giao hàng</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['trangthai'] === 'Đang giao'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="maDH" value="<?php echo $row['MaDH']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $row['trangthai']; ?>">
                                            <input type="hidden" name="action" value="complete">
                                            <button type="submit" name="update_status" class="btn-action btn-success">✓ Hoàn thành</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['trangthai'] !== 'Đang giao' && $row['trangthai'] !== 'Giao thành công' && $row['trangthai'] !== 'Hủy đơn'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="maDH" value="<?php echo $row['MaDH']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $row['trangthai']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" name="update_status" class="btn-action btn-danger" 
                                                    onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">✗ Hủy</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <a href="order_detail.php?id=<?php echo $row['MaDH']; ?>" class="btn-action btn-primary">👁 Chi tiết</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <!-- Phân trang -->
            <div class="pagination-info">
                Hiển thị từ <?php echo min($offset + 1, $total_rows); ?> đến <?php echo min($offset + $items_per_page, $total_rows); ?> của <?php echo $total_rows; ?> đơn hàng
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?<?php echo build_query_string(['page' => 1]); ?>">« Đầu</a>
                        <a href="?<?php echo build_query_string(['page' => $current_page - 1]); ?>">‹ Trước</a>
                    <?php else: ?>
                        <span class="disabled">« Đầu</span>
                        <span class="disabled">‹ Trước</span>
                    <?php endif; ?>
                    
                    <?php
                    // Hiển thị số trang
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                        if ($i == $current_page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo build_query_string(['page' => $i]); ?>"><?php echo $i; ?></a>
                        <?php endif;
                    endfor;
                    ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?<?php echo build_query_string(['page' => $current_page + 1]); ?>">Sau ›</a>
                        <a href="?<?php echo build_query_string(['page' => $total_pages]); ?>">Cuối »</a>
                    <?php else: ?>
                        <span class="disabled">Sau ›</span>
                        <span class="disabled">Cuối »</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <p style="font-size: 18px;">📭 Không tìm thấy đơn hàng nào</p>
                <p>Hãy thử thay đổi bộ lọc hoặc tìm kiếm</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
    // Hàm tạo query string giữ nguyên các tham số lọc
    function build_query_string($new_params = []) {
        global $status_filter, $date_from, $date_to, $search, $current_page;
        
        $params = [];
        if ($status_filter && $status_filter !== 'Tất cả') $params['status'] = $status_filter;
        if ($date_from) $params['date_from'] = $date_from;
        if ($date_to) $params['date_to'] = $date_to;
        if ($search) $params['search'] = $search;
        
        $params = array_merge($params, $new_params);
        
        return http_build_query($params);
    }
    ?>
    
    <script>
        // Xử lý sự kiện cho ngày
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            
            // Thiết lập max date cho date_to là hôm nay
            const dateFromInput = document.getElementById('date_from');
            const dateToInput = document.getElementById('date_to');
            
            if (dateFromInput) {
                dateFromInput.max = today;
            }
            
            if (dateToInput) {
                dateToInput.max = today;
                
                // Nếu date_from có giá trị, set min cho date_to
                if (dateFromInput.value) {
                    dateToInput.min = dateFromInput.value;
                }
                
                dateFromInput.addEventListener('change', function() {
                    dateToInput.min = this.value;
                    if (dateToInput.value && dateToInput.value < this.value) {
                        dateToInput.value = this.value;
                    }
                });
            }
            
            // Tự động submit form khi thay đổi trạng thái
            const statusSelect = document.getElementById('status');
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    if (this.value !== '') {
                        this.form.submit();
                    }
                });
            }
        });
    </script>
</body>
</html>