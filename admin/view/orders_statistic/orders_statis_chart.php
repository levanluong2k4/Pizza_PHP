<?php
// Kết nối DB
$conn = new mysqli("localhost", "root", "", "php_pizza");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

/* 1. Khởi tạo 4 trạng thái cố định */
$stats = [
    "Chờ xử lý" => 0,
    "Đang giao" => 0,
    "Hoàn thành" => 0,
    "Đã huỷ"    => 0,
];

/* 2. Gom trạng thái từ donhang về 4 nhóm trên */
$sql = "
    SELECT 
        CASE
            WHEN trangthai = 'Chờ xử lý' THEN 'Chờ xử lý'
            WHEN trangthai IN ('Chờ giao', 'Đang giao') THEN 'Đang giao'
            WHEN trangthai IN ('Giao thành công', 'Hoàn thành') THEN 'Hoàn thành'
            WHEN trangthai = 'Đã huỷ' THEN 'Đã huỷ'
        END AS st,
        COUNT(*) AS so_don
    FROM donhang
    GROUP BY st
    HAVING st IS NOT NULL
";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $st = $row['st'];
        if (isset($stats[$st])) {
            $stats[$st] = (int)$row['so_don'];
        }
    }
}

/* 3. Chuẩn bị dữ liệu cho Chart.js & bảng bên phải */
$labels = array_keys($stats);   // ["Chờ xử lý","Đang giao","Hoàn thành","Đã huỷ"]
$data   = array_values($stats); // [.., .., .., ..]
$total  = array_sum($data);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tỷ lệ đơn hàng theo trạng thái</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background-color: #f3f4f6;
            font-family: "Segoe UI", sans-serif;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            color: #28a745;
        }

        .card-stat {
            border-radius: 16px;
            border: none;
        }

        #orderStatusChart {
            max-height: 380px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            #orderStatusChart {
                max-height: 300px;
            }
        }
    </style>
</head>

<body>

<?php
include __DIR__ . '/../../navbar_admin.php';
?>

<div class="container mt-4 mb-5">

    <h2 class="page-title mb-3">
        <i class="fa-solid fa-chart-pie me-2"></i>
        Tỷ lệ đơn hàng theo trạng thái
    </h2>

    <div class="card shadow card-stat">
        <div class="card-body">
            <div class="row g-4">
                <!-- Biểu đồ -->
                <div class="col-md-7">
                    <canvas id="orderStatusChart"></canvas>
                </div>

                <!-- Bảng chi tiết -->
                <div class="col-md-5">
                    <h5 class="fw-bold mb-3">Chi tiết</h5>
                    <table class="table table-sm align-middle text-center">
                        <thead class="table-success">
                            <tr>
                                <th>Trạng thái</th>
                                <th>Số đơn</th>
                                <th>Tỷ lệ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($labels as $i => $label): 
                                $count   = $data[$i];
                                $percent = $total > 0 ? round($count * 100 / $total, 1) : 0;
                            ?>
                                <tr>
                                    <td class="text-start"><?= htmlspecialchars($label) ?></td>
                                    <td><?= $count ?></td>
                                    <td><?= $percent ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
    const ctx = document.getElementById('orderStatusChart').getContext('2d');

    const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
    const data   = <?= json_encode($data) ?>;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#f1c40f', // Chờ xử lý  - vàng
                    '#28a745', // Đang giao  - xanh lá
                    '#007bff', // Hoàn thành - xanh dương
                    '#e74c3c', // Đã huỷ     - đỏ
                ],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '60%', // lỗ giữa rộng hơn cho đẹp
            plugins: {
                legend: {
                    display: false  // ẩn legend, vì đã có bảng chi tiết bên phải
                }
            }
        }
    });
</script>

</body>
</html>
