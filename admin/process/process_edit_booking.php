<?php
session_start();
require __DIR__ . '/../../includes/db_connect.php';

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Phương thức không hợp lệ';
     header("Location: ../view/datban.php?date=$date");
    exit;
}

// Lấy dữ liệu từ form
$madatban = intval($_POST['madatban'] ?? 0);
$hoten = trim($_POST['hoten'] ?? '');
$sdt = trim($_POST['sdt'] ?? '');
$loaidatban = $_POST['loaidatban'] ?? 'thuong';
$ngayden = $_POST['ngayden'] ?? '';
$gioden = $_POST['gioden'] ?? '';
$table_id = $_POST['table_id'] ?? '';
$combo_id = intval($_POST['combo_id'] ?? 0);

$ghichu = trim($_POST['ghichu'] ?? '');
$products = $_POST['products'] ?? [];

$date=date("Y-m-d", strtotime($ngayden));
// Validate dữ liệu
if ($madatban <= 0) {
    $_SESSION['error'] = 'Mã đặt bàn không hợp lệ';
     header("Location: ../view/datban.php?date=$date");
    exit;
}
$sql_check_type = "SELECT LoaiDatBan FROM datban WHERE MaDatBan = ?";
$stmt = $ketnoi->prepare($sql_check_type);
$stmt->bind_param("i", $madatban);
$stmt->execute();
$result = $stmt->get_result();
$current_booking = $result->fetch_assoc();
$stmt->close();

if ($current_booking && $current_booking['LoaiDatBan'] != $loaidatban) {
    $_SESSION['error'] = 'Không được phép thay đổi loại đặt bàn. Vui lòng tạo đơn mới nếu muốn đổi loại.';
    header("Location: ../view/edit_booking.php?id=$madatban");
    exit;
}

if (empty($hoten) || empty($sdt)) {
    $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin khách hàng';
    header("Location: ../view/edit_booking.php?id=$madatban");
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $sdt)) {
    $_SESSION['error'] = 'Số điện thoại không hợp lệ';
    header("Location: ../view/edit_booking.php?id=$madatban");
    exit;
}

if (empty($ngayden) || empty($gioden)) {
    $_SESSION['error'] = 'Vui lòng chọn ngày giờ đến';
    header("Location: ../view/edit_booking.php?id=$madatban");
    exit;
}

if (empty($table_id)) {
    $_SESSION['error'] = 'Vui lòng chọn bàn hoặc phòng';
    header("Location: ../view/edit_booking.php?id=$madatban");
    exit;
}

// Tạo datetime
$ngaygio = $ngayden . ' ' . $gioden . ':00';

// Phân tích table_id
$table_parts = explode('_', $table_id);
if (count($table_parts) != 2) {
    $_SESSION['error'] = 'Định dạng bàn/phòng không hợp lệ';
    header("Location: ../view/edit_booking.php?id=$madatban");
    exit;
}

$table_type = $table_parts[0]; // 'ban' hoặc 'phong'
$table_id_num = intval($table_parts[1]);

// Xác định MaBan và MaPhong
$maban = null;
$maphong = null;

if ($loaidatban == 'thuong' && $table_type == 'ban') {
    $maban = $table_id_num;
} elseif ($loaidatban == 'tiec' && $table_type == 'phong') {
    $maphong = $table_id_num;
} else {
    $_SESSION['error'] = 'Loại đặt bàn không khớp với bàn/phòng đã chọn';
    header("Location: ../view/edit_booking.php?id=$madatban");
    exit;
}

// Kiểm tra trùng lịch (trừ booking hiện tại)
if ($loaidatban == 'thuong' && $maban) {
    $sql_check = "SELECT MaDatBan FROM datban 
                  WHERE MaBan = ? 
                  AND MaDatBan != ?
                  AND DATE(NgayGio) = ? 
                  AND TrangThaiDatBan NOT IN ('da_huy', 'thanh_cong')";
    $stmt = $ketnoi->prepare($sql_check);
    $stmt->bind_param("iis", $maban, $madatban, $ngayden);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Bàn này đã được đặt trong ngày đã chọn';
        header("Location: ../view/edit_booking.php?id=$madatban");
        exit;
    }
    $stmt->close();
}

if ($loaidatban == 'tiec' && $maphong) {
    $sql_check = "SELECT MaDatBan FROM datban 
                  WHERE MaPhong = ? 
                  AND MaDatBan != ?
                  AND DATE(NgayGio) = ? 
                  AND TrangThaiDatBan NOT IN ('da_huy', 'thanh_cong')";
    $stmt = $ketnoi->prepare($sql_check);
    $stmt->bind_param("iis", $maphong, $madatban, $ngayden);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Phòng này đã được đặt trong ngày đã chọn';
        header("Location: ../view/edit_booking.php?id=$madatban");
        exit;
    }
    $stmt->close();
}

