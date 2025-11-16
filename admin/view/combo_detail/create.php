<?php
require __DIR__ . '/../../../includes/db_connect.php';

$MaCombo  = isset($_GET['MaCombo']) ? intval($_GET['MaCombo']) : 0;
$Tencombo = isset($_GET['Tencombo']) ? urldecode($_GET['Tencombo']) : "";

// Biến giữ lại lựa chọn khi submit
$MaLoai = isset($_POST['MaLoai']) ? intval($_POST['MaLoai']) : 0;
$MaSP   = isset($_POST['MaSP']) ? intval($_POST['MaSP']) : 0;
$MaSize = isset($_POST['MaSize']) ? intval($_POST['MaSize']) : 0;

// Lấy danh sách loại sản phẩm
$loaiSql = "SELECT * FROM loaisanpham";
$loaiKQ = mysqli_query($ketnoi, $loaiSql);

// Nếu chọn loại sản phẩm → lấy danh sách SP của loại đó
$dsSanPham = [];
if ($MaLoai > 0) {
    $sqlSP = "SELECT MaSP, TenSP FROM sanpham WHERE MaLoai = $MaLoai ORDER BY TenSP";
    $dsSanPham = mysqli_query($ketnoi, $sqlSP);
}

// Nếu chọn sản phẩm → lấy danh sách size của sản phẩm đó
$dsSize = [];
if ($MaSP > 0) {
    $sqlSize = "SELECT ss.MaSize, s.TenSize 
                FROM sanpham_size ss
                JOIN size s ON ss.MaSize = s.MaSize
                WHERE ss.MaSP = $MaSP";
    $dsSize = mysqli_query($ketnoi, $sqlSize);
}

// Nếu người dùng nhấn nút thêm → xử lý lưu
if (isset($_POST['submit_add'])) {

    $SoLuong = intval($_POST['SoLuong']);

    // Kiểm tra SP + Size đã tồn tại trong combo chưa?
    $sqlCheck = "SELECT id, SoLuong 
                 FROM chitietcombo 
                 WHERE MaCombo = $MaCombo 
                   AND MaSP = $MaSP 
                   AND MaSize = $MaSize
                 LIMIT 1";

    $kqCheck = mysqli_query($ketnoi, $sqlCheck);

    // Nếu tồn tại → update tăng số lượng
    if (mysqli_num_rows($kqCheck) > 0) {
        $row = mysqli_fetch_assoc($kqCheck);
        $idOld   = $row['id'];
        $newSL   = $row['SoLuong'] + $SoLuong;

        $update = "UPDATE chitietcombo 
                   SET SoLuong = $newSL
                   WHERE id = $idOld";

        mysqli_query($ketnoi, $update);
    } 
    else {
        // Nếu chưa tồn tại → thêm mới
        $insert = "INSERT INTO chitietcombo(MaCombo, MaSP, MaSize, SoLuong)
                   VALUES ($MaCombo, $MaSP, $MaSize, $SoLuong)";

        mysqli_query($ketnoi, $insert);
    }

    header("Location: index.php?MaCombo=$MaCombo");
    exit;
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
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
        .card-custom {
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        label {
            font-weight: 600;
        }
    </style>
    <title>Thêm sản phẩm vào combo</title>
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

    <div class="container mt-5">

        <a href="index.php?MaCombo=<?= $MaCombo ?>" class="btn btn-secondary ">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>

        <h2 class="main-title">
            <i class="fa-solid fa-plus-circle"></i> Thêm món vào combo: <?= $Tencombo ?>
        </h2>

        <div class="card card-custom mt-4 p-4">

            <form method="POST">

                <!-- CHỌN LOẠI -->
                <div class="mb-3">
                    <label>Loại sản phẩm</label>
                    <select name="MaLoai" class="form-select" onchange="this.form.submit()">
                        <option value="0">-- Chọn loại sản phẩm --</option>
                        <?php while ($l = mysqli_fetch_assoc($loaiKQ)) { ?>
                            <option value="<?= $l['MaLoai'] ?>" <?= $MaLoai == $l['MaLoai'] ? "selected" : "" ?>>
                                <?= $l['TenLoai'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- CHỌN SẢN PHẨM -->
                <div class="mb-3">
                    <label>Sản phẩm</label>
                    <select name="MaSP" class="form-select" onchange="this.form.submit()">
                        <option value="0">-- Chọn sản phẩm --</option>

                        <?php if (!empty($dsSanPham)) { ?>
                            <?php while ($sp = mysqli_fetch_assoc($dsSanPham)) { ?>
                                <option value="<?= $sp['MaSP'] ?>" <?= $MaSP == $sp['MaSP'] ? "selected" : "" ?>>
                                    <?= $sp['TenSP'] ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>

                <!-- CHỌN SIZE -->
                <div class="mb-3">
                    <label>Size</label>
                    <select name="MaSize" class="form-select">
                        <option value="0">-- Chọn size --</option>

                        <?php if (!empty($dsSize)) { ?>
                            <?php while ($sz = mysqli_fetch_assoc($dsSize)) { ?>
                                <option value="<?= $sz['MaSize'] ?>" <?= $MaSize == $sz['MaSize'] ? "selected" : "" ?>>
                                    <?= $sz['TenSize'] ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>

                <!-- NHẬP SL + GIẢM GIÁ -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Số lượng</label>
                        <input type="number" name="SoLuong" class="form-control" min="1" value="1" required>
                    </div>
                </div>

                <button type="submit" name="submit_add" class="btn btn-success mt-3">
                    <i class="fa-solid fa-check"></i> Thêm vào combo
                </button>

            </form>

        </div>

    </div>

    

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<!-- Script navbar dropdown submenu -->
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelector("form").addEventListener("submit", function(e) {
    let MaLoai = document.querySelector("select[name='MaLoai']").value;
    let MaSP   = document.querySelector("select[name='MaSP']").value;
    let MaSize = document.querySelector("select[name='MaSize']").value;
    let SL     = document.querySelector("input[name='SoLuong']").value;

    if (MaLoai == 0 || MaSP == 0 || MaSize == 0 || SL <= 0) {
        e.preventDefault(); // Chặn submit

        Swal.fire({
            icon: "warning",
            title: "Thiếu thông tin!",
            text: "Vui lòng nhập đủ các trường.",
            timer: 2000,
            showConfirmButton: false
        });

        return false;
    }
});
</script>

</html>