<?php
$conn = new mysqli("localhost", "root", "", "php_pizza");

$sql = "SELECT donhang.*, khachhang.HoTen 
        FROM donhang 
        LEFT JOIN khachhang ON donhang.MaKH = khachhang.MaKH
        WHERE trangthai = 'Giao thành công'
        ORDER BY MaDH DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Danh sách đơn hàng</title>

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


    </style>
</head>
<?php include "navbar_admin.php"; ?>    
<h2>Đơn hàng đã hoàn thành</h2>
<table border="1" cellspacing="0" cellpadding="10">
<tr>
    <th>Mã ĐH</th>
    <th>Khách hàng</th>
    <th>Ngày đặt</th>
    <th>Tổng tiền</th>
    <th>Chi tiết</th>

</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['MaDH'] ?></td>
    <td><?= $row['HoTen'] ?></td>
    <td><?= $row['ngaydat'] ?></td>
    <td><?= number_format($row['TongTien']) ?>₫</td>
    <td><a href="detail.php?MaDH=<?= $row['MaDH'] ?>">Xem</a></td>
</tr>
<?php endwhile; ?>
</table>

