<?php
require __DIR__ . '/../../includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm size sản phẩm mới - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .form-container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .btn-submit { background-color: #28a745; border: none; }
        .btn-submit:hover { background-color: #218838; }
    </style>
</head>
<body>
    <?php include '../navbar_admin.php'; ?>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4"><i class="fa-solid fa-plus-circle"></i> Thêm size sản phẩm mới</h2>
            <form action="../process/add_size.php" method="post">
                <div class="mb-3">
                    <label for="tenSize" class="form-label">Tên size <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="tenSize" name="tenSize" placeholder="Ví dụ: Large (Lớn 12 inch)" required>
                    <div class="form-text">Nhập tên size kèm mô tả kích thước</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="list_sizes.php" class="btn btn-secondary me-md-2"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
                    <button type="submit" class="btn btn-submit"><i class="fa-solid fa-save"></i> Thêm size</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
