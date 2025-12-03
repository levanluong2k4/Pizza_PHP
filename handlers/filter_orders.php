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
        
        // Xác định class tracking dựa trên trạng thái
        $trackingClass = '';
        $statusBadgeClass = '';
        $statusDescription = '';
        
        switch($row['trangthai']) {
            case 'Chờ xử lý':
                $trackingClass = 'tracking-choxuly';
                $statusBadgeClass = 'bg-warning';
                $statusDescription = 'Đơn hàng đang được xác nhận bởi cửa hàng';
                break;
            case 'Chờ giao':
                $trackingClass = 'tracking-chogiao';
                $statusBadgeClass = 'bg-info';
                $statusDescription = 'Đơn hàng đang được chuẩn bị và đóng gói';
                break;
            case 'Đang giao':
                $trackingClass = 'tracking-danggiao';
                $statusBadgeClass = 'bg-primary';
                $statusDescription = 'Đơn hàng đang trên đường giao đến bạn';
                break;
            case 'Giao thành công':
                $trackingClass = 'tracking-giaothanhcong';
                $statusBadgeClass = 'bg-success';
                $statusDescription = 'Đơn hàng đã được giao thành công';
                break;
            case 'Hủy đơn':
                $trackingClass = 'tracking-huy';
                $statusBadgeClass = 'bg-danger';
                $statusDescription = 'Đơn hàng đã bị hủy';
                break;
            default:
                $trackingClass = '';
                $statusBadgeClass = 'bg-secondary';
                $statusDescription = 'Đang cập nhật trạng thái';
        }
        
        echo '<div class="order-item mb-3 p-3 border rounded">';
        
        // Header với mã đơn hàng
        echo '<div class="d-flex justify-content-between mb-2">
                <b>Đơn hàng #' . htmlspecialchars($row['MaDHcode']) . '</b>
                <a href="detail_order_user.php?madon=' . htmlspecialchars($row['MaDH']) . '">Xem chi tiết ></a>
              </div>';
        
        // Tracking Section
        echo '<div class="order-status-tracking ' . $trackingClass . '">
                <div class="tracking-container">
                    <div class="tracking-steps">';
        
        // Step 1: Chờ xử lý
        $step1Active = in_array($row['trangthai'], ['Chờ xử lý', 'Chờ giao', 'Đang giao', 'Giao thành công', 'Hủy đơn']);
        echo '<div class="tracking-step step-1">
                <div class="step-icon ' . ($step1Active ? ($row['trangthai'] == 'Hủy đơn' ? 'cancelled' : 'active') : '') . '">
                    <img src="./img/hinhthuc_2.png" alt="" width="30" height="30">
                </div>
                <div class="step-text ' . ($step1Active ? ($row['trangthai'] == 'Hủy đơn' ? 'cancelled' : 'active') : '') . '">
                    Chờ xử lý
                </div>
              </div>';
        
        // Step 2: Chuẩn bị hàng (chỉ hiện nếu không phải Hủy đơn)
        if ($row['trangthai'] != 'Hủy đơn') {
            $step2Active = in_array($row['trangthai'], ['Chờ giao', 'Đang giao', 'Giao thành công']);
            echo '<div class="tracking-step step-2">
                    <div class="step-icon ' . ($step2Active ? 'active' : '') . '">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="step-text ' . ($step2Active ? 'active' : '') . '">
                        Soạn hàng
                    </div>
                  </div>';
        }
        
        // Step 3: Đang giao (chỉ hiện nếu không phải Hủy đơn)
        if ($row['trangthai'] != 'Hủy đơn') {
            $step3Active = in_array($row['trangthai'], ['Đang giao', 'Giao thành công']);
            echo '<div class="tracking-step step-3">
                    <div class="step-icon ' . ($step3Active ? 'active' : '') . '">
                       <img src="./img/hinhthuc_1.png" alt="" width="30" height="30">
                    </div>
                    <div class="step-text ' . ($step3Active ? 'active' : '') . '">
                        Đang giao
                    </div>
                  </div>';
        }
        
        // Step 4: Giao hàng (chỉ hiện nếu không phải Hủy đơn)
        if ($row['trangthai'] != 'Hủy đơn') {
            $step4Active = $row['trangthai'] == 'Giao thành công';
            echo '<div class="tracking-step step-4">
                    <div class="step-icon ' . ($step4Active ? 'active' : '') . '">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="step-text ' . ($step4Active ? 'active' : '') . '">
                        Đã giao
                    </div>
                  </div>';
        }
        
        // Step 5: Hủy (chỉ hiện nếu là Hủy đơn)
        if ($row['trangthai'] == 'Hủy đơn') {
            echo '<div class="tracking-step step-5">
                    <div class="step-icon cancelled">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="step-text cancelled">
                        Đã hủy
                    </div>
                  </div>';
        }
        
        echo '</div>'; // đóng tracking-steps
        
        // Status description
        echo '<div class="text-center mt-3">
                <small class="text-muted">' . $statusDescription . '</small>
              </div>';
        
        echo '</div></div>'; // đóng tracking-container và order-status-tracking
        
        // Thông tin đơn hàng
        echo '<div class="d-flex justify-content-between mb-2">
                <div><p class="mb-0"><b>Thời gian đặt hàng:</b> ' . date('d/m/Y H:i', strtotime($row['ngaydat'])) . '</p></div>
                <div><p class="mb-0"><span class="badge ' . $statusBadgeClass . ' text-white fw-bolder fs-6">' . htmlspecialchars($row['trangthai']) . '</span></p></div>
              </div>';
        
        echo '<div class="d-flex justify-content-between mb-2">
                <div><strong>Tổng tiền:</strong></div>
                <div class="text-danger fw-bold">' . number_format($row['TongTien'], 0, ',', '.') . '₫</div>
              </div>';
        
        // Phương thức thanh toán
        echo '<div class="d-flex justify-content-between mb-2">
                <div><strong>Phương thức thanh toán:</strong></div>
                <div>
                    <div class="fw-bold">' . htmlspecialchars($row["phuongthucthanhtoan"]) . '</div>';
        
        if ($row["trangthaithanhtoan"] == "chuathanhtoan") {
            echo '<div class="text-danger fw-bold">Chưa thanh toán</div>';
        } else {
            echo '<div class="text-success fw-bold">Đã thanh toán</div>';
        }
        
        echo '</div></div>';
        
        // Ảnh sản phẩm
        echo '<div class="d-flex gap-2 mb-3">';
        $count = 0;
        foreach ($danhSachAnh as $anh) {
            if ($count >= 3) break;
            if (!empty(trim($anh))) {
                echo '<img src="./' . htmlspecialchars(trim($anh)) . '" class="img-thumbnail" style="width:80px;height:80px;object-fit:contain;">';
                $count++;
            }
        }
        if (count($danhSachAnh) > 3) {
            echo '<div class="d-flex align-items-center justify-content-center img-thumbnail" style="width:80px;height:80px;background:#f8f9fa;">
                    <span>+' . (count($danhSachAnh) - 3) . '</span>
                  </div>';
        }
        echo '</div>';
        
        // Form thanh toán
        echo '<form action="./handlers/pay_order.php" method="post">
                <input type="hidden" name="order_id" value="' . htmlspecialchars($row["MaDHcode"]) . '">';
        
        if ($row["trangthaithanhtoan"] == "chuathanhtoan") {
            $uniqueId = $row['MaDH'];
            echo '<div class="form-section">
                    <div class="form-section-title fw-bold">
                        <i class="fa-solid fa-credit-card"></i> Thanh toán trực tuyến
                    </div>
                    <div class="d-flex justify-content-around align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <input class="form-check-input me-2" type="radio" name="transfer_method" id="momo_' . $uniqueId . '" value="momo" checked>
                            <label class="form-check-label" for="momo_' . $uniqueId . '">
                                <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo" style="height:50px;vertical-align:middle;">
                                MoMo
                            </label>
                        </div>
                        <div class="d-flex align-items-center">
                            <input class="form-check-input me-2" type="radio" name="transfer_method" id="vnpay_' . $uniqueId . '" value="vnpay">
                            <label class="form-check-label" for="vnpay_' . $uniqueId . '">
                                <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png" alt="VNPay" style="height:50px;vertical-align:middle;">
                                VNPay
                            </label>
                        </div>
                    </div>
                  </div>';
        }
        
        echo '<div class="d-flex gap-2">';
        
        // Xác định các nút hiển thị
        if ($row["trangthai"] == "Chờ xử lý" && $row["trangthaithanhtoan"] == "chuathanhtoan") {
            // Chờ xử lý + Chưa thanh toán → Hiện cả 2 nút
            echo '<button data-order_id="' . htmlspecialchars($row["MaDH"]) . '" class="col-6 btn-cancel_order">Hủy đơn hàng</button>
                  <button type="submit" name="btn_pay_order" class="col-6 btn-pay_order">Thanh toán</button>';
        } else if ($row["trangthai"] == "Chờ xử lý" && $row["trangthaithanhtoan"] == "dathanhtoan") {
            // Chờ xử lý + Đã thanh toán → Chỉ hủy đơn
            echo '<button data-order_id="' . htmlspecialchars($row["MaDH"]) . '" class="col-12 btn-cancel_order">Hủy đơn hàng</button>';
        } else if ($row["trangthai"] != "Chờ xử lý" && $row["trangthaithanhtoan"] == "chuathanhtoan") {
            // Không phải chờ xử lý + Chưa thanh toán → Chỉ thanh toán
            echo '<button type="submit" name="btn_pay_order" class="col-12 btn-pay_order">Thanh toán</button>';
        }
        
        echo '</div></form></div>'; // đóng form và order-item
        
    }
} else {
    echo '<div class="text-center mt-4 p-4">
            <p class="text-muted">Không có đơn hàng nào với trạng thái này.</p>
          </div>';
}
?>