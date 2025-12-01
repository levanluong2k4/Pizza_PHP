<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: http://localhost/unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}
require __DIR__ . '/../../../includes/db_connect.php';

$filter_type = $_GET['type'] ?? 'month';
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// ----------------------------
// TÍNH THỜI GIAN LỌC
// ----------------------------
if ($filter_type == 'day') {
    $from = date("Y-m-d 00:00:00");
    $to = date("Y-m-d 23:59:59");
    $title = "Tỷ lệ lấp đầy hôm nay (" . date("d/m/Y") . ")";
} elseif ($filter_type == 'month') {
    $from = date("$year-$month-01 00:00:00");
    $to   = date("Y-m-t 23:59:59", strtotime($from));
    $title = "Tỷ lệ lấp đầy tháng $month/$year";
} elseif ($filter_type == 'year') {
    $from = date("$year-01-01 00:00:00");
    $to   = date("$year-12-31 23:59:59");
    $title = "Tỷ lệ lấp đầy năm $year";
}

// ----------------------------
// LẤY TỔNG SỐ BÀN
// ----------------------------
$sql_total_tables = "SELECT COUNT(*) AS total FROM banan";
$res_total = mysqli_query($ketnoi, $sql_total_tables);
$total_tables = mysqli_fetch_assoc($res_total)['total'];

// ----------------------------
// LẤY LƯỢT BÀN ĐƯỢC ĐẶT
// ----------------------------
$sql_usage = "
    SELECT 
        DATE(NgayGio) AS ngay,
        COUNT(MaDatBan) AS so_luot_su_dung
    FROM datban
    WHERE MaBan IS NOT NULL
      AND TrangThaiDatBan IN ('da_xac_nhan','dang_su_dung','thanh_cong')
      AND NgayGio BETWEEN '$from' AND '$to'
    GROUP BY DATE(NgayGio)
    ORDER BY ngay ASC
";
$res_usage = mysqli_query($ketnoi, $sql_usage);

$labels = [];
$data = [];

$period = new DatePeriod(
    new DateTime($from),
    new DateInterval('P1D'),
    (new DateTime($to))->modify('+1 day')
);

// init mảng đầy đủ ngày → tránh lỗi Chart.js bị thiếu ngày
foreach ($period as $date) {
    $d = $date->format("Y-m-d");
    $labels[$d] = $d;
    $data[$d] = 0;
}

// đổ dữ liệu thực vào
while ($row = mysqli_fetch_assoc($res_usage)) {
    $day = $row['ngay'];
    $data[$day] = $row['so_luot_su_dung'];
}

// ----------------------------
// TÍNH TỶ LỆ LẤP ĐẦY THEO NGÀY
// ----------------------------
$ratio = [];
foreach ($data as $day => $count) {
    // số slot bàn trong 1 ngày = tổng số bàn
    $ratio[$day] = $total_tables > 0 ? round(($count / $total_tables) * 100, 2) : 0;
}

// ----------------------------
// TÍNH XU HƯỚNG SO VỚI KỲ TRƯỚC
// ----------------------------
$trend = 0;

// Tỷ lệ trung bình hiện tại
$current_avg = count($ratio) > 0 ? array_sum($ratio) / count($ratio) : 0;

// Xác định kỳ trước
if ($filter_type == 'month') {
    $prev_month = $month - 1;
    $prev_year  = $year;

    if ($prev_month == 0) {
        $prev_month = 12;
        $prev_year--;
    }

    $prev_from = "$prev_year-$prev_month-01 00:00:00";
    $prev_to   = date("Y-m-t 23:59:59", strtotime($prev_from));
} elseif ($filter_type == 'year') {
    $prev_year = $year - 1;

    $prev_from = "$prev_year-01-01 00:00:00";
    $prev_to   = "$prev_year-12-31 23:59:59";
} else {
    // Lọc theo ngày → không có xu hướng
    $trend = 0;
    goto end_trend;
}

