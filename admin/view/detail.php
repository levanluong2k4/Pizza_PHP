<?php
$conn = new mysqli("localhost", "root", "", "pizza_company");

$MaDH = $_GET['MaDH'];

$order = $conn->query("
    SELECT donhang.*, khachhang.HoTen 
    FROM donhang 
    LEFT JOIN khachhang ON donhang.MaKH = khachhang.MaKH
    WHERE donhang.MaDH = $MaDH
")->fetch_assoc();

$items = $conn->query("
    SELECT chitietdonhang.*, sanpham.TenSP, size.TenSize
    FROM chitietdonhang
    JOIN sanpham ON chitietdonhang.MaSP = sanpham.MaSP
    LEFT JOIN size ON chitietdonhang.MaSize = size.MaSize
    WHERE MaDH = $MaDH
");
?>

<h2>Chi tiết đơn hàng #<?= $MaDH ?></h2>
<?php include "tabs.php"; ?>

<p><b>Khách hàng:</b> <?= $order['HoTen'] ?></p>
<p><b>Ngày đặt:</b> <?= $order['ngaydat'] ?></p>
<p><b>Địa chỉ:</b> <?= $order['diachinguoinhan'] ?></p>
<p><b>SĐT:</b> <?= $order['sdtnguoinhan'] ?></p>

<h3>Cập nhật trạng thái</h3>

<form action="update_status.php" method="POST">
    <input type="hidden" name="MaDH" value="<?= $MaDH ?>">

    <select name="trangthai">
        <option <?= $order['trangthai']=='Chờ xử lý'?'selected':'' ?>>Chờ xử lý</option>
        <option <?= $order['trangthai']=='Đang giao'?'selected':'' ?>>Đang giao</option>
        <option <?= $order['trangthai']=='Giao thành công'?'selected':'' ?>>Giao thành công</option>
        <option <?= $order['trangthai']=='Đã huỷ'?'selected':'' ?>>Đã huỷ</option>
    </select>

    <button type="submit">Cập nhật</button>
</form>

<h3>Danh sách sản phẩm</h3>

<table border="1" cellspacing="0" cellpadding="10">
<tr>
    <th>Sản phẩm</th>
    <th>Size</th>
    <th>Số lượng</th>
    <th>Thành tiền</th>
</tr>

<?php while($row = $items->fetch_assoc()): ?>
<tr>
    <td><?= $row['TenSP'] ?></td>
    <td><?= $row['TenSize'] ?></td>
    <td><?= $row['SoLuong'] ?></td>
    <td><?= number_format($row['ThanhTien']) ?>₫</td>
</tr>
<?php endwhile; ?>

</table>
