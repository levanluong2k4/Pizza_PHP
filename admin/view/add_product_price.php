<?php
require __DIR__ . '/../../includes/db_connect.php';

$sql_sanpham = "SELECT MaSP, TenSP FROM sanpham ORDER BY TenSP";
$result_sanpham = mysqli_query($ketnoi, $sql_sanpham);

$sql_size = "SELECT MaSize, TenSize FROM size ORDER BY TenSize";
$result_size = mysqli_query($ketnoi, $sql_size);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm giá sản phẩm theo size - Admin</title>
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
            <h2 class="text-center mb-4"><i class="fa-solid fa-plus-circle"></i> Thêm giá sản phẩm theo size</h2>
            <form action="../process/insert_product_size.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="masanpham" class="form-label">Chọn sản phẩm <span class="text-danger">*</span></label>
                    <select class="form-select" id="masanpham" name="masanpham" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        <?php while ($row = mysqli_fetch_assoc($result_sanpham)) { ?>
                            <option value="<?php echo $row['MaSP']; ?>"><?php echo $row['TenSP']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="masize" class="form-label">Chọn size <span class="text-danger">*</span></label>
                    <select class="form-select" id="masize" name="masize" required>
                        <option value="">-- Chọn size --</option>
                        <?php while ($row = mysqli_fetch_assoc($result_size)) { ?>
                            <option value="<?php echo $row['MaSize']; ?>"><?php echo $row['TenSize']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="gia" class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="gia" name="gia" min="0" step="1000" placeholder="Nhập giá sản phẩm" required>
                    <div class="form-text">Nhập giá bằng VNĐ, ví dụ: 99000</div>
                </div>

                <div class="mb-3">
                    <label for="Anh" class="form-label">Ảnh sản phẩm <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="Anh" name="Anh" accept="image/*" required>
                    <div class="form-text">Chọn file ảnh cho sản phẩm theo size này (JPG, PNG, GIF)</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="list_product_prices.php" class="btn btn-secondary me-md-2"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
                    <button type="submit" class="btn btn-submit"><i class="fa-solid fa-save"></i> Thêm giá sản phẩm</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
