<?php
require __DIR__ . '/../../../includes/db_connect.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');
$now = new DateTime();
$currentYear = (int)$now->format('Y');
$currentMonth = (int)$now->format('m');

$filter = $_GET['filter'] ?? 'year';
$selectedYear = $_GET['year'] ?? $currentYear;
$selectedMonth = $_GET['month'] ?? null;
$from = $_GET['from_date'] ?? null;
$to = $_GET['to_date'] ?? null;

// Xác định điều kiện lọc
if ($from && $to) {
    $condition = "AND DATE(dh.NgayDat) BETWEEN '$from' AND '$to'";
    $title = "Doanh thu từ $from đến $to";
} else {
    switch ($filter) {
        case 'today':
            $condition = "AND DATE(dh.NgayDat) = CURDATE()";
            $title = "Doanh thu hôm nay (" . $now->format('d/m/Y') . ")";
            break;
        case '12months':
            $condition = "AND dh.NgayDat >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
            $title = "Doanh thu 12 tháng gần nhất";
            break;
        case 'year':
            $condition = "AND YEAR(dh.NgayDat) = $selectedYear";
            $title = "Doanh thu năm $selectedYear";
            break;
        case 'month':
            $start = sprintf("%04d-%02d-01", $selectedYear, $selectedMonth);
            $end = date("Y-m-t", strtotime($start));
            $condition = "AND dh.NgayDat BETWEEN '$start' AND '$end'";
            $title = "Doanh thu tháng $selectedMonth/$selectedYear";
            break;
        default:
            $condition = "AND YEAR(dh.NgayDat) = $currentYear";
            $title = "Doanh thu năm $currentYear";
            break;
    }
}

$sql = "
SELECT 
    DATE(dh.NgayDat) AS Ngay,
    SUM(ctdh.SoLuong) AS TongSoLuongBan,
    SUM(ctdh.ThanhTien) AS DoanhThuNgay
FROM donhang dh
JOIN chitietdonhang ctdh ON dh.MaDH = ctdh.MaDH
WHERE dh.trangthai = 'Giao thành công'
$condition
GROUP BY DATE(dh.NgayDat)
ORDER BY Ngay ASC;
";

