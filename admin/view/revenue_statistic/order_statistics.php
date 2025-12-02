<?php
// order_statistics.php - Thống kê tổng quan đơn hàng
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}
require __DIR__ . '/../../../includes/db_connect.php';
if($_SESSION['phanquyen'] != 0){
    echo "Bạn không có quyền truy cập trang này.";
    exit();
}

$page = $_GET['page'] ?? 'overview';

// Lấy thống kê tổng quan
function getOverviewStats($ketnoi) {
    $stats = [];
    
    // Tổng số đơn hàng
    $sql = "SELECT COUNT(*) as total FROM donhang";
    $stats['total_orders'] = $ketnoi->query($sql)->fetch_assoc()['total'];
    
    // Tổng doanh thu
    $sql = "SELECT SUM(TongTien) as total FROM donhang WHERE trangthai NOT IN ('Đã hủy')";
    $stats['total_revenue'] = $ketnoi->query($sql)->fetch_assoc()['total'] ?? 0;
    
    // Đơn hàng theo trạng thái
    $sql = "SELECT trangthai, COUNT(*) as count, SUM(TongTien) as revenue 
            FROM donhang 
            GROUP BY trangthai";
    $result = $ketnoi->query($sql);
    $stats['by_status'] = [];
    while($row = $result->fetch_assoc()) {
        $stats['by_status'][] = $row;
    }
    
    // Đơn hàng hôm nay
    $sql = "SELECT COUNT(*) as count, COALESCE(SUM(TongTien), 0) as revenue 
            FROM donhang 
            WHERE DATE(ngaydat) = CURDATE()";
    $today = $ketnoi->query($sql)->fetch_assoc();
    $stats['today'] = $today;
    
    // Đơn hàng tháng này
    $sql = "SELECT COUNT(*) as count, COALESCE(SUM(TongTien), 0) as revenue 
            FROM donhang 
            WHERE MONTH(ngaydat) = MONTH(CURDATE()) 
            AND YEAR(ngaydat) = YEAR(CURDATE())";
    $month = $ketnoi->query($sql)->fetch_assoc();
    $stats['month'] = $month;
    
    // Top 5 sản phẩm bán chạy
    $sql = "SELECT sp.TenSP, SUM(ct.SoLuong) as total_sold, SUM(ct.SoLuong * sps.Gia) as revenue
            FROM chitietdonhang ct
            INNER JOIN sanpham sp ON ct.MaSP = sp.MaSP
            INNER JOIN sanpham_size sps ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize
            GROUP BY ct.MaSP
            ORDER BY total_sold DESC
            LIMIT 5";
    $result = $ketnoi->query($sql);
    $stats['top_products'] = [];
    while($row = $result->fetch_assoc()) {
        $stats['top_products'][] = $row;
    }
    
    return $stats;
}

// Lấy thống kê theo thời gian
function getTimeStats($ketnoi, $period = '7days') {
    $stats = [];
    
    switch($period) {
        case '7days':
            $sql = "SELECT DATE(ngaydat) as date, COUNT(*) as count, COALESCE(SUM(TongTien), 0) as revenue
                    FROM donhang
                    WHERE ngaydat >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    GROUP BY DATE(ngaydat)
                    ORDER BY date";
            break;
        case '30days':
            $sql = "SELECT DATE(ngaydat) as date, COUNT(*) as count, COALESCE(SUM(TongTien), 0) as revenue
                    FROM donhang
                    WHERE ngaydat >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY DATE(ngaydat)
                    ORDER BY date";
            break;
        case 'year':
            $sql = "SELECT DATE_FORMAT(ngaydat, '%Y-%m') as date, COUNT(*) as count, COALESCE(SUM(TongTien), 0) as revenue
                    FROM donhang
                    WHERE YEAR(ngaydat) = YEAR(CURDATE())
                    GROUP BY DATE_FORMAT(ngaydat, '%Y-%m')
                    ORDER BY date";
            break;
    }
    
    $result = $ketnoi->query($sql);
    while($row = $result->fetch_assoc()) {
        $stats[] = $row;
    }
    
    return $stats;
}

