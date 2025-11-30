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

  <?php include '../../navbar_admin.php'; ?>

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