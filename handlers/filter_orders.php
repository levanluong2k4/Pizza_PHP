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
    echo '<div class="d-flex justify-content-between mb-2">';
    echo '<b>Đơn hàng #' . $row['MaDH'] . '</b>';
    echo '<a href="detailt_order.php?madon=' . $row['MaDH'] . '">Xem chi tiết</a>';
    echo '</div>';

    echo '<div class="d-flex justify-content-between mb-2">';
    echo '<div><p class="mb-0"><b>Thời gian đặt hàng:</b> ' . date('d/m/Y H:i', strtotime($row['ngaydat'])) . '</p></div>';
    echo '<div><p class="mb-0"><span class="badge text-success fw-bolder fs-6">' . $row['trangthai'] . '</span></p></div>';
    echo '</div>';

    echo '<div class="d-flex justify-content-between mb-2">';
    echo '<div><strong>Tổng tiền:</strong> ' . number_format($row['TongTien'], 0, ',', '.') . '₫</div>';
    echo '</div>';

    echo '<div class="d-flex gap-2">';
    $count = 0;
    foreach ($danhSachAnh as $anh) {
        if ($count >= 3) break;
        echo '<img src="./' . trim($anh) . '" alt="Product" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">';
        $count++;
    }
    if (count($danhSachAnh) > 3) {
        echo '<div class="d-flex align-items-center justify-content-center img-thumbnail" style="width: 80px; height: 80px;">
                <span>+' . (count($danhSachAnh) - 3) . '</span>
              </div>';
    }
    echo '</div>'; // đóng danh sách ảnh

    
    echo '<div class="align-content-end d-flex p-0">';
    if ($row["trangthai"] == "Chờ xử lý") {
        echo '<button class="col-6">Hủy đơn hàng</button>';
        echo '<button class="col-6">Thanh toán</button>';
    } else {
        echo '<button class="col-12 border-end-0">Thanh toán</button>';
    }
    echo '</div>';
    echo '</div>';
}

} else {
    echo '<div class="text-center mt-4 p-4">
        <p class="text-muted">Không có đơn hàng nào với trạng thái này.</p>
    </div>';
}
?>