// Bắt đầu transaction
$ketnoi->begin_transaction();



try {
    // Cập nhật bảng datban
    $sql_update = "UPDATE datban SET 
                   HoTen = ?,
                   SDT = ?,
                   NgayGio = ?,
                   LoaiDatBan = ?,
                   MaBan = ?,
                   MaPhong = ?,
                   MaCombo = ?,
                   GhiChu = ?
                   WHERE MaDatBan = ?";
    
    $stmt = $ketnoi->prepare($sql_update);
    
    // Bàn thường không có combo
    $combo_id_val = ($loaidatban == 'tiec' && $combo_id > 0) ? $combo_id : null;
    
    $stmt->bind_param(
        "ssssiiisi",
        $hoten,
        $sdt,
        $ngaygio,
        $loaidatban,
        $maban,
        $maphong,
        $combo_id_val,
        $ghichu,
        $madatban
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Lỗi cập nhật thông tin đặt bàn: ' . $stmt->error);
    }
    $stmt->close();
    
    // Xử lý sản phẩm (cho cả bàn thường và bàn tiệc)
    if (!empty($products)) {
        // Xóa tất cả chi tiết cũ
        $sql_delete = "DELETE FROM chitietdatban WHERE MaDatBan = ?";
        $stmt = $ketnoi->prepare($sql_delete);
        $stmt->bind_param("i", $madatban);
        $stmt->execute();
        $stmt->close();
        
        // Thêm chi tiết mới
        $sql_insert = "INSERT INTO chitietdatban (MaDatBan, MaSP, MaSize, SoLuong, ThanhTien) 
                      VALUES (?, ?, ?, ?, ?)";
        $stmt = $ketnoi->prepare($sql_insert);
        
        foreach ($products as $product) {
            if (isset($product['masp']) && isset($product['masize']) && isset($product['quantity']) && isset($product['price'])) {
                $masp = intval($product['masp']);
                $masize = intval($product['masize']);
                $soluong = intval($product['quantity']);
                $dongia = floatval($product['price']);
                $thanhtien = $soluong * $dongia;
                
                if ($masp > 0 && $masize > 0 && $soluong > 0) {
                    $stmt->bind_param("iiiid", $madatban, $masp, $masize, $soluong, $thanhtien);
                    
                    if (!$stmt->execute()) {
                        throw new Exception('Lỗi thêm chi tiết sản phẩm: ' . $stmt->error);
                    }
                }
            }
        }
        $stmt->close();
        
        // Tính tổng tiền
        $sql_total = "SELECT SUM(ThanhTien) as TongTien 
                     FROM chitietdatban 
                     WHERE MaDatBan = ?";
        $stmt = $ketnoi->prepare($sql_total);
        $stmt->bind_param("i", $madatban);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $tongtien = $row['TongTien'] ?? 0;
        $stmt->close();
        
        // Áp dụng giảm giá combo (chỉ cho bàn tiệc)
        $tongtien_sau_giam = $tongtien;
        if ($loaidatban == 'tiec' && $combo_id > 0) {
            $sql_combo = "SELECT giamgia FROM combo WHERE MaCombo = ?";
            $stmt = $ketnoi->prepare($sql_combo);
            $stmt->bind_param("i", $combo_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($combo_row = $result->fetch_assoc()) {
                $giamgia = floatval($combo_row['giamgia']);
                $tongtien_sau_giam = $tongtien - ($tongtien * $giamgia / 100);
            }
            $stmt->close();
        }
        
        // Cập nhật tổng tiền vào datban
        $sql_update_total = "UPDATE datban SET TongTien = ? WHERE MaDatBan = ?";
        $stmt = $ketnoi->prepare($sql_update_total);
        $stmt->bind_param("di", $tongtien_sau_giam, $madatban);
        $stmt->execute();
        $stmt->close();
    } else {
        // Không có sản phẩm - xóa hết chi tiết và set TongTien = 0
        $sql_delete = "DELETE FROM chitietdatban WHERE MaDatBan = ?";
        $stmt = $ketnoi->prepare($sql_delete);
        $stmt->bind_param("i", $madatban);
        $stmt->execute();
        $stmt->close();
        
        $sql_update_total = "UPDATE datban SET TongTien = 0 WHERE MaDatBan = ?";
        $stmt = $ketnoi->prepare($sql_update_total);
        $stmt->bind_param("i", $madatban);
        $stmt->execute();
        $stmt->close();
    }
    
    // Commit transaction
    $ketnoi->commit();
    
    $_SESSION['success'] = 'Cập nhật đặt bàn thành công!';
    header("Location: ../view/datban.php?date=$date");
    exit;
    
} catch (Exception $e) {
    // Rollback nếu có lỗi
    $ketnoi->rollback();
    
    $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    header("Location: ../view/edit_booking.php?id=$madatban");
    exit;
}
?>