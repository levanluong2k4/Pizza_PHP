<?php
session_start();
// Top customers by total spent / orders / items
// Simple admin page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}
if($_SESSION['phanquyen'] != 0){
    echo "Bạn không có quyền truy cập trang này.";
    exit();
}
require __DIR__ . '/../../../includes/db_connect.php';
if (!$ketnoi) die('Kết nối thất bại: ' . mysqli_connect_error());

// params
$start = isset($_GET['start']) ? $_GET['start'] : '';
$end = isset($_GET['end']) ? $_GET['end'] : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$limit = $limit > 0 ? $limit : 50;

// build date condition if provided
$dateCond = '';
if (!empty($start)) {
    $s = mysqli_real_escape_string($ketnoi, $start);
    $dateCond .= " AND d.ngaydat >= '" . $s . "'";
}
if (!empty($end)) {
    $e = mysqli_real_escape_string($ketnoi, $end);
    $dateCond .= " AND d.ngaydat <= '" . $e . "'";
}

// check columns
$has_is_guest = false;
$res_cols = mysqli_query($ketnoi, "SHOW COLUMNS FROM donhang");
if ($res_cols) {
    while ($c = mysqli_fetch_assoc($res_cols)) {
        if ($c['Field'] === 'is_guest') $has_is_guest = true;
    }
}
$guestCond = $has_is_guest ? " AND d.is_guest = 0" : "";

// Main query: aggregate per customer
$sql = "SELECT k.MaKH, k.HoTen, k.Email, k.SoDT, ";
$sql .= "COUNT(DISTINCT d.MaDH) AS orders_count, ";
$sql .= "COALESCE(SUM(d.TongTien),0) AS total_spent, ";
$sql .= "COALESCE(SUM(ct.SoLuong),0) AS items_count ";
$sql .= "FROM khachhang k ";
$sql .= "LEFT JOIN donhang d ON d.MaKH = k.MaKH";
$sql .= " AND 1=1" . $dateCond . $guestCond . " ";
$sql .= "LEFT JOIN chitietdonhang ct ON ct.MaDH = d.MaDH ";
$sql .= "GROUP BY k.MaKH ";
$sql .= "ORDER BY total_spent DESC ";
$sql .= "LIMIT " . intval($limit);

$res = mysqli_query($ketnoi, $sql);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Khách hàng mua nhiều nhất</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../../navbar_admin.php'; ?>
<div class="container mt-4">
    <h3>Khách hàng mua nhiều nhất</h3>
    <form class="row g-2 mb-3" method="get">
        <div class="col-auto">
            <input type="date" name="start" class="form-control" value="<?php echo htmlspecialchars($start); ?>" placeholder="Từ" />
        </div>
        <div class="col-auto">
            <input type="date" name="end" class="form-control" value="<?php echo htmlspecialchars($end); ?>" placeholder="Đến" />
        </div>
        <div class="col-auto">
            <input type="number" name="limit" class="form-control" value="<?php echo intval($limit); ?>" min="1" />
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Lọc</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>MaKH</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>SDT</th>
                    <th>Số đơn</th>
                    <th>Tổng chi (VNĐ)</th>
                    <th>Tống sản phẩm</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
<?php
if ($res && mysqli_num_rows($res) > 0) {
    $i = 1;
    while ($row = mysqli_fetch_assoc($res)) {
        echo '<tr>';
        echo '<td>' . $i++ . '</td>';
        echo '<td>' . htmlspecialchars($row['MaKH']) . '</td>';
        echo '<td>' . htmlspecialchars($row['HoTen']) . '</td>';
        echo '<td>' . htmlspecialchars($row['Email']) . '</td>';
        echo '<td>' . htmlspecialchars($row['SoDT']) . '</td>';
        echo '<td>' . number_format($row['orders_count']) . '</td>';
        echo '<td>' . number_format($row['total_spent'],0,'.',',') . '</td>';
        echo '<td>' . number_format($row['items_count']) . '</td>';
        echo '<td><a class="btn btn-sm btn-primary" href="/unitop/backend/lesson/school/project_pizza/admin/view/customer/detail_customer.php?MaKH=' . urlencode($row['MaKH']) . '">Chi tiết</a></td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="9" class="text-center text-muted">Không có kết quả</td></tr>';
}
?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php mysqli_close($ketnoi); ?>
