<?php
require __DIR__ . '/../../includes/db_connect.php';

$sql = "SELECT ss.id, ss.Gia, sp.TenSP, ss.Anh, s.TenSize, sp.MaSP, s.MaSize
        FROM sanpham_size ss, sanpham sp, size s
        WHERE ss.MaSP = sp.MaSP
        AND ss.MaSize = s.MaSize
        ORDER BY sp.TenSP, s.TenSize";
$result = mysqli_query($ketnoi, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách giá sản phẩm theo size - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .table th { background-color: #28a745; color: white; }
        .btn-edit { background-color: #ffc107; border: none; }
        .btn-delete { background-color: #dc3545; border: none; }
        .product-image { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>
    <?php include '../navbar_admin.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4"><i class="fa-solid fa-tags"></i> Danh sách giá sản phẩm theo size</h2>
        <div class="d-flex justify-content-between mb-3">
            <a href="add_product_price.php" class="btn btn-success"><i class="fa-solid fa-plus"></i> Thêm giá sản phẩm</a>
            <a href="list_sizes.php" class="btn btn-primary"><i class="fa-solid fa-ruler-combined"></i> Quản lý size</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Size</th>
                        <th>Giá (VNĐ)</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><img src="../../<?php echo $row['Anh']; ?>" alt="Ảnh sản phẩm" class="product-image"></td>
                            <td><?php echo $row['TenSP']; ?></td>
                            <td><?php echo $row['TenSize']; ?></td>
                            <td><?php echo number_format($row['Gia'], 0, ',', '.'); ?> VNĐ</td>
                            <td>
                                <button class="btn btn-edit btn-sm me-2" onclick="editPrice(<?php echo $row['id']; ?>, '<?php echo $row['TenSP']; ?>', '<?php echo $row['TenSize']; ?>', <?php echo $row['Gia']; ?>)"><i class="fa-solid fa-edit"></i> Sửa</button>
                                <a href="../process/delete.php?id=<?php echo $row['id']; ?>" class="btn btn-delete btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa giá sản phẩm này?');"><i class="fa-solid fa-trash"></i> Xóa</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Sửa giá -->
    <div class="modal fade" id="editPriceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-edit"></i> Sửa giá sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../process/update_process.php" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="ma">
                        <div class="mb-3">
                            <label class="form-label">Sản phẩm:</label>
                            <p id="editProductName" class="form-control-plaintext"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Size:</label>
                            <p id="editSizeName" class="form-control-plaintext"></p>
                        </div>
                        <div class="mb-3">
                            <label for="editGia" class="form-label">Giá mới (VNĐ)</label>
                            <input type="number" class="form-control" id="editGia" name="gia" min="0" step="1000" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAnh" class="form-label">Ảnh mới (tùy chọn)</label>
                            <input type="file" class="form-control" id="editAnh" name="Anhnew" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editPrice(id, productName, sizeName, gia) {
            document.getElementById('editId').value = id;
            document.getElementById('editProductName').textContent = productName;
            document.getElementById('editSizeName').textContent = sizeName;
            document.getElementById('editGia').value = gia;
            new bootstrap.Modal(document.getElementById('editPriceModal')).show();
        }
    </script>
</body>
</html>
