<?php
// Kết nối DB
$conn = new mysqli("localhost", "root", "", "php_pizza");
if ($conn->connect_error) die("Lỗi kết nối DB");

// Lấy tổng quan đơn hàng theo ngày
$sql = "
    SELECT 
        DATE(ngaydat) AS ngay,
        COUNT(*) AS so_don,
        SUM(TongTien) AS tong_tien
    FROM donhang
    GROUP BY DATE(ngaydat)
    ORDER BY DATE(ngaydat) ASC
";

$result = $conn->query($sql);

// Chuẩn bị dữ liệu cho chart
$labels    = [];
$soDonData = [];
$tongTienData = [];

while ($row = $result->fetch_assoc()) {
    $labels[]       = $row['ngay'];
    $soDonData[]    = (int)$row['so_don'];
    $tongTienData[] = (float)$row['tong_tien'];
}

// reset con trỏ để dùng lại cho bảng
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê đơn hàng</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: #f3f4f6;
            font-family: "Segoe UI", sans-serif;
        }
        .card-stat {
            border-radius: 16px;
            border: none;
        }
        .page-title {
            color: #28a745;
            font-weight: 800;
            margin-bottom: 20px;
        }
        #ordersByDayChart {
            max-height: 380px;
            margin: 0 auto 10px auto;
        }
    </style>
</head>

<body>
<?php
// navbar_admin.php nằm ở: admin/navbar_admin.php
include __DIR__ . '/../../navbar_admin.php';
?>

<div class="container mt-4 mb-5">

    <h2 class="page-title">
        <i class="fa-solid fa-chart-column me-2"></i>
        Thống kê tổng quan theo ngày
    </h2>


    <div class="card shadow card-stat">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Chi tiết theo ngày</h5>
            <table class="table table-hover text-center align-middle">
                <thead class="table-success">
                    <tr>
                        <th>Ngày</th>
                        <th>Số lượng đơn</th>
                        <th>Tổng tiền</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['ngay']) ?></strong></td>
                            <td><?= (int)$row['so_don'] ?></td>
                            <td><?= number_format($row['tong_tien']) ?>₫</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

<script>
    const ctx = document.getElementById('ordersByDayChart').getContext('2d');

    const labels       = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
    const soDonData    = <?= json_encode($soDonData) ?>;
    const tongTienData = <?= json_encode($tongTienData) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Số đơn',
                    data: soDonData,
                    yAxisID: 'y',
                },
                {
                    type: 'line',
                    label: 'Tổng tiền (₫)',
                    data: tongTienData,
                    yAxisID: 'y1',
                    tension: 0.3,
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Số đơn'
                    }
                },
                y1: {
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Tổng tiền (₫)'
                    }
                }
            }
        }
    });
</script>

</body>
</html>
