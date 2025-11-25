<?php
$conn = new mysqli("localhost", "root", "", "php_pizza");

$sql = "SELECT donhang.*, khachhang.HoTen 
        FROM donhang 
        LEFT JOIN khachhang ON donhang.MaKH = khachhang.MaKH
        ORDER BY MaDH DESC";
$result = $conn->query($sql);
?>

<h2>Danh sách tất cả đơn hàng</h2>
<?php include "tabs.php"; ?>

<table border="1" cellspacing="0" cellpadding="10">
<tr>
    <th>Mã ĐH</th>
    <th>Khách hàng</th>
    <th>Ngày đặt</th>
    <th>Tổng tiền</th>
    <th>Trạng thái</th>
    <th>Chi tiết</th>
</tr>

<?php
// Lặp qua từng đơn hàng
while ($row = $result->fetch_assoc()) {
?>
    <tr>
        <td><?php echo $row['MaDH']; ?></td>
        <td><?php echo $row['HoTen']; ?></td>
        <td><?php echo $row['ngaydat']; ?></td>
        <td><?php echo number_format($row['TongTien']); ?>₫</td>
        <td><?php echo $row['trangthai']; ?></td>

        <td>
            <a href="detail.php?MaDH=<?php echo $row['MaDH']; ?>">
                Xem
            </a>
        </td>
    </tr>
<?php
} 
?>
</table>