$kq = mysqli_query($ketnoi, $sql);
$chartData = [];
while ($row = mysqli_fetch_assoc($kq)) $chartData[] = $row;
mysqli_data_seek($kq, 0);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <title>Thống kê doanh thu theo biểu đồ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        body {
            background-color: #f9fafb;
            font-family: "Segoe UI", sans-serif;
        }

        .main-title {
            color: #28a745;
            font-weight: 700;
            text-align: center;
            margin-top: 40px;
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
    </style>
</head>

<body class="p-3">

    <!-- ===== NAVBAR GIỮ NGUYÊN ===== -->
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
                    <!-- Giữ nguyên tất cả các option -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false"><i class="fa-solid fa-box-open"></i> Quản lý sản phẩm</a>
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

                    <!-- Các mục khác giữ nguyên -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false"><i class="fa-solid fa-user-tie"></i> Quản lý tài khoản</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="create_account.php"><i class="fa-solid fa-id-card"></i> Tạo tài khoản nhân viên</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false"><i class="fa-solid fa-users"></i> Quản lý khách hàng</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="list_customer.php"><i class="fa-solid fa-list"></i> Danh sách khách hàng</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-crown"></i> Khách hàng mua nhiều nhất</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-map-marked-alt"></i> Khu vực KH mua nhiều nhất</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false"><i class="fa-solid fa-receipt"></i> Quản lý đơn hàng</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="list_orders.php"><i class="fa-solid fa-clipboard-list"></i> Danh sách đơn hàng</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-hourglass-half"></i> Đơn hàng chờ xử lý</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-check-circle"></i> Đơn hàng đã hoàn thành</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-times-circle"></i> Đơn hàng đã hủy</a></li>
                        </ul>
                    </li>

                    <!-- Thống kê -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false"><i class="fa-solid fa-chart-line"></i> Thống kê</a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown"><i class="fa-solid fa-box"></i> Thống kê sản phẩm</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="../best_selling/best_selling_product.php"><i class="fa-solid fa-fire"></i> Sản phẩm bán chạy</a></li>
                                    <li><a class="dropdown-item" href="../best_selling/best_selling_category.php"><i class="fa-solid fa-layer-group"></i> Loại sản phẩm bán chạy</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-box-open"></i> Tồn kho sản phẩm</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fa-solid fa-exclamation-triangle"></i> Sản phẩm sắp hết hàng</a></li>
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown"><i class="fa-solid fa-money-bill-wave"></i> Thống kê doanh thu</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="./revenue_statistic_table.php"><i class="fa-solid fa-table"></i> Doanh thu theo số liệu</a></li>
                                    <li><a class="dropdown-item" href="./revenue_statistic_chart.php"><i class="fa-solid fa-chart-bar"></i> Doanh thu theo biểu đồ</a></li>
                                </ul>
                            </li>
                            <!-- Thống kê đơn hàng -->
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
                        </ul>
                    </li>


                </ul>

                <div class="ms-auto">
                    <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="main-title">Thống kê doanh thu theo biểu đồ</h2>

        <form method="GET" class="row g-3 align-items-end mb-4">
            <div class="col-auto">
                <label for="from_date" class="form-label">Từ ngày:</label>
                <input type="date" id="from_date" name="from_date" class="form-control"
                    value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
            </div>
            <div class="col-auto">
                <label for="to_date" class="form-label">Đến ngày:</label>
                <input type="date" id="to_date" name="to_date" class="form-control"
                    value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-success" type="submit"><i class="fa-solid fa-filter"></i> Lọc</button>
            </div>

            <!-- Các nút lọc nhanh -->
            <div class="col-auto ms-auto d-flex flex-wrap gap-2">
                <a href="?filter=today" class="btn btn-outline-success <?php if($filter=='today') echo 'active'; ?>">Hôm nay</a>
                <a href="?filter=12months" class="btn btn-outline-success <?php if($filter=='12months') echo 'active'; ?>">12 tháng gần nhất</a>

                <!-- Năm -->
                <div class="dropdown">
                    <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Năm <?php echo $selectedYear; ?>
                    </button>
                    <ul class="dropdown-menu">
                        <?php for ($y = $currentYear; $y >= $currentYear - 5; $y--): ?>
                            <li><a class="dropdown-item" href="?filter=year&year=<?php echo $y; ?>">Năm <?php echo $y; ?></a></li>
                        <?php endfor; ?>
                    </ul>
                </div>

                <!-- Tháng -->
                <div class="dropdown">
                    <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Tháng <?php echo $selectedMonth ?: $currentMonth; ?>
                    </button>
                    <ul class="dropdown-menu">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <li>
                                <a class="dropdown-item" href="?filter=month&year=<?php echo $selectedYear; ?>&month=<?php echo $m; ?>">
                                    Tháng <?php echo $m; ?>/<?php echo $selectedYear; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
        </form>

        <h5 class="text-center text-success fw-bold mb-3"><?php echo $title; ?></h5>

        <!-- Biểu đồ -->
        <div class="mt-5">
            <canvas id="revenueChart" height="120"></canvas>
        </div>
    </div>

    <script>
        const data = <?php echo json_encode($chartData); ?>;
        const labels = data.map(d => d.Ngay);
        const doanhThu = data.map(d => d.DoanhThuNgay);
        const soLuong = data.map(d => d.TongSoLuongBan);

        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Doanh thu (VNĐ)',
                        data: doanhThu,
                        backgroundColor: 'rgba(40,167,69,0.6)',
                        borderColor: 'rgba(40,167,69,1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Số lượng bán',
                        data: soLuong,
                        backgroundColor: 'rgba(75,192,192,0.4)',
                        borderColor: 'rgba(75,192,192,1)',
                        borderWidth: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                stacked: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Biểu đồ doanh thu & số lượng bán theo ngày',
                        font: { size: 18 }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label.includes('Doanh thu')
                                ? ctx.parsed.y.toLocaleString('vi-VN') + '₫'
                                : ctx.parsed.y
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Doanh thu (VNĐ)' },
                        ticks: { callback: val => val.toLocaleString('vi-VN') }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Số lượng bán' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownSubmenus = document.querySelectorAll('.dropdown-submenu');
            dropdownSubmenus.forEach(function(submenu) {
                const submenuLink = submenu.querySelector('a[data-bs-toggle="dropdown"]');
                const submenuDropdown = submenu.querySelector('.dropdown-menu');
                submenu.addEventListener('mouseenter', () => submenuDropdown.classList.add('show'));
                submenu.addEventListener('mouseleave', () => submenuDropdown.classList.remove('show'));
                submenuLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    submenuDropdown.classList.toggle('show');
                });
            });
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-submenu')) {
                    document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(menu => menu.classList.remove('show'));
                }
            });
        });
    </script>
</body>

</html>