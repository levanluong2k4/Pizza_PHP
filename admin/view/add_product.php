<?php
require __DIR__ . '/../../includes/db_connect.php';

$sql = "SELECT * FROM loaisanpham";
$result = mysqli_query($ketnoi, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm mới - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .form-container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .btn-submit { background-color: #28a745; border: none; }
        .btn-submit:hover { background-color: #218838; }
    </style>
</head>
<body>
    <?php include '../navbar_admin.php'; ?>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4"><i class="fa-solid fa-plus-circle"></i> Thêm sản phẩm mới</h2>
            <form action="../process/insert_product.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="tensp" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="tensp" name="tensp" required>
                </div>

                <div class="mb-3">
                    <label for="Anh" class="form-label">Ảnh sản phẩm <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="Anh" name="Anh" accept="image/*" required>
                    <div class="form-text">Chọn file ảnh (JPG, PNG, GIF)</div>
                </div>

                <div class="mb-3">
                    <label for="maloai" class="form-label">Danh mục sản phẩm <span class="text-danger">*</span></label>
                    <select class="form-select" id="maloai" name="maloai" required>
                        <option value="">Chọn danh mục</option>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <option value="<?php echo $row['MaLoai']; ?>"><?php echo $row['TenLoai']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="mota" class="form-label">Mô tả sản phẩm</label>
                    <textarea class="form-control" id="mota" name="mota" rows="4" placeholder="Nhập mô tả chi tiết về sản phẩm..."></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="list_products.php" class="btn btn-secondary me-md-2"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
                    <button type="submit" class="btn btn-submit"><i class="fa-solid fa-save"></i> Thêm sản phẩm</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
