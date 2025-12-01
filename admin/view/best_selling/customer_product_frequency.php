<?php
require __DIR__ . '/../../../includes/db_connect.php';

// Query to get most frequently purchased products for each customer
$sql = "
    SELECT
        k.HoTen,
        k.Email,
        sp.TenSP,
        sp.Anh,
        SUM(ct.SoLuong) AS tong_so_luong,
        COUNT(DISTINCT d.MaDH) AS so_don_hang,
        GROUP_CONCAT(DISTINCT s.TenSize ORDER BY s.TenSize SEPARATOR ', ') AS kich_thuoc
    FROM khachhang k
    JOIN donhang d ON k.MaKH = d.MaKH
    JOIN chitietdonhang ct ON d.MaDH = ct.MaDH
    JOIN sanpham sp ON ct.MaSP = sp.MaSP
    LEFT JOIN size s ON ct.MaSize = s.MaSize
    WHERE d.trangthai = 'Giao thành công'
    GROUP BY k.MaKH, k.HoTen, k.Email, sp.MaSP, sp.TenSP, sp.Anh
    ORDER BY k.HoTen, tong_so_luong DESC
";

$result = mysqli_query($ketnoi, $sql);
$customer_products = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Group by customer for display
$customers_data = [];
foreach ($customer_products as $row) {
    $customer_name = $row['HoTen'];
    if (!isset($customers_data[$customer_name])) {
        $customers_data[$customer_name] = [
            'email' => $row['Email'],
            'products' => []
        ];
    }
    $customers_data[$customer_name]['products'][] = $row;
}
?>

<?php include '../../../admin/navbar_admin.php'; ?>

<style>
    .main-title { color: #28a745; font-weight: 700; margin-top: 40px; text-align: center; }
    .customer-section { background: white; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); margin-bottom: 30px; padding: 20px; }
    .customer-name { color: #28a745; font-size: 24px; font-weight: bold; margin-bottom: 15px; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
    .customer-email { color: #666; font-style: italic; margin-bottom: 20px; }
    .product-card { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f9f9f9; }
    .product-image { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; }
    .product-info { margin-left: 15px; }
    .product-name { font-weight: bold; color: #333; margin-bottom: 5px; }
    .product-stats { color: #666; font-size: 14px; }
    .no-data { text-align: center; color: #666; font-style: italic; padding: 40px; }
</style>

<div class="container mt-5">
    <h2 class="main-title"><i class="fa-solid fa-users"></i> Sản phẩm khách hàng mua nhiều nhất</h2>

    <?php if (empty($customers_data)): ?>
        <div class="no-data">
            <i class="fa-solid fa-info-circle fa-3x mb-3"></i>
            <p>Không có dữ liệu khách hàng nào.</p>
        </div>
    <?php else: ?>
        <?php foreach ($customers_data as $customer_name => $customer_data): ?>
            <div class="customer-section">
                <div class="customer-name">
                    <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($customer_name); ?>
                </div>
                <div class="customer-email">
                    <i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($customer_data['email']); ?>
                </div>

                <div class="row">
                    <?php foreach ($customer_data['products'] as $product): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="product-card">
                                <div class="d-flex align-items-center">
                                    <img src="../../../<?php echo htmlspecialchars($product['Anh']); ?>"
                                         alt="<?php echo htmlspecialchars($product['TenSP']); ?>"
                                         class="product-image">
                                    <div class="product-info flex-grow-1">
                                        <div class="product-name"><?php echo htmlspecialchars($product['TenSP']); ?></div>
                                        <div class="product-stats">
                                            <div><i class="fa-solid fa-shopping-cart"></i> Tổng số lượng: <?php echo number_format($product['tong_so_luong']); ?></div>
                                            <div><i class="fa-solid fa-receipt"></i> Số đơn hàng: <?php echo number_format($product['so_don_hang']); ?></div>
                                            <?php if ($product['kich_thuoc']): ?>
                                                <div><i class="fa-solid fa-expand"></i> Kích thước: <?php echo htmlspecialchars($product['kich_thuoc']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<?php mysqli_close($ketnoi); ?>
</body>
</html>
