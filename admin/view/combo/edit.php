<?php
require __DIR__ . '/../../../includes/db_connect.php';

$errors = [];
$success = "";

// Lấy id combo
$id = intval($_GET["MaCombo"] ?? 0);

// Lấy dữ liệu combo
$sql = "SELECT * FROM combo WHERE MaCombo = ?";
$stmt = $ketnoi->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$combo = $result->fetch_assoc();

if (!$combo) {
    die("Không tìm thấy combo.");
}

$Tencombo_old = $combo["Tencombo"];
$Anh_old      = $combo["Anh"];
$giamgia_old  = $combo["giamgia"];

// Xử lý submit update
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $Tencombo = trim($_POST["Tencombo"] ?? "");
    $giamgia  = intval($_POST["giamgia"] ?? 0);

    $Anh = $Anh_old; // Mặc định giữ ảnh cũ

    // Xử lý upload ảnh mới
    if (isset($_FILES["Anh"]) && $_FILES["Anh"]["error"] === 0) {
        $targetDir = "../../../img/";
        $ext = pathinfo($_FILES["Anh"]["name"], PATHINFO_EXTENSION);
        $fileName = "combo_" . time() . "." . $ext;
        $targetPath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["Anh"]["tmp_name"], $targetPath)) {
            $Anh = $fileName;
        }
    }

    // Validate
    if ($Tencombo === "") $errors[] = "Tên combo không được để trống";
    if ($giamgia < 0 || $giamgia > 100) $errors[] = "Giảm giá phải từ 0–100%";

    if (empty($errors)) {

        $sql = "UPDATE combo 
                SET Tencombo = ?, Anh = ?, giamgia = ?
                WHERE MaCombo = ?";
        $stmt = $ketnoi->prepare($sql);
        $stmt->bind_param("ssii", $Tencombo, $Anh, $giamgia, $id);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Lỗi SQL: " . $ketnoi->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sửa Combo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        body {
            background-color: #f9fafb;
            font-family: "Segoe UI", sans-serif;
        }

        .navbar {
            background: linear-gradient(90deg, #28a745, #66bb6a);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            font-weight: bold;
            color: #fff !important;
        }

        .navbar-nav .nav-link {
            color: #fff !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .dropdown:hover .nav-link {
            background-color: rgba(255, 255, 255, 0.25);
            border-radius: 8px;
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .dropdown-menu a:hover {
            background-color: #e8f5e9;
            color: #28a745;
        }

        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -1px;
        }

        .navbar-nav>li:last-child .dropdown-submenu .dropdown-menu {
            left: auto;
            right: 100%;
        }

        .dropdown-submenu>a::after {
            content: "\f054";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-left: auto;
            font-size: 0.8em;
        }

        .navbar-nav {
            margin: 0 auto;
        }

        .logout-btn {
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 6px;
            padding: 6px 14px;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: white;
            color: #28a745;
        }

        .main-title {
            color: #28a745;
            font-weight: 700;
            margin-top: 50px;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .preview-img {
            width: 120px;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fa-solid fa-leaf"></i> Admin Panel</a>

            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav text-center">

                    <!-- QUẢN LÝ SẢN PHẨM -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-box-open"></i> Quản lý sản phẩm
                        </a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-cubes"></i> Quản lý sản phẩm
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-list"></i> Danh sách sản phẩm</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-plus-circle"></i> Thêm sản phẩm</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-layer-group"></i> Danh mục loại sản phẩm</a></li>
                                </ul>
                            </li>

                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-ruler-combined"></i> Quản lý size sản phẩm
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-list-ul"></i> Danh sách size sản phẩm</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-plus-circle"></i> Thêm size sản phẩm</a></li>
                                </ul>
                            </li>

                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-tags"></i> Quản lý giá theo size
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-list-ol"></i> Danh sách giá sản phẩm theo size</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-plus-circle"></i> Thêm giá sản phẩm</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <!-- TÀI KHOẢN -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-user-tie"></i> Quản lý tài khoản
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="create_account.php"><i class="fa-solid fa-id-card"></i> Tạo tài khoản nhân viên</a></li>
                        </ul>
                    </li>

                    <!-- KHÁCH HÀNG -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-users"></i> Quản lý khách hàng
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="list_customer.php"><i class="fa-solid fa-list"></i> Danh sách khách hàng</a></li>
                            <li><a class="dropdown-item" href="add_customer.php"><i class="fa-solid fa-crown"></i> Khách hàng mua nhiều nhất</a></li>
                            <li><a class="dropdown-item" href="add_customer.php"><i class="fa-solid fa-map-marked-alt"></i> Khu vực KH mua nhiều nhất</a></li>
                        </ul>
                    </li>

                    <!-- ĐƠN HÀNG -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-receipt"></i> Quản lý đơn hàng
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="list_orders.php"><i class="fa-solid fa-clipboard-list"></i> Danh sách đơn hàng</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-hourglass-half"></i> Đơn hàng chờ xử lý</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-check-circle"></i> Đơn hàng đã hoàn thành</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-times-circle"></i> Đơn hàng đã hủy</a></li>
                        </ul>
                    </li>

                    <!-- THỐNG KÊ -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-chart-line"></i> Thống kê
                        </a>
                        <ul class="dropdown-menu">

                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-box"></i> Thống kê sản phẩm
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="../best_selling/best_selling_product.php"><i class="fa-solid fa-fire"></i> Sản phẩm bán chạy</a></li>
                                    <li><a class="dropdown-item" href="../best_selling/best_selling_category.php"><i class="fa-solid fa-layer-group"></i> Loại sản phẩm bán chạy</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-box-open"></i> Tồn kho sản phẩm</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-exclamation-triangle"></i> Sản phẩm sắp hết hàng</a></li>
                                </ul>
                            </li>

                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-money-bill-wave"></i> Thống kê doanh thu
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="../revenue_statistic/revenue_statistic_table.php"><i class="fa-solid fa-table"></i> Doanh thu theo số liệu</a></li>
                                    <li><a class="dropdown-item" href="../revenue_statistic/revenue_statistic_chart.php"><i class="fa-solid fa-chart-bar"></i> Doanh thu theo biểu đồ</a></li>
                                </ul>
                            </li>

                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-shopping-cart"></i> Thống kê đơn hàng
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-list-alt"></i> Tổng quan đơn hàng</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-chart-pie"></i> Tỷ lệ đơn hàng theo trạng thái</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-clock"></i> Đơn hàng theo thời gian</a></li>
                                </ul>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-chart-area"></i> Báo cáo tổng hợp</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-file-export"></i> Xuất báo cáo Excel</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-file-pdf"></i> Xuất báo cáo PDF</a></li>
                        </ul>
                    </li>

                </ul>

                <!-- Logout -->
                <div class="ms-auto">
                    <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
                </div>

            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="main-title"><i class="fa-solid fa-pen-to-square"></i> Sửa Combo</h2>

        <a href="./index.php" class="btn btn-secondary mt-2">
            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
        </a>

        <div class="form-container mt-4">

            <?php if (!empty($errors)) { ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $err) echo "<div>- $err</div>"; ?>
                </div>
            <?php } ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Tên Combo:</label>
                    <input type="text" name="Tencombo" value="<?= htmlspecialchars($Tencombo_old) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Giảm giá (%):</label>
                    <input type="number" name="giamgia" class="form-control"
                        min="0" max="100"
                        value="<?= htmlspecialchars($giamgia_old) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ảnh Combo:</label>
                    <input type="file" name="Anh" class="form-control">
                    <small class="text-muted d-block mt-1">Nếu không chọn ảnh → giữ ảnh cũ.</small>

                    <?php if ($Anh_old): ?>
                        <img src="../../../img/<?= $Anh_old ?>" class="preview-img">
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-success px-4">
                    <i class="fa-solid fa-check"></i> Cập nhật
                </button>

            </form>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownSubmenus = document.querySelectorAll('.dropdown-submenu');

            dropdownSubmenus.forEach(function(submenu) {
                const submenuLink = submenu.querySelector('a[data-bs-toggle="dropdown"]');
                const submenuDropdown = submenu.querySelector('.dropdown-menu');

                submenu.addEventListener('mouseenter', function() {
                    submenuDropdown.classList.add('show');
                });

                submenu.addEventListener('mouseleave', function() {
                    submenuDropdown.classList.remove('show');
                });

                submenuLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    submenuDropdown.classList.toggle('show');
                });
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-submenu')) {
                    document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(menu) {
                        menu.classList.remove('show');
                    });
                }
            });
        });
    </script>

</body>

</html>