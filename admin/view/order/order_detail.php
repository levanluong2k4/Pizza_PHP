<?php
// order_detail.php - Chi tiết đơn hàng
session_start();
require __DIR__ . '/../../../includes/db_connect.php';

// Xử lý AJAX request
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['ajax_action'] == 'update_quantity') {
        $maDH = $_POST['maDH'];
        $maSP = $_POST['maSP'];
        $maSize = $_POST['maSize'] ?? null;
        $soLuong = $_POST['soLuong'];
        
        $sql = "UPDATE chitietdonhang SET SoLuong = ? WHERE MaDH = ? AND MaSP = ? AND MaSize = ?";
        $stmt = $ketnoi->prepare($sql);
        $stmt->bind_param("iiii", $soLuong, $maDH, $maSP, $maSize);
        
        if ($stmt->execute()) {
            // Cập nhật tổng tiền
            updateOrderTotal($ketnoi, $maDH);
            
            // Lấy thông tin mới
            $sql = "SELECT ct.SoLuong, sps.Gia as GiaSP, dh.TongTien
                    FROM chitietdonhang ct
                    INNER JOIN sanpham_size sps ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize
                    INNER JOIN donhang dh ON ct.MaDH = dh.MaDH
                    WHERE ct.MaDH = ? AND ct.MaSP = ? AND ct.MaSize = ?";
            $stmt = $ketnoi->prepare($sql);
            $stmt->bind_param("iii", $maDH, $maSP, $maSize);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã cập nhật số lượng!',
                'soLuong' => $result['SoLuong'],
                'thanhTien' => $result['SoLuong'] * $result['GiaSP'],
                'tongTien' => $result['TongTien']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại!']);
        }
        exit;
    }
    
    if ($_POST['ajax_action'] == 'delete_product') {
        $maDH = $_POST['maDH'];
        $maSP = $_POST['maSP'];
        $maSize = $_POST['maSize'] ?? null;
        
        $sql = "DELETE FROM chitietdonhang WHERE MaDH = ? AND MaSP = ? AND MaSize = ?";
        $stmt = $ketnoi->prepare($sql);
        $stmt->bind_param("iii", $maDH, $maSP, $maSize);
        
        if ($stmt->execute()) {
            updateOrderTotal($ketnoi, $maDH);
            
            // Lấy tổng tiền mới
            $sql = "SELECT TongTien FROM donhang WHERE MaDH = ?";
            $stmt = $ketnoi->prepare($sql);
            $stmt->bind_param("i", $maDH);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã xóa sản phẩm!',
                'tongTien' => $result['TongTien']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Xóa thất bại!']);
        }
        exit;
    }
}

$maDH = $_GET['id'] ?? 0;

// Hàm cập nhật tổng tiền đơn hàng
function updateOrderTotal($ketnoi, $maDH) {
    $sql = "SELECT SUM(ct.SoLuong * sps.Gia) as TongTien
            FROM chitietdonhang ct
            INNER JOIN sanpham_size sps ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize
            WHERE ct.MaDH = ?";
    
    $stmt = $ketnoi->prepare($sql);
    $stmt->bind_param("i", $maDH);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $tongTien = $row['TongTien'] ?? 0;
    
    $sql = "UPDATE donhang SET TongTien = ? WHERE MaDH = ?";
    $stmt = $ketnoi->prepare($sql);
    $stmt->bind_param("di", $tongTien, $maDH);
    $stmt->execute();
}

// Lấy thông tin đơn hàng
$sql = "SELECT dh.*, kh.HoTen 
        FROM donhang dh
        LEFT JOIN khachhang kh ON dh.MaKH = kh.MaKH
        WHERE dh.MaDH = ?";
$stmt = $ketnoi->prepare($sql);
$stmt->bind_param("i", $maDH);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Không tìm thấy đơn hàng!");
}