$overview = getOverviewStats($ketnoi);
$timeStats = getTimeStats($ketnoi, $_GET['period'] ?? '7days');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê đơn hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .nav-tabs {
            display: flex;
            gap: 10px;
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .nav-tab {
            padding: 12px 24px;
            text-decoration: none;
            color: #555;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .nav-tab:hover {
            background: #f0f2f5;
            color: #4CAF50;
        }
        
        .nav-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        
        .stat-card.blue { border-color: #3498db; }
        .stat-card.green { border-color: #2ecc71; }
        .stat-card.orange { border-color: #f39c12; }
        .stat-card.purple { border-color: #9b59b6; }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-title {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .stat-card.blue .stat-icon { background: #e3f2fd; color: #3498db; }
        .stat-card.green .stat-icon { background: #e8f5e9; color: #2ecc71; }
        .stat-card.orange .stat-icon { background: #fff3e0; color: #f39c12; }
        .stat-card.purple .stat-icon { background: #f3e5f5; color: #9b59b6; }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-subtitle {
            color: #95a5a6;
            font-size: 13px;
        }
        
        .chart-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .chart-header h3 {
            color: #2c3e50;
            font-size: 20px;
        }
        
        .period-filter {
            display: flex;
            gap: 10px;
        }
        
        .period-btn {
            padding: 8px 16px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            color: #666;
        }
        
        .period-btn:hover {
            border-color: #4CAF50;
            color: #4CAF50;
        }
        
        .period-btn.active {
            background: #4CAF50;
            border-color: #4CAF50;
            color: white;
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .table-header {
            padding: 20px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 16px 25px;
            text-align: left;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #e0e0e0;
        }
        
        td {
            border-bottom: 1px solid #f0f0f0;
            color: #555;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        .back-btn {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-btn:hover {
            background: #5a6268;
            transform: translateX(-3px);
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }
        
        @media (max-width: 968px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
      <?php include '../../navbar_admin.php'; ?>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-chart-line"></i>
                Thống kê đơn hàng
            </h1>
            <a href="/unitop/backend/lesson/school/project_pizza/admin/view/order/order_list.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
        
        <div class="nav-tabs">
            <a href="?page=overview" class="nav-tab <?php echo $page == 'overview' ? 'active' : ''; ?>">
                <i class="fa-solid fa-list-alt"></i> Tổng quan
            </a>
            <a href="?page=status" class="nav-tab <?php echo $page == 'status' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-pie"></i> Theo trạng thái
            </a>
            <a href="?page=timeline" class="nav-tab <?php echo $page == 'timeline' ? 'active' : ''; ?>">
                <i class="fa-solid fa-clock"></i> Theo thời gian
            </a>
        </div>
        
        <?php if ($page == 'overview'): ?>
            <!-- TỔNG QUAN -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-header">
                        <div class="stat-title">Tổng đơn hàng</div>
                        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                    </div>
                    <div class="stat-value"><?php echo number_format($overview['total_orders']); ?></div>
                    <div class="stat-subtitle">Tất cả đơn hàng</div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-header">
                        <div class="stat-title">Tổng doanh thu</div>
                        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    </div>
                    <div class="stat-value"><?php echo number_format($overview['total_revenue'], 0, ',', '.'); ?>đ</div>
                    <div class="stat-subtitle">Doanh thu tích lũy</div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-header">
                        <div class="stat-title">Hôm nay</div>
                        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $overview['today']['count']; ?> đơn</div>
                    <div class="stat-subtitle"><?php echo number_format($overview['today']['revenue'], 0, ',', '.'); ?>đ</div>
                </div>
                
                <div class="stat-card purple">
                    <div class="stat-header">
                        <div class="stat-title">Tháng này</div>
                        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $overview['month']['count']; ?> đơn</div>
                    <div class="stat-subtitle"><?php echo number_format($overview['month']['revenue'], 0, ',', '.'); ?>đ</div>
                </div>
            </div>
            
            <div class="grid-2">
                <div class="chart-container">
                    <h3 style="margin-bottom: 20px; color: #2c3e50;">
                        <i class="fas fa-chart-pie"></i> Đơn hàng theo trạng thái
                    </h3>
                    <canvas id="statusChart"></canvas>
                </div>
                
                <div class="table-container">
                    <div class="table-header">
                        <h3><i class="fas fa-fire"></i> Top 5 sản phẩm bán chạy</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đã bán</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($overview['top_products'] as $product): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($product['TenSP']); ?></strong></td>
                                <td><?php echo $product['total_sold']; ?> sp</td>
                                <td><strong style="color: #2ecc71;"><?php echo number_format($product['revenue'], 0, ',', '.'); ?>đ</strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        <?php elseif ($page == 'status'): ?>
            <!-- THEO TRẠNG THÁI -->
            <div class="stats-grid">
                <?php foreach($overview['by_status'] as $status): 
                    $colors = [
                        'Chờ xử lý' => 'orange',
                        'Đã xác nhận' => 'blue',
                        'Đang giao' => 'purple',
                        'Đã giao' => 'green',
                        'Đã hủy' => 'orange'
                    ];
                    $color = $colors[$status['trangthai']] ?? 'blue';
                ?>
                <div class="stat-card <?php echo $color; ?>">
                    <div class="stat-header">
                        <div class="stat-title"><?php echo htmlspecialchars($status['trangthai']); ?></div>
                        <div class="stat-icon"><i class="fas fa-box"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $status['count']; ?> đơn</div>
                    <div class="stat-subtitle"><?php echo number_format($status['revenue'], 0, ',', '.'); ?>đ</div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="grid-2">
                <div class="chart-container">
                    <h3 style="margin-bottom: 20px;">Biểu đồ tròn - Số lượng đơn</h3>
                    <canvas id="pieChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3 style="margin-bottom: 20px;">Biểu đồ cột - Doanh thu</h3>
                    <canvas id="barChart"></canvas>
                </div>
            </div>
            
        <?php else: ?>
            <!-- THEO THỜI GIAN -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-line"></i> Biểu đồ đơn hàng theo thời gian</h3>
                    <div class="period-filter">
                        <button class="period-btn <?php echo ($_GET['period'] ?? '7days') == '7days' ? 'active' : ''; ?>" 
                                onclick="location.href='?page=timeline&period=7days'">7 ngày</button>
                        <button class="period-btn <?php echo ($_GET['period'] ?? '') == '30days' ? 'active' : ''; ?>"
                                onclick="location.href='?page=timeline&period=30days'">30 ngày</button>
                        <button class="period-btn <?php echo ($_GET['period'] ?? '') == 'year' ? 'active' : ''; ?>"
                                onclick="location.href='?page=timeline&period=year'">Năm nay</button>
                    </div>
                </div>
                <canvas id="timeChart"></canvas>
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-table"></i> Chi tiết theo thời gian</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Số đơn hàng</th>
                            <th>Doanh thu</th>
                            <th>Trung bình/đơn</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($timeStats as $stat): ?>
                        <tr>
                            <td><strong><?php echo date('d/m/Y', strtotime($stat['date'])); ?></strong></td>
                            <td><?php echo $stat['count']; ?> đơn</td>
                            <td><strong style="color: #2ecc71;"><?php echo number_format($stat['revenue'], 0, ',', '.'); ?>đ</strong></td>
                            <td><?php echo number_format($stat['revenue'] / max($stat['count'], 1), 0, ',', '.'); ?>đ</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        const statusData = <?php echo json_encode($overview['by_status']); ?>;
        const timeData = <?php echo json_encode($timeStats); ?>;
        
        // Chart cho tổng quan - Status Doughnut
        <?php if ($page == 'overview'): ?>
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusData.map(s => s.trangthai),
                datasets: [{
                    data: statusData.map(s => s.count),
                    backgroundColor: ['#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#e74c3c']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        <?php endif; ?>
        
        // Charts cho status page
        <?php if ($page == 'status'): ?>
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: statusData.map(s => s.trangthai),
                datasets: [{
                    data: statusData.map(s => s.count),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: statusData.map(s => s.trangthai),
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: statusData.map(s => s.revenue),
                    backgroundColor: '#4CAF50'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + 'đ';
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        // Chart cho timeline
        <?php if ($page == 'timeline'): ?>
        new Chart(document.getElementById('timeChart'), {
            type: 'line',
            data: {
                labels: timeData.map(d => {
                    const date = new Date(d.date);
                    return date.toLocaleDateString('vi-VN');
                }),
                datasets: [{
                    label: 'Số đơn hàng',
                    data: timeData.map(d => d.count),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    yAxisID: 'y',
                    tension: 0.3
                }, {
                    label: 'Doanh thu (VNĐ)',
                    data: timeData.map(d => d.revenue),
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    yAxisID: 'y1',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Số đơn hàng' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Doanh thu (VNĐ)' },
                        grid: { drawOnChartArea: false },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + 'đ';
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>