// Query lấy tỷ lệ của kỳ trước
$sql_prev = "
    SELECT DATE(NgayGio) AS ngay, COUNT(*) AS so_luot
    FROM datban
    WHERE MaBan IS NOT NULL
      AND TrangThaiDatBan IN ('da_xac_nhan','dang_su_dung','thanh_cong')
      AND NgayGio BETWEEN '$prev_from' AND '$prev_to'
    GROUP BY DATE(NgayGio)
";

$res_prev = mysqli_query($ketnoi, $sql_prev);

$prev_ratio = [];
while ($row = mysqli_fetch_assoc($res_prev)) {
    $day = $row['ngay'];
    $prev_ratio[$day] = $total_tables > 0
        ? round(($row['so_luot'] / $total_tables) * 100, 2)
        : 0;
}

$prev_avg = count($prev_ratio) > 0
    ? array_sum($prev_ratio) / count($prev_ratio)
    : 0;

// Tính phần trăm thay đổi
if ($prev_avg > 0) {
    $trend = (($current_avg - $prev_avg) / $prev_avg) * 100;
} else {
    $trend = 0;
}

end_trend:


?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Thống kê tỉ lệ lấp đầy bàn</title>

    <!-- Bootstrap CSS -->
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
            margin-top: 40px;
            text-align: center;
        }

        table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        th {
            background-color: #28a745;
            color: white;
        }

        td,
        th {
            vertical-align: middle;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        img {
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../../navbar_admin.php'; ?>

    <!-- ===== NỘI DUNG CHÍNH: BẢNG THỐNG KÊ ===== -->
    <div class="container mt-4">

        <h2 class="main-title text-center mb-3">Thống kê Tỷ lệ Lấp đầy</h2>
        <h5 class="text-center mb-4">
            <?= $title ?>
        </h5>

        <!-- FORM LỌC -->
        <form method="GET" class="row g-3 align-items-end justify-content-center">

            <input type="hidden" name="type" value="month">

            <div class="col-auto">
                <label class="form-label">Tháng:</label>
                <input type="number" min="1" max="12" name="month"
                    class="form-control" value="<?= $month ?>">
            </div>

            <div class="col-auto">
                <label class="form-label">Năm:</label>
                <input type="number" name="year" class="form-control" value="<?= $year ?>">
            </div>

            <div class="col-auto d-flex gap-2">
                <button class="btn btn-success" type="submit" name="type" value="month">
                    Lọc theo tháng
                </button>

                <button class="btn btn-primary" type="submit" name="type" value="year">
                    Lọc theo năm
                </button>
            </div>

        </form>

        <!-- XU HƯỚNG -->
        <div class="alert alert-info text-center mt-4" style="font-size: 18px;">
            <strong>Xu hướng:</strong>
            <?php if ($trend > 0): ?>
                <span style="color:green;">▲ Tăng <?= number_format($trend, 1) ?>% so với kỳ trước</span>
            <?php elseif ($trend < 0): ?>
                <span style="color:red;">▼ Giảm <?= number_format(abs($trend), 1) ?>% so với kỳ trước</span>
            <?php else: ?>
                <span>— Không thay đổi</span>
            <?php endif; ?>
        </div>

    </div>


    <!-- BIỂU ĐỒ -->
    <div class="container mt-4">

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0">Biểu đồ Tỷ lệ Lấp đầy theo ngày</h5>
            </div>

            <div class="card-body">
                <canvas id="fillRateChart" height="100"></canvas>
            </div>
        </div>

    </div>


    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const labels = <?= json_encode(array_keys($ratio)) ?>;
        const data = <?= json_encode(array_values($ratio)) ?>;

        new Chart(document.getElementById("fillRateChart"), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: "Tỷ lệ lấp đầy (%)",
                    data: data,
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: (v) => v + "%"
                        }
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