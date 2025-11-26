<?php
// Kết nối DB
$conn = new mysqli("localhost", "root", "", "php_pizza");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

/* 1. Lấy số đơn theo ngày (30 ngày gần nhất) */
$sql = "
    SELECT DATE(ngaydat) AS ngay, COUNT(*) AS so_don
    FROM donhang
    GROUP BY DATE(ngaydat)
    ORDER BY ngay ASC
    LIMIT 30
";

$result = $conn->query($sql);

$labels = [];
$data   = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['ngay'];
        $data[]   = (int)$row['so_don'];
    }
}

$total = array_sum($data);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đơn hàng theo thời gian</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: #f4f5f7;
            font-family: "Segoe UI", sans-serif;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            color: #0d6efd;
        }

        #timeChart {
            max-height: 360px;
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/../../navbar_admin.php'; ?>

<div class="container mt-4 mb-5">

    <h2 class="page-title mb-3">
        <i class="fa-solid fa-chart-line me-2"></i>
        Đơn hàng theo thời gian
    </h2>

    <div class="card shadow">
        <div class="card-body">

            <div class="row g-4">

                <!-- Biểu đồ -->
                <div class="col-md-8">
                    <canvas id="timeChart"></canvas>
                </div>

                <!-- Bảng đơn giản -->
                <div class="col-md-4">
                    <h5 class="fw-bold">Chi tiết</h5>

                    <table class="table table-sm text-center align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Ngày</th>
                                <th>Số đơn</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($labels as $i => $ngay): ?>
                                <tr>
                                    <td><?= $ngay ?></td>
                                    <td><?= $data[$i] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <p class="mt-3 fw-bold">
                        Tổng đơn: <?= $total ?>
                    </p>
                </div>

            </div>

        </div>
    </div>

</div>

<script>
    const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
    const data   = <?= json_encode($data) ?>;

    new Chart(document.getElementById('timeChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: "Số đơn hàng",
                data: data,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.15)',
                borderWidth: 2,
                tension: 0.25,
                pointRadius: 4,
                pointBackgroundColor: '#0d6efd',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
</script>

</body>
</html>
