<?php
require __DIR__ . '/../../../includes/db_connect.php';

if (!isset($_GET["MaCombo"])) {
    die("Thiếu tham số MaCombo!");
}

$MaCombo = intval($_GET["MaCombo"]);

// Lấy thông tin combo
$sql_combo = "SELECT * FROM combo WHERE MaCombo = $MaCombo";
$kq_combo = mysqli_query($ketnoi, $sql_combo);
$combo = mysqli_fetch_assoc($kq_combo);

if (!$combo) {
    die("Không tìm thấy combo!");
}

// Lấy danh sách chi tiết combo + JOIN lấy giá
$sql_ct = "
SELECT 
    ct.id,
    ct.SoLuong,
    sp.MaSP,
    sp.TenSP,
    sp.Anh AS AnhSP,
    sz.TenSize,
    ss.Gia
FROM chitietcombo ct
JOIN sanpham sp      ON ct.MaSP = sp.MaSP
LEFT JOIN size sz     ON ct.MaSize = sz.MaSize
LEFT JOIN sanpham_size ss ON ct.MaSP = ss.MaSP AND ct.MaSize = ss.MaSize
WHERE ct.MaCombo = $MaCombo
";

$kq_ct = mysqli_query($ketnoi, $sql_ct);

// Tính tổng tiền combo:
$tongTruocGiam = 0;
$dataCombo = [];

if ($kq_ct && $kq_ct->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($kq_ct)) {

        $donGia = $row["Gia"] ?? 0;
        $soLuong = $row["SoLuong"];
        $thanhTien = $donGia * $soLuong;

        $row["ThanhTien"] = $thanhTien;

        $tongTruocGiam += $thanhTien;

        $dataCombo[] = $row;
    }
}

$giamgia = $combo["giamgia"];
$tongSauGiam = $tongTruocGiam - ($tongTruocGiam * $giamgia / 100);

// delete chi tiết combo
if (isset($_POST["delete_id"])) {
    $id = intval($_POST["delete_id"]);
    $sql_del = "DELETE FROM chitietcombo WHERE id = $id";
    mysqli_query($ketnoi, $sql_del);

    if (mysqli_affected_rows($ketnoi) > 0) {
        echo "OK";
    } else {
        echo "ERR";
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết Combo</title>

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

        .back-btn {
            font-weight: 500;
        }

        .img-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
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

    <!-- Nội dung -->
    <div class="container mt-4">

        <a href="../combo/index.php" class="btn btn-secondary back-btn">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>

        <h2 class="main-title"><i class="fa-solid fa-circle-info"></i> Chi tiết Combo: <?= $combo["Tencombo"] ?></h2>
        <p class="text-muted">Mã Combo: <strong><?= $combo["MaCombo"] ?></strong></p>
        <a href="./create.php?MaCombo=<?= $MaCombo ?>&Tencombo=<?= urlencode($combo['Tencombo']) ?>"
            class="btn btn-success">
            <i class="fa-solid fa-plus"></i> Thêm món vào combo
        </a>

        <div class="table-responsive mt-4">
            <table class="table table-bordered table-hover align-middle">
                <thead class="text-center">
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên món</th>
                        <th>Size</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                        <?php if(!empty($dataCombo)) echo "<th>Thao tác</th>"; ?>
                        
                    </tr>
                </thead>

                <tbody class="text-center">
                    <?php if (!empty($dataCombo)) {
                        foreach ($dataCombo as $row) {

                            $imgPath = "../../../img/" . $row["AnhSP"];
                            if (!file_exists($imgPath) || empty($row["AnhSP"])) {
                                $imgPath = "../../../img/combo.png";
                            }
                    ?>
                            <tr>
                                <td><?= $row["id"] ?></td>
                                <td><img src="<?= $imgPath ?>" class="img-thumb"></td>
                                <td><?= $row["TenSP"] ?></td>
                                <td><?= $row["TenSize"] ?: "Không có" ?></td>
                                <td><?= $row["SoLuong"] ?></td>
                                <td><?= number_format($row["Gia"], 0, ',', '.') ?> đ</td>
                                <td class="text-success fw-bold">
                                    <?= number_format($row["ThanhTien"], 0, ',', '.') ?> đ
                                </td>
                                <td>
                                    <button class="btn btn-danger btn-sm btn-delete" data-id="<?= $row['id'] ?>">
                                        Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php
                        }
                    } else { ?>
                        <tr>
                            <td colspan="7" class="text-muted">Không có sản phẩm nào trong combo</td>
                        </tr>
                    <?php } ?>


                </tbody>


            </table>
        </div>

        <!-- Tổng tiền trước giảm -->
        <h4 class="text-end mt-4">
            Tổng tiền:
            <span class="fw-bold text-primary">
                <?= number_format($tongTruocGiam, 0, ',', '.') ?> đ
            </span>
        </h4>

        <!-- Giảm giá -->
        <h5 class="text-end">
            Giảm giá: <strong class="text-danger"><?= $giamgia ?>%</strong>
        </h5>

        <!-- Tổng tiền sau giảm -->
        <h4 class="text-end">
            Tổng tiền sau giảm giá:
            <span class="fw-bold text-success">
                <?= number_format($tongSauGiam, 0, ',', '.') ?> đ
            </span>
        </h4>


    </div>
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap JS -->
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    let id = this.dataset.id;

                    Swal.fire({
                        title: "Bạn chắc chắn?",
                        text: "Món này sẽ bị xóa khỏi combo!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Xóa",
                        cancelButtonText: "Hủy"
                    }).then((result) => {
                        if (result.isConfirmed) {

                            // gọi AJAX
                            fetch("", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/x-www-form-urlencoded"
                                    },
                                    body: "delete_id=" + id
                                })
                                .then(res => res.text())
                                .then(data => {

                                    if (data.trim() === "OK") {
                                        Swal.fire({
                                            title: "Đã xóa thành công!",
                                            text: "Đang tải lại trang...",
                                            icon: "success",
                                            timer: 1500, // thời gian tự tắt (ms)
                                            timerProgressBar: true,
                                            showConfirmButton: false
                                        });

                                        setTimeout(() => {
                                            location.reload();
                                        }, 1500);


                                    } else {
                                        Swal.fire({
                                            title: "Lỗi!",
                                            text: "Không thể xóa món.",
                                            icon: "error"
                                        });
                                    }

                                });
                        }
                    });

                });
            });

        });
    </script>


</body>

</html>