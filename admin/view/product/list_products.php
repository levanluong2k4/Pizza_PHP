<?php
require_once __DIR__ . '/../../../includes/db_connect.php';

$sql = "SELECT sp.*, lsp.TenLoai FROM sanpham sp JOIN loaisanpham lsp ON sp.MaLoai = lsp.MaLoai";
$result = mysqli_query($ketnoi, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách sản phẩm - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .table th { background-color: #28a745; color: white; }
        .btn-edit { background-color: #ffc107; border: none; }
        .btn-delete { background-color: #dc3545; border: none; }
        .product-img { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>
   <?php include '../../navbar_admin.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4"><i class="fa-solid fa-box-open"></i> Danh sách sản phẩm</h2>
        <div class="d-flex justify-content-between mb-3">
            <a href="add_product.php" class="btn btn-success"><i class="fa-solid fa-plus"></i> Thêm sản phẩm mới</a>
            <a href="manage_categories.php" class="btn btn-primary"><i class="fa-solid fa-layer-group"></i> Quản lý danh mục</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Mô tả</th>
                        <th>Ảnh</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $row['MaSP']; ?></td>
                            <td><?php echo $row['TenSP']; ?></td>
                            <td><?php echo $row['TenLoai']; ?></td>
                            <td><?php echo substr($row['MoTa'], 0, 100) . '...'; ?></td>
                            <td><img src="/unitop/backend/lesson/school/project_pizza/<?php echo $row['Anh']; ?>" alt="Ảnh sản phẩm" class="product-img"></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $row['MaSP']; ?>" class="btn btn-edit btn-sm me-2"><i class="fa-solid fa-edit"></i> Sửa</a>
                                <a href="/unitop/backend/lesson/school/project_pizza/admin/process/delete_product.php?id=<?php echo $row['MaSP']; ?>" class="btn btn-delete btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');"><i class="fa-solid fa-trash"></i> Xóa</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>