<?php
require_once __DIR__ . '/../../../includes/db_connect.php';

$sql = "SELECT * FROM loaisanpham";
$result = mysqli_query($ketnoi, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục loại sản phẩm - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .table th { background-color: #28a745; color: white; }
        .btn-edit { background-color: #ffc107; border: none; }
        .btn-delete { background-color: #dc3545; border: none; }
        .modal-header { background-color: #28a745; color: white; }
    </style>
</head>
<body>
   <?php include '../../navbar_admin.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4"><i class="fa-solid fa-layer-group"></i> Quản lý danh mục loại sản phẩm</h2>
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="fa-solid fa-plus"></i> Thêm danh mục mới</button>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Mã loại</th>
                        <th>Tên loại</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $row['MaLoai']; ?></td>
                            <td><?php echo $row['TenLoai']; ?></td>
                            <td>
                                <button class="btn btn-edit btn-sm me-2" onclick="editCategory(<?php echo $row['MaLoai']; ?>, '<?php echo $row['TenLoai']; ?>')"><i class="fa-solid fa-edit"></i> Sửa</button>
                                <a href="/unitop/backend/lesson/school/project_pizza/admin/process/delete_category.php?id=<?php echo $row['MaLoai']; ?>" class="btn btn-delete btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');"><i class="fa-solid fa-trash"></i> Xóa</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Thêm danh mục -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-plus"></i> Thêm danh mục mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/unitop/backend/lesson/school/project_pizza/admin/process/add_category.php" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tenLoai" class="form-label">Tên loại sản phẩm</label>
                            <input type="text" class="form-control" id="tenLoai" name="tenLoai" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">Thêm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sửa danh mục -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-edit"></i> Sửa danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/unitop/backend/lesson/school/project_pizza/admin/process/edit_category.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="editMaLoai" name="maLoai">
                        <div class="mb-3">
                            <label for="editTenLoai" class="form-label">Tên loại sản phẩm</label>
                            <input type="text" class="form-control" id="editTenLoai" name="tenLoai" required>
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
        function editCategory(maLoai, tenLoai) {
            document.getElementById('editMaLoai').value = maLoai;
            document.getElementById('editTenLoai').value = tenLoai;
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        }
    </script>
</body>
</html>