<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /unitop/backend/lesson/school/project_pizza/sign_in.php");
    exit();
}
require __DIR__ . '/../../../includes/db_connect.php';

// =============== BỘ LỌC ================
$filter = $_GET['filter'] ?? 'homnay';

switch ($filter) {
    case 'thang':
        $title = "Doanh thu tháng " . date('m/Y');
        $condition = "AND MONTH(db.NgayGio) = MONTH(CURDATE()) 
                      AND YEAR(db.NgayGio) = YEAR(CURDATE())";
        break;

    case 'nam':
        $title = "Doanh thu năm " . date('Y');
        $condition = "AND YEAR(db.NgayGio) = YEAR(CURDATE())";
        break;

    default:
        $title = "Doanh thu hôm nay (" . date('d/m/Y') . ")";
        $condition = "AND DATE(db.NgayGio) = CURDATE()";
        break;
}

// =============== TRUY VẤN CHÍNH ================
$sql = "
SELECT 
    DATE(db.NgayGio) AS Ngay,
    SUM(ctdb.SoLuong) AS TongSoLuong,
    SUM(ctdb.ThanhTien) AS DoanhThuNgay
FROM datban db
JOIN chitietdatban ctdb ON db.MaDatBan = ctdb.MaDatBan
WHERE db.TrangThaiThanhToan = 'dathanhtoan'
$condition
GROUP BY DATE(db.NgayGio)
ORDER BY Ngay DESC
";

$kq = mysqli_query($ketnoi, $sql);

// =============== TÍNH TỔNG DOANH THU ================
$sql_tong = "
SELECT SUM(ctdb.ThanhTien) AS TongDoanhThu
FROM datban db
JOIN chitietdatban ctdb ON db.MaDatBan = ctdb.MaDatBan
WHERE db.TrangThaiThanhToan = 'dathanhtoan'
$condition
";

$tong = mysqli_fetch_assoc(mysqli_query($ketnoi, $sql_tong))["TongDoanhThu"] ?? 0;


// =============== EXPORT ================
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_GET['export'])) {

    // Lấy dữ liệu để export
    $data = [];
    while ($row = mysqli_fetch_assoc($kq)) {
        $data[] = $row;
    }
    mysqli_data_seek($kq, 0);

    if ($_GET['export'] === 'excel') {
        // ===== Export Excel =====
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Ngày');
        $sheet->setCellValue('B1', 'Tổng số lượng');
        $sheet->setCellValue('C1', 'Doanh thu (VNĐ)');

        $rowIndex = 2;
        foreach ($data as $row) {
            $sheet->setCellValue("A{$rowIndex}", $row['Ngay']);
            $sheet->setCellValue("B{$rowIndex}", $row['TongSoLuong']);
            $sheet->setCellValue("C{$rowIndex}", $row['DoanhThuNgay']);
            $rowIndex++;
        }

        $sheet->setCellValue("A{$rowIndex}", "TỔNG DOANH THU");
        $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}");
        $sheet->setCellValue("C{$rowIndex}", $tong);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="thongke_datban.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    } elseif ($_GET['export'] === 'pdf') {
        // ===== Export PDF =====
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('Admin Panel');
        $pdf->SetAuthor('Hệ thống');
        $pdf->SetTitle('Thống kê doanh thu đặt bàn');

        // Quan trọng: font unicode hỗ trợ tiếng Việt
        $pdf->SetFont('dejavusans', '', 12);

        $pdf->AddPage();


        $html = "<h3 style='text-align:center;'>THỐNG KÊ DOANH THU ĐẶT BÀN</h3>";
        $html .= '<table border="1" cellspacing="0" cellpadding="5">
                <tr style="background-color:#28a745;color:#ffffff;">
                    <th>Ngày</th>
                    <th>Tổng số lượng</th>
                    <th>Doanh thu (VNĐ)</th>
                </tr>';

        foreach ($data as $r) {
            $html .= "<tr>
                        <td>{$r['Ngay']}</td>
                        <td>{$r['TongSoLuong']}</td>
                        <td>" . number_format($r['DoanhThuNgay']) . "</td>
                    </tr>";
        }

        $html .= "<tr style='background:#e7ffe7;font-weight:bold;'>
                    <td colspan='2'>TỔNG DOANH THU</td>
                    <td>" . number_format($tong) . "</td>
                  </tr>
                </table>";

        $pdf->writeHTML($html);
        $pdf->Output('thongke_datban.pdf', 'I');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Thống kê doanh thu đặt bàn</title>

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

        <form method="GET" class="d-flex gap-2 mb-3">

            <button type="submit" name="filter" value="homnay"
                class="btn btn-outline-success <?php echo ($filter == 'homnay') ? 'active' : ''; ?>">
                Hôm nay
            </button>

            <button type="submit" name="filter" value="thang"
                class="btn btn-outline-success <?php echo ($filter == 'thang') ? 'active' : ''; ?>">
                Tháng này
            </button>

            <button type="submit" name="filter" value="nam"
                class="btn btn-outline-success <?php echo ($filter == 'nam') ? 'active' : ''; ?>">
                Năm này
            </button>

            <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'excel'])); ?>"
                class="btn btn-primary ms-auto">
                Xuất Excel
            </a>

            <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'pdf'])); ?>"
                class="btn btn-danger">
                Xuất PDF
            </a>

        </form>

        <h4 class="text-center mb-3" style="color:#28a745"><?php echo $title; ?></h4>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Tổng số lượng</th>
                        <th>Doanh thu (VNĐ)</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($kq as $row) { ?>
                        <tr>
                            <td><?php echo $row['Ngay']; ?></td>
                            <td><?php echo $row['TongSoLuong']; ?></td>
                            <td><b><?php echo number_format($row['DoanhThuNgay']); ?> VNĐ</b></td>
                        </tr>
                    <?php } ?>

                    <tr style="background:#e7ffe7;font-weight:bold;">
                        <td colspan="2">TỔNG DOANH THU</td>
                        <td><?php echo number_format($tong); ?> VNĐ</td>
                    </tr>
                </tbody>
            </table>
        </div>
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