// Lấy chi tiết sản phẩm
$sql = "SELECT 
            ct.*,
            sp.TenSP ,
            sps.Gia as GiaSP,
            sps.Anh as AnhSP,
            s.TenSize
        FROM chitietdonhang ct
        INNER JOIN sanpham sp ON ct.MaSP = sp.MaSP
        INNER JOIN sanpham_size sps ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize
        LEFT JOIN size s ON ct.MaSize = s.MaSize
        WHERE ct.MaDH = ?
        ORDER BY ct.id";
$stmt = $ketnoi->prepare($sql);
$stmt->bind_param("i", $maDH);
$stmt->execute();
$details = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $order['MaDHcode']; ?></title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4CAF50;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            padding: 10px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            color: #333;
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
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .product-details {
            flex: 1;
        }
        
        .product-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .product-size {
            color: #666;
            font-size: 14px;
        }
        
        .quantity-input {
            width: 70px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
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
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .total-section {
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
        }
        
        .total-amount {
            font-size: 24px;
            font-weight: 700;
            color: #e74c3c;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        .guest-badge {
            background: #6c757d;
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 8px;
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .loading {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 5px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php include '../../navbar_admin.php'; ?>
    <div class="container">

        <div class="header">
            <h1>📋 Chi tiết đơn hàng #<?php echo $order['MaDHcode']; ?></h1>
            <a href="order_list.php" class="back-btn">← Quay lại</a>
        </div>
        
        <div id="alertContainer"></div>
        
        <div class="order-info">
            <h2 style="margin-bottom: 20px;">Thông tin đơn hàng</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Khách hàng:</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($order['HoTen'] ?? 'Khách lẻ'); ?>
                        <?php if ($order['is_guest'] == 1): ?>
                            <span class="guest-badge">Khách vãng lai</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Số điện thoại:</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['sdtnguoinhan']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Địa chỉ giao hàng:</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['diachinguoinhan']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ngày đặt:</div>
                    <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['ngaydat'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Trạng thái:</div>
                    <div class="info-value"><strong><?php echo htmlspecialchars($order['trangthai']); ?></strong></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Phương thức thanh toán:</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['phuongthucthanhtoan'] ?? 'Tiền mặt'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Trạng thái thanh toán:</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['trangthaithanhtoan'] ?? 'Chưa thanh toán'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ghi chú:</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['ghichu'] ?? 'Không có'); ?></div>
                </div>
            </div>
        </div>
        
        <h2>Sản phẩm trong đơn hàng</h2>
        
        <?php if ($details->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $details->fetch_assoc()): ?>
                        <tr id="row-<?php echo $item['MaSP']; ?>-<?php echo $item['MaSize']; ?>">
                            <td>
                                <div class="product-info">
                                    <img src="/unitop/backend/lesson/school/project_pizza/<?php echo htmlspecialchars($item['AnhSP']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['TenSP']); ?>" 
                                         class="product-image"
                                         onerror="this.src='../../../assets/images/no-image.png'">
                                    
                                    <div class="product-details">
                                        <div class="product-name">
                                            <?php echo htmlspecialchars($item['TenSP']); ?>
                                        </div>
                                        <?php if ($item['TenSize']): ?>
                                            <div class="product-size">Size: <?php echo htmlspecialchars($item['TenSize']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="price-cell"><?php echo number_format($item['GiaSP'], 0, ',', '.'); ?>đ</td>
                            <td>
                                <input type="number" 
                                       class="quantity-input" 
                                       value="<?php echo $item['SoLuong']; ?>" 
                                       min="1"
                                       data-madh="<?php echo $maDH; ?>"
                                       data-masp="<?php echo $item['MaSP']; ?>"
                                       data-masize="<?php echo $item['MaSize']; ?>"
                                       data-price="<?php echo $item['GiaSP']; ?>">
                                <button class="btn btn-primary btn-update" 
                                        data-madh="<?php echo $maDH; ?>"
                                        data-masp="<?php echo $item['MaSP']; ?>"
                                        data-masize="<?php echo $item['MaSize']; ?>">
                                    Cập nhật
                                </button>
                            </td>
                            <td class="subtotal-cell">
                                <strong style="color: #e74c3c;">
                                    <?php 
                                    $Gia = $item['SoLuong'] * $item['GiaSP'];
                                    echo number_format($Gia, 0, ',', '.'); 
                                    ?>đ
                                </strong>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-delete"
                                        data-madh="<?php echo $maDH; ?>"
                                        data-masp="<?php echo $item['MaSP']; ?>"
                                        data-masize="<?php echo $item['MaSize']; ?>">
                                    🗑 Xóa
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="total-section">
                <h3>Tổng tiền: <span class="total-amount" id="totalAmount"><?php echo number_format($order['TongTien'], 0, ',', '.'); ?>đ</span></h3>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <h3>Chưa có sản phẩm nào trong đơn hàng</h3>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Hàm hiển thị thông báo
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = (type === 'success' ? '✓ ' : '✗ ') + message;
            
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }

        // Hàm format số tiền
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        }

        // Xử lý cập nhật số lượng
        document.querySelectorAll('.btn-update').forEach(btn => {
            btn.addEventListener('click', async function() {
                const maDH = this.dataset.madh;
                const maSP = this.dataset.masp;
                const maSize = this.dataset.masize;
                const row = document.getElementById(`row-${maSP}-${maSize}`);
                const input = row.querySelector('.quantity-input');
                const soLuong = input.value;
                
                if (soLuong < 1) {
                    showAlert('Số lượng phải lớn hơn 0!', 'error');
                    return;
                }
                
                // Disable button và hiển thị loading
                this.disabled = true;
                const originalText = this.textContent;
                this.innerHTML = 'Đang xử lý...<span class="loading"></span>';
                
                try {
                    const formData = new FormData();
                    formData.append('ajax_action', 'update_quantity');
                    formData.append('maDH', maDH);
                    formData.append('maSP', maSP);
                    formData.append('maSize', maSize);
                    formData.append('soLuong', soLuong);
                    
                    const response = await fetch('order_detail.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Cập nhật thành tiền
                        row.querySelector('.subtotal-cell strong').textContent = formatMoney(data.thanhTien);
                        
                        // Cập nhật tổng tiền
                        document.getElementById('totalAmount').textContent = formatMoney(data.tongTien);
                        
                        showAlert(data.message);
                    } else {
                        showAlert(data.message, 'error');
                    }
                } catch (error) {
                    showAlert('Có lỗi xảy ra!', 'error');
                    console.error(error);
                } finally {
                    this.disabled = false;
                    this.textContent = originalText;
                }
            });
        });

        // Xử lý xóa sản phẩm
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', async function() {
                if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                    return;
                }
                
                const maDH = this.dataset.madh;
                const maSP = this.dataset.masp;
                const maSize = this.dataset.masize;
                const row = document.getElementById(`row-${maSP}-${maSize}`);
                
                // Disable button
                this.disabled = true;
                const originalText = this.textContent;
                this.innerHTML = 'Đang xóa...<span class="loading"></span>';
                
                try {
                    const formData = new FormData();
                    formData.append('ajax_action', 'delete_product');
                    formData.append('maDH', maDH);
                    formData.append('maSP', maSP);
                    formData.append('maSize', maSize);
                    
                    const response = await fetch('order_detail.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Xóa dòng với animation
                        row.style.animation = 'slideIn 0.3s ease reverse';
                        setTimeout(() => {
                            row.remove();
                            
                            // Cập nhật tổng tiền
                            document.getElementById('totalAmount').textContent = formatMoney(data.tongTien);
                            
                            // Kiểm tra nếu không còn sản phẩm
                            const tbody = document.querySelector('tbody');
                            if (tbody.children.length === 0) {
                                location.reload();
                            }
                        }, 300);
                        
                        showAlert(data.message);
                    } else {
                        showAlert(data.message, 'error');
                        this.disabled = false;
                        this.textContent = originalText;
                    }
                } catch (error) {
                    showAlert('Có lỗi xảy ra!', 'error');
                    console.error(error);
                    this.disabled = false;
                    this.textContent = originalText;
                }
            });
        });

        // Cho phép nhấn Enter để cập nhật
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const row = this.closest('tr');
                    row.querySelector('.btn-update').click();
                }
            });
        });
    </script>
</body>
</html>