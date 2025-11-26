<?php
$conn = new mysqli("localhost", "root", "", "php_pizza");

$sql = "SELECT donhang.*, khachhang.HoTen 
        FROM donhang 
        LEFT JOIN khachhang ON donhang.MaKH = khachhang.MaKH
        WHERE trangthai = 'Đã huỷ'
        ORDER BY MaDH DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<title>đơn hàng đã huỷ</title>
<head>
    <meta charset="UTF-8">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        body {
            background-color: #f3f4f6;
            font-family: "Segoe UI", sans-serif;
        }

        .page-title {
            font-size: 26px;
            font-weight: bold;
            color: #28a745;
        }

        .order-card {
            border-radius: 12px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            color: #fff;
        }

        .dang-xu-ly {
            background: #f1c40f;      /* btn-warning */
        }

        .hoan-thanh {
            background: #007bff;      /* btn-primary */
        }

        .da-huy {
            background: #e74c3c;      /* btn-danger */
        }

    </style>
</head>

<body>

    <?php include "navbar_admin.php"; ?>

    <div class="container mt-4">

        <h2 class="page-title mb-3"><i class="fa-solid fa-receipt"></i> Đơn hàng đã huỷ</h2>
        <div class="card shadow order-card mt-3">
            <div class="card-body">

                <table class="table table-hover text-center align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Khách hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>

                    <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['MaDH'] ?></td>
                    <td><?= $row['HoTen'] ?></td>
                    <td><?= $row['ngaydat'] ?></td>
                    <td><?= number_format($row['TongTien']) ?>₫</td>
                    <td><a href="detail.php?MaDH=<?= $row['MaDH'] ?>" class="btn btn-outline-success btn-sm">
                        <i class="fa-solid fa-eye"></i> Xem</a></td>
                </tr>
                <?php endwhile; ?>
                    </tbody>

                </table>

            </div>
        </div>
    </div>

</body>
</html>

