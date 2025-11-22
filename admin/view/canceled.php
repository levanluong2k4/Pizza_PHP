<?php
$conn = new mysqli("localhost", "root", "", "pizza_company");

$sql = "SELECT donhang.*, khachhang.HoTen 
        FROM donhang 
        LEFT JOIN khachhang ON donhang.MaKH = khachhang.MaKH
        WHERE trangthai = 'Đã huỷ'
        ORDER BY MaDH DESC";
$result = $conn->query($sql);
?>

<h2>Đơn hàng đã huỷ</h2>
<?php include "tabs.php"; ?>

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
