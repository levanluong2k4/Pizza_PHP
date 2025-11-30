<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}
// Top regions by orders or total spent
require __DIR__ . '/../../../includes/db_connect.php';
if (!$ketnoi) die('Kết nối thất bại: ' . mysqli_connect_error());

$groupBy = isset($_GET['group']) ? $_GET['group'] : 'tinhthanhpho'; // tinhthanhpho | huyenquan | xaphuong
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

// defensive: ensure column exists in khachhang
$validCols = array('tinhthanhpho','huyenquan','xaphuong');
if (!in_array($groupBy, $validCols)) $groupBy = 'tinhthanhpho';

// check donhang has is_guest
$has_is_guest = false;
$res_cols = mysqli_query($ketnoi, "SHOW COLUMNS FROM donhang");
if ($res_cols) {
    while ($c = mysqli_fetch_assoc($res_cols)) {
        if ($c['Field'] === 'is_guest') $has_is_guest = true;
    }
}
$guestCond = $has_is_guest ? " AND d.is_guest = 0" : "";

$sql = "SELECT k.$groupBy AS region, COUNT(DISTINCT d.MaDH) AS orders_count, COALESCE(SUM(d.TongTien),0) AS total_spent ";
$sql .= "FROM khachhang k LEFT JOIN donhang d ON d.MaKH = k.MaKH" . $guestCond . " ";
$sql .= "GROUP BY k.$groupBy ORDER BY total_spent DESC LIMIT " . intval($limit);

$res = mysqli_query($ketnoi, $sql);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Khu vực khách hàng mua nhiều nhất</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../../navbar_admin.php'; ?>
<div class="container mt-4">
    <h3>Khu vực khách hàng mua nhiều nhất</h3>
    <form class="row g-2 mb-3" method="get">
        <div class="col-auto">
            <select name="group" class="form-select">
                <option value="tinhthanhpho" <?php echo $groupBy === 'tinhthanhpho' ? 'selected' : ''; ?>>Tỉnh / Thành phố</option>
                <option value="huyenquan" <?php echo $groupBy === 'huyenquan' ? 'selected' : ''; ?>>Huyện / Quận</option>
                <option value="xaphuong" <?php echo $groupBy === 'xaphuong' ? 'selected' : ''; ?>>Xã / Phường</option>
            </select>
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
                    <th>Khu vực</th>
                    <th>Số đơn</th>
                    <th>Tổng chi (VNĐ)</th>
                </tr>
            </thead>
            <tbody>
<?php
if ($res && mysqli_num_rows($res) > 0) {
    $i = 1;
    while ($row = mysqli_fetch_assoc($res)) {
        echo '<tr>';
        echo '<td>' . $i++ . '</td>';
        echo '<td>' . htmlspecialchars($row['region'] ?: 'Chưa cập nhật') . '</td>';
        echo '<td>' . number_format($row['orders_count']) . '</td>';
        echo '<td>' . number_format($row['total_spent'],0,'.',',') . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4" class="text-center text-muted">Không có kết quả</td></tr>';
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
