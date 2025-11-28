<?php
session_start();
// include admin navbar

// Kết nối database (giống create_account.php)
require __DIR__ . '/../../../includes/db_connect.php';
if (!$ketnoi) { die("Kết nối thất bại: " . mysqli_connect_error()); }

// Pagination & search
$perPage = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Build where for search
$where = "1";
if ($q !== '') {
    $esc = mysqli_real_escape_string($ketnoi, $q);
    $where = "(k.HoTen LIKE '%$esc%' OR k.Email LIKE '%$esc%' OR k.SoDT LIKE '%$esc%')";
}

// Total customers count for pagination
$sql_count = "SELECT COUNT(*) AS cnt FROM khachhang k WHERE $where";
$res_count = mysqli_query($ketnoi, $sql_count);
$row_count = mysqli_fetch_assoc($res_count);
$totalCount = intval($row_count['cnt']);
$totalPages = max(1, ceil($totalCount / $perPage));

// Determine which columns exist in donhang to avoid Unknown column errors
$donhang_cols = array();
$res_cols = mysqli_query($ketnoi, "SHOW COLUMNS FROM donhang");
if ($res_cols) {
    while ($c = mysqli_fetch_assoc($res_cols)) {
        $donhang_cols[] = $c['Field'];
    }
}
$has_is_guest = in_array('is_guest', $donhang_cols);
$has_paid = in_array('trangthaithanhtoan', $donhang_cols);
$has_tongtien = in_array('TongTien', $donhang_cols);

// Build condition used to count/sum orders safely
$conds = array();
if ($has_is_guest) $conds[] = "d.is_guest = 0";
if ($has_paid) $conds[] = "d.trangthaithanhtoan = 'dathanhtoan'";
if ($has_tongtien) $conds[] = "d.TongTien > 0";
$cond_str = implode(' AND ', $conds);

// Build select expressions safely
if ($has_tongtien) {
    if ($cond_str !== '') {
        $sumExpr = "COALESCE(SUM(CASE WHEN $cond_str THEN d.TongTien ELSE 0 END),0) AS total_spent";
        $countExpr = "COUNT(DISTINCT CASE WHEN $cond_str THEN d.MaDH END) AS orders_count";
    } else {
        $sumExpr = "COALESCE(SUM(d.TongTien),0) AS total_spent";
        $countExpr = "COUNT(DISTINCT d.MaDH) AS orders_count";
    }
} else {
    // TongTien missing: cannot sum amounts
    $sumExpr = "0 AS total_spent";
    if ($cond_str !== '') {
        $countExpr = "COUNT(DISTINCT CASE WHEN $cond_str THEN d.MaDH END) AS orders_count";
    } else {
        $countExpr = "COUNT(DISTINCT d.MaDH) AS orders_count";
    }
}

// Use simple LEFT JOIN and conditional aggregation to avoid referencing non-existent columns in ON clause
$sql = "SELECT k.MaKH, k.HoTen, k.Email, k.SoDT, k.tinhthanhpho, k.huyenquan, k.xaphuong, \n            $sumExpr, \n            $countExpr\n        FROM khachhang k\n        LEFT JOIN donhang d ON d.MaKH = k.MaKH\n        WHERE $where\n        GROUP BY k.MaKH, k.HoTen, k.Email, k.SoDT, k.tinhthanhpho, k.huyenquan, k.xaphuong\n        ORDER BY total_spent DESC, k.MaKH DESC\n        LIMIT $perPage OFFSET $offset";

$result = mysqli_query($ketnoi, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body>

<?php include '../../navbar_admin.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Danh sách khách hàng</h3>
        <form class="d-flex" method="GET">
            <input class="form-control me-2" type="search" name="q" placeholder="Tìm tên, email, số điện thoại" value="<?php echo htmlspecialchars($q); ?>">
            <button class="btn btn-outline-success" type="submit"><i class="fa-solid fa-search"></i></button>
        </form>
    </div>

    <div class="card mb-4">
        <div class="card-body table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>SDT</th>
                        <th>Thành phố / Huyện / Xã</th>
                        <th>Số đơn</th>
                        <th>Tổng chi (VNĐ)</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($r = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $r['MaKH']; ?></td>
                                <td><?php echo htmlspecialchars($r['HoTen']); ?></td>
                                <td><?php echo htmlspecialchars($r['Email']); ?></td>
                                <td><?php echo htmlspecialchars($r['SoDT']); ?></td>
                                <td><?php echo htmlspecialchars(trim(($r['tinhthanhpho'] ?: 'Chưa cập nhật') . ' / ' . ($r['huyenquan'] ?: 'Chưa cập nhật') . ' / ' . ($r['xaphuong'] ?: 'Chưa cập nhật'))); ?></td>
                                <td><?php echo number_format(intval($r['orders_count'])); ?></td>
                                <td><?php echo number_format(floatval($r['total_spent']), 0, '.', ','); ?></td>
                                <td>
                                    <a href="/unitop/backend/lesson/school/project_pizza/admin/view/customer/detail_customer.php?MaKH=<?php echo $r['MaKH']; ?>" class="btn btn-sm btn-primary">Chi tiết</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted">Chưa có khách hàng</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $p; ?><?php echo $q !== '' ? '&q=' . urlencode($q) : ''; ?>"><?php echo $p; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php mysqli_close($ketnoi); ?>
