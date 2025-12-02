<?php
session_start();

// kiểm tra quyền admin
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

// group theo địa phận
$groupBy = isset($_GET['group']) ? $_GET['group'] : 'tinhthanhpho'; 
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

// chỉ cho phép 3 trường
$validCols = array('tinhthanhpho','huyenquan','xaphuong');
if (!in_array($groupBy, $validCols)) $groupBy = 'tinhthanhpho';

// check guest
$has_is_guest = false;
$res_cols = mysqli_query($ketnoi, "SHOW COLUMNS FROM donhang");
if ($res_cols) {
    while ($c = mysqli_fetch_assoc($res_cols)) {
        if ($c['Field'] === 'is_guest') $has_is_guest = true;
    }
}
$guestCond = $has_is_guest ? " AND d.is_guest = 0" : "";

// ===========================
// SQL tùy theo mức địa chỉ
// ===========================
if ($groupBy === 'xaphuong') {

    $sql = "
        SELECT 
            COALESCE(k.xaphuong, '') AS region,
            COALESCE(k.huyenquan, '') AS district,
            COALESCE(k.tinhthanhpho, '') AS province,
            COUNT(DISTINCT d.MaDH) AS orders_count,
            COALESCE(SUM(d.TongTien),0) AS total_spent
        FROM khachhang k
        LEFT JOIN donhang d ON d.MaKH = k.MaKH $guestCond
        GROUP BY k.xaphuong, k.huyenquan, k.tinhthanhpho
        ORDER BY total_spent DESC
        LIMIT $limit
    ";

} elseif ($groupBy === 'huyenquan') {

    $sql = "
        SELECT 
            COALESCE(k.huyenquan, '') AS region,
            COALESCE(k.tinhthanhpho, '') AS province,
            COUNT(DISTINCT d.MaDH) AS orders_count,
            COALESCE(SUM(d.TongTien),0) AS total_spent
        FROM khachhang k
        LEFT JOIN donhang d ON d.MaKH = k.MaKH $guestCond
        GROUP BY k.huyenquan, k.tinhthanhpho
        ORDER BY total_spent DESC
        LIMIT $limit
    ";

} else {

    $sql = "
        SELECT 
            COALESCE(k.tinhthanhpho,'') AS region,
            COUNT(DISTINCT d.MaDH) AS orders_count,
            COALESCE(SUM(d.TongTien),0) AS total_spent
        FROM khachhang k
        LEFT JOIN donhang d ON d.MaKH = k.MaKH $guestCond
        GROUP BY k.tinhthanhpho
        ORDER BY total_spent DESC
        LIMIT $limit
    ";
}

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

    <!-- Lọc -->
    <form class="row g-2 mb-3" method="get">
        <div class="col-auto">
            <select name="group" class="form-select">
                <option value="tinhthanhpho" <?= $groupBy === 'tinhthanhpho' ? 'selected' : '' ?>>Tỉnh / Thành phố</option>
                <option value="huyenquan" <?= $groupBy === 'huyenquan' ? 'selected' : '' ?>>Huyện / Quận</option>
                <option value="xaphuong" <?= $groupBy === 'xaphuong' ? 'selected' : '' ?>>Xã / Phường</option>
            </select>
        </div>
        <div class="col-auto">
            <input type="number" name="limit" class="form-control" value="<?= intval($limit) ?>" min="1" />
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Lọc</button>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>#</th>

                    <!-- Cột địa chỉ chính -->
                    <?php if ($groupBy === 'xaphuong'): ?>
                        <th>Xã / Phường</th>
                        <th>Huyện / Quận</th>
                        <th>Tỉnh / Thành phố</th>
                    <?php elseif ($groupBy === 'huyenquan'): ?>
                        <th>Huyện / Quận</th>
                        <th>Tỉnh / Thành phố</th>
                    <?php else: ?>
                        <th>Tỉnh / Thành phố</th>
                    <?php endif; ?>

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

        // render từng loại
        if ($groupBy === 'xaphuong') {
            echo '<td>' . ($row['region'] ?: '---') . '</td>';
            echo '<td>' . ($row['district'] ?: '---') . '</td>';
            echo '<td>' . ($row['province'] ?: '---') . '</td>';
        }
        elseif ($groupBy === 'huyenquan') {
            echo '<td>' . ($row['region'] ?: '---') . '</td>';
            echo '<td>' . ($row['province'] ?: '---') . '</td>';
        }
        else {
            echo '<td>' . ($row['region'] ?: '---') . '</td>';
        }

        echo '<td>' . number_format($row['orders_count']) . '</td>';
        echo '<td>' . number_format($row['total_spent'],0,'.',',') . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6" class="text-center text-muted">Không có dữ liệu</td></tr>';
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
