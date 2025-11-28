<?php
session_start();
/*
// Kiểm tra quyền admin (nếu cần)
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}
*/

// Kết nối DB
require __DIR__ . '/../../../includes/db_connect.php';
if (!$ketnoi) die('Kết nối thất bại: ' . mysqli_connect_error());

$MaKH = isset($_GET['MaKH']) ? intval($_GET['MaKH']) : 0;
if ($MaKH <= 0) {
    echo '<p class="alert alert-danger">MaKH không hợp lệ.</p>';
    exit;
}

// Lấy thông tin khách
$sql = "SELECT * FROM khachhang WHERE MaKH = $MaKH LIMIT 1";
$res = mysqli_query($ketnoi, $sql);
$user = $res ? mysqli_fetch_assoc($res) : null;
if (!$user) {
    echo '<p class="alert alert-warning">Không tìm thấy khách hàng.</p>';
    exit;
}

// Thống kê đơn hàng liên quan (non-guest nếu có cột)
// An toàn: kiểm tra cột tồn tại
$has_is_guest = false;
$has_tongtien = false;
$res_cols = mysqli_query($ketnoi, "SHOW COLUMNS FROM donhang");
if ($res_cols) {
    while ($c = mysqli_fetch_assoc($res_cols)) {
        if ($c['Field'] === 'is_guest') $has_is_guest = true;
        if ($c['Field'] === 'TongTien') $has_tongtien = true;
    }
}

$where = "MaKH = $MaKH";
if ($has_is_guest) $where .= " AND is_guest = 0";

$orders_count = 0;
$total_spent = 0.0;
$q1 = "SELECT COUNT(*) AS cnt, COALESCE(SUM(TongTien),0) AS total FROM donhang WHERE $where";
$r1 = mysqli_query($ketnoi, $q1);
if ($r1) {
    $row = mysqli_fetch_assoc($r1);
    $orders_count = intval($row['cnt']);
    $total_spent = floatval($row['total']);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chi tiết khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../../navbar_admin.php'; ?>
<div class="container mt-4">
    <a href="list_customer.php" class="btn btn-secondary mb-3">← Quay lại</a>
    <div class="card">
        <div class="card-header">
            <h5>Chi tiết khách hàng: <?php echo htmlspecialchars($user['HoTen'] ?? ''); ?> (ID: <?php echo $user['MaKH']; ?>)</h5>
        </div>
        <div class="card-body">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email'] ?? ''); ?></p>
            <p><strong>Số ĐT:</strong> <?php echo htmlspecialchars($user['SoDT'] ?? ''); ?></p>
            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars(implode(', ', array_filter([$user['sonha'] ?? '', $user['xaphuong'] ?? '', $user['huyenquan'] ?? '', $user['tinhthanhpho'] ?? ''])) ?: 'Chưa cập nhật'); ?></p>
            <p><strong>Số đơn (đã lọc):</strong> <?php echo number_format($orders_count); ?></p>
            <p><strong>Tổng chi (VNĐ):</strong> <?php echo number_format($total_spent, 0, '.', ','); ?></p>

            <hr>
            <h6>Đơn hàng gần đây</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
<?php
// Build select list & headers dynamically based on actual columns in donhang
$dh_cols = array();
$res_cols2 = mysqli_query($ketnoi, "SHOW COLUMNS FROM donhang");
if ($res_cols2) {
    while ($c = mysqli_fetch_assoc($res_cols2)) {
        $dh_cols[] = $c['Field'];
    }
}
$selectCols = array();
$headers = array();
// ensure MaDH present
if (in_array('MaDH', $dh_cols)) { $selectCols[] = 'MaDH'; $headers[] = 'MaDH'; }
if (in_array('MaDHcode', $dh_cols)) { $selectCols[] = 'MaDHcode'; $headers[] = 'MaDHcode'; }
if (in_array('ngaydat', $dh_cols)) { $selectCols[] = 'ngaydat'; $headers[] = 'ngaydat'; }
if (in_array('TongTien', $dh_cols)) { $selectCols[] = 'TongTien'; $headers[] = 'TongTien'; }
if (in_array('trangthai', $dh_cols)) { $selectCols[] = 'trangthai'; $headers[] = 'trangthai'; }
// fallback if nothing found
if (empty($selectCols)) { $selectCols = array('MaDH','ngaydat'); $headers = array('MaDH','ngaydat'); }
$selectList = implode(',', $selectCols);
?>
                        <tr>
<?php foreach ($headers as $h): ?>
    <th><?php echo htmlspecialchars($h); ?></th>
<?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
<?php
$q2 = "SELECT $selectList FROM donhang WHERE $where ORDER BY ngaydat DESC LIMIT 50";
$r2 = mysqli_query($ketnoi, $q2);
if ($r2 && mysqli_num_rows($r2) > 0) {
    while ($row = mysqli_fetch_assoc($r2)) {
        echo '<tr>';
        foreach ($selectCols as $col) {
            $val = $row[$col] ?? '';
            if ($col === 'TongTien') $out = number_format(floatval($val),0,'.',',');
            else $out = htmlspecialchars($val);
            echo '<td>' . $out . '</td>';
        }
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="' . max(1,count($headers)) . '" class="text-center text-muted">Không có đơn hàng</td></tr>';
}
?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php mysqli_close($ketnoi); ?>
