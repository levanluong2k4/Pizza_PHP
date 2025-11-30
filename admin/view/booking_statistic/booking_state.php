<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}


require __DIR__ . '/../../../includes/db_connect.php';

// ====== LẤY BỘ LỌC ======
$filter = $_GET['filter'] ?? 'homnay'; // mặc định hôm nay
$from_date = $_GET['from_date'] ?? null;

// ====== XỬ LÝ BỘ LỌC ======
switch ($filter) {

    case 'ngay': // Lọc theo ngày cụ thể
        $date = $from_date ?? date("Y-m-d");
        $title = "Trạng thái đặt bàn ngày " . date("d/m/Y", strtotime($date));
        $condition = "DATE(db.NgayGio) = '$date'";
        break;

    case 'thangnay':
        $month = date('m');
        $year = date('Y');
        $title = "Trạng thái đặt bàn trong tháng $month/$year";
        $condition = "MONTH(db.NgayGio) = $month AND YEAR(db.NgayGio) = $year";
        break;

    case 'namnay':
        $year = date('Y');
        $title = "Trạng thái đặt bàn trong năm $year";
        $condition = "YEAR(db.NgayGio) = $year";
        break;

    default: // Hôm nay
        $title = "Trạng thái đặt bàn hôm nay (" . date('d/m/Y') . ")";
        $condition = "DATE(db.NgayGio) = CURDATE()";
        break;
}

// ====== TRUY VẤN =========
// Chỉ lấy bản ghi đã thanh toán
$sql = "
SELECT 
    TrangThaiDatBan,
    COUNT(*) AS SoLuong
FROM datban db
WHERE db.TrangThaiThanhToan = 'dathanhtoan'
  AND $condition
GROUP BY TrangThaiDatBan
";

$kq = mysqli_query($ketnoi, $sql);

// Chuyển dữ liệu thành dạng mảng để dễ hiển thị
$trangthai = [
    'da_dat' => 0,
    'da_xac_nhan' => 0,
    'dang_su_dung' => 0,
    'thanh_cong' => 0,
    'da_huy' => 0
];

while ($row = mysqli_fetch_assoc($kq)) {
    $trangthai[$row['TrangThaiDatBan']] = $row['SoLuong'];
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Thống kê trạng thái đặt bàn</title>

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

        <h2 class="main-title">Thống kê trạng thái đặt bàn</h2>
        <h5 class="text-center mb-4"><?php echo $title; ?></h5>

        <form method="GET" class="row g-3 align-items-end">

            <!-- Lọc theo ngày -->
            <div class="col-auto">
                <label class="form-label">Chọn ngày:</label>
                <input type="date" name="from_date" class="form-control" value="<?php echo $from_date; ?>">
            </div>

            <div class="col-auto">
                <button class="btn btn-success" type="submit" name="filter" value="ngay">
                    Lọc ngày
                </button>
            </div>

            <!-- Các nút lọc nhanh -->
            <div class="col-auto d-flex gap-2 flex-wrap">
                <a href="?filter=homnay" class="btn btn-outline-success">Hôm nay</a>
                <a href="?filter=thangnay" class="btn btn-outline-success">Tháng này</a>
                <a href="?filter=namnay" class="btn btn-outline-success">Năm này</a>
            </div>

        </form>

    </div>

    <!-- BẢNG THỐNG KÊ -->
    <div class="container mt-4">
        <table class="table table-bordered table-hover text-center">
            <thead style="background:#28a745;color:white;">
                <tr>
                    <th>Trạng thái</th>
                    <th>Số lượng</th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <td>Đã đặt</td>
                    <td><?php echo $trangthai['da_dat']; ?></td>
                </tr>
                <tr>
                    <td>Đã xác nhận</td>
                    <td><?php echo $trangthai['da_xac_nhan']; ?></td>
                </tr>
                <tr>
                    <td>Đang sử dụng</td>
                    <td><?php echo $trangthai['dang_su_dung']; ?></td>
                </tr>
                <tr>
                    <td>Thành công</td>
                    <td><?php echo $trangthai['thanh_cong']; ?></td>
                </tr>
                <tr>
                    <td>Đã hủy</td>
                    <td><?php echo $trangthai['da_huy']; ?></td>
                </tr>

                <!-- TỔNG -->
                <tr style="background:#d6ffe2;font-weight:bold;">
                    <td>Tổng cộng</td>
                    <td>
                        <?php
                        echo array_sum($trangthai);
                        ?>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

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