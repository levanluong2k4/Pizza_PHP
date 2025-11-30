<?php
session_start();
require "../includes/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../trangchu.php");
    exit();
}

$id = $_SESSION['user_id'];
$trangthai = $_POST["trangthai"] ?? "all";

// Xây dựng câu SQL
if ($trangthai == "all" || strtolower($trangthai) == "all") {
    // Lấy tất cả đơn hàng
    $sql = "SELECT 
        donhang.MaDH,
        donhang.MaDHcode,
        donhang.phuongthucthanhtoan,
        donhang.trangthaithanhtoan,
        donhang.ngaydat,
        donhang.trangthai,
        donhang.is_guest,
        donhang.TongTien,
        GROUP_CONCAT(DISTINCT sanpham_size.Anh SEPARATOR ',') as DanhSachAnh
    FROM donhang, chitietdonhang, sanpham_size, khachhang
    WHERE donhang.MaDH = chitietdonhang.MaDH
        AND chitietdonhang.MaSP = sanpham_size.MaSP
        AND chitietdonhang.MaSize = sanpham_size.MaSize
        AND donhang.MaKH = khachhang.MaKH
        AND donhang.MaKH = '$id'
    GROUP BY donhang.MaDH
    ORDER BY donhang.ngaydat DESC";
} else {
    // Lọc theo trạng thái
    $sql = "SELECT 
        donhang.MaDH,
        donhang.ngaydat,
        donhang.trangthai,
        donhang.MaDHcode,
        donhang.phuongthucthanhtoan,
        donhang.trangthaithanhtoan,
        donhang.is_guest,
        donhang.TongTien,
        GROUP_CONCAT(DISTINCT sanpham_size.Anh SEPARATOR ',') as DanhSachAnh
    FROM donhang, chitietdonhang, sanpham_size, khachhang
    WHERE donhang.MaDH = chitietdonhang.MaDH
        AND chitietdonhang.MaSP = sanpham_size.MaSP
        AND chitietdonhang.MaSize = sanpham_size.MaSize
        AND donhang.MaKH = khachhang.MaKH
        AND donhang.MaKH = '$id'
        AND donhang.trangthai = '$trangthai'
    GROUP BY donhang.MaDH
    ORDER BY donhang.ngaydat DESC";
}

$result = mysqli_query($ketnoi, $sql);

if (mysqli_num_rows($result) > 0) {
while ($row = mysqli_fetch_assoc($result)) {
    $danhSachAnh = explode(',', $row['DanhSachAnh']);

   echo '<div class="order-item mb-3 p-3 border rounded">';

echo '<div class="d-flex justify-content-between mb-2">
        <b>Đơn hàng #' . $row['MaDHcode'] . '</b>
        <a href="detail_order_user.php?madon=' . $row['MaDHcode'] . '">Xem chi tiết</a>
      </div>';

echo '<div class="d-flex justify-content-between mb-2">
        <div><p class="mb-0"><b>Thời gian đặt hàng:</b> ' . date('d/m/Y H:i', strtotime($row['ngaydat'])) . '</p></div>
        <div><p class="mb-0"><span class="badge text-success fw-bolder fs-6">' . $row['trangthai'] . '</span></p></div>
      </div>';

echo '<div class="d-flex justify-content-between mb-2">
        <strong>Tổng tiền:</strong><div class="text-danger"> ' . number_format($row['TongTien'], 0, ',', '.') . '₫</div>
     
     
        </div>';


// Phương thức thanh toán
echo '<div class="d-flex justify-content-between mb-2">
        <div><strong>Phương thức thanh toán:</strong></div>
        <div>';

echo '<div class="fw-bold">' . $row["phuongthucthanhtoan"] . '</div>';

if ($row["trangthaithanhtoan"] == "chuathanhtoan") {
    echo '<div class="text-danger fw-bold">Chưa thanh toán</div>';
} else {
    echo '<div class="text-success fw-bold">Đã thanh toán</div>';
}

echo '</div></div>';


// Ảnh
echo '<div class="d-flex gap-2">';
$count = 0;
foreach ($danhSachAnh as $anh) {
    if ($count >= 3) break;
    echo '<img src="./' . trim($anh) . '" class="img-thumbnail" style="width:80px;height:80px;object-fit:cover;">';
    $count++;
}
if (count($danhSachAnh) > 3) {
    echo '<div class="d-flex align-items-center justify-content-center img-thumbnail" style="width:80px;height:80px;">
            <span>+' . (count($danhSachAnh) - 3) . '</span>
          </div>';
}
echo '</div>'; // đóng ảnh


// Form thanh toán
echo '<form action="./handlers/pay_order.php" method="post">
        <input type="hidden" name="order_id" value="' . $row["MaDHcode"] . '">';

if ($row["trangthaithanhtoan"] == "chuathanhtoan") {
    echo '<div class="form-section">
            <div class="form-section-title fw-bold">
                <i class="fa-solid fa-credit-card"></i> Thanh toán trực tuyến
            </div>

            <div class="d-flex justify-content-around align-items-center mb-2">
                <div class="d-flex align-items-center">
                    <input class="form-check-input me-2" type="radio" name="transfer_method" value="momo" checked>
                    <label class="form-check-label">
                        <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" style="height: 50px;"> MoMo
                    </label>
                </div>

                <div class="d-flex align-items-center">
                    <input class="form-check-input me-2" type="radio" name="transfer_method" value="vnpay">
                    <label class="form-check-label">
                        <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png" style="height: 50px;"> VNPay
                    </label>
                </div>
            </div>
          </div>';
}

echo '<div class="align-content-end d-flex p-0">';

if ($row["trangthai"] == "Chờ xử lý" && $row["trangthaithanhtoan"] != "chuathanhtoan") {
    echo '<button data-order_id="' . $row["MaDH"] . '" class="col-12 btn-cancel_order">Hủy đơn hàng</button>';

} else if ($row["trangthai"] == "Chờ xử lý" && $row["trangthaithanhtoan"] == "chuathanhtoan") {
    echo '<button data-order_id="' . $row["MaDH"] . '" class="col-6 btn-cancel_order">Hủy đơn hàng</button>
          <button type="submit" name="btn_pay_order" class="col-6 btn-pay_order">Thanh toán</button>';

} else if ($row["trangthai"] != "Chờ xử lý" && $row["trangthaithanhtoan"] == "chuathanhtoan") {
    echo '<button type="submit" name="btn_pay_order" class="col-12 btn-pay_order">Thanh toán</button>';
}

echo '</div></form>';

echo '</div>'; // đóng order-item

    


}

} else {
    echo '<div class="text-center mt-4 p-4">
        <p class="text-muted">Không có đơn hàng nào với trạng thái này.</p>
    </div>';
}
?>