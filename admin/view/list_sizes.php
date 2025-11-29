<?php
require __DIR__ . '/../../includes/db_connect.php';

$sql = "SELECT * FROM size ORDER BY MaSize";
$result = mysqli_query($ketnoi, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách size sản phẩm - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .table th { background-color: #28a745; color: white; }
        .btn-edit { background-color: #ffc107; border: none; }
        .btn-delete { background-color: #dc3545; border: none; }
    </style>
</head>
<body>
    <?php include '../navbar_admin.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4"><i class="fa-solid fa-ruler-combined"></i> Danh sách size sản phẩm</h2>
        <div class="d-flex justify-content-between mb-3">
            <a href="add_size.php" class="btn btn-success"><i class="fa-solid fa-plus"></i> Thêm size mới</a>
            <a href="list_product_prices.php" class="btn btn-primary"><i class="fa-solid fa-tags"></i> Quản lý giá theo size</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Mã size</th>
                        <th>Tên size</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $row['MaSize']; ?></td>
                            <td><?php echo $row['TenSize']; ?></td>
                            <td>
                                <button class="btn btn-edit btn-sm me-2" onclick="editSize(<?php echo $row['MaSize']; ?>, '<?php echo $row['TenSize']; ?>')"><i class="fa-solid fa-edit"></i> Sửa</button>
                                <a href="../process/delete_size.php?id=<?php echo $row['MaSize']; ?>" class="btn btn-delete btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa size này?');"><i class="fa-solid fa-trash"></i> Xóa</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Sửa size -->
    <div class="modal fade" id="editSizeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-edit"></i> Sửa size</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../process/edit_size.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="editMaSize" name="maSize">
                        <div class="mb-3">
                            <label for="editTenSize" class="form-label">Tên size</label>
                            <input type="text" class="form-control" id="editTenSize" name="tenSize" required>
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
        function editSize(maSize, tenSize) {
            document.getElementById('editMaSize').value = maSize;
            document.getElementById('editTenSize').value = tenSize;
            new bootstrap.Modal(document.getElementById('editSizeModal')).show();
        }
    </script>
</body>
</html>
