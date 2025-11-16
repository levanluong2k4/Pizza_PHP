<?php
session_start();
require '../includes/db_connect.php';

// Bật error reporting cho mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Lấy dữ liệu từ form
$loaidatban = $_POST['loaidatban'] ?? 'thuong';
$hoten = trim($_POST['hoten'] ?? '');
$sdt = trim($_POST['sdt'] ?? '');
$ngayden = $_POST['ngayden'] ?? '';
$gioden = $_POST['gioden'] ?? '';
$table_id = intval($_POST['table_id'] ?? 0);
$combo_id = intval($_POST['combo_id'] ?? 0);
$tiencoc = floatval($_POST['tiencoc'] ?? 0);
$ghichu = trim($_POST['ghichu'] ?? '');

// Ghép ngày và giờ thành datetime
$ngaygio = '';
if ($ngayden && $gioden) {
    $ngaygio = $ngayden . ' ' . $gioden . ':00';
}

// Validate dữ liệu
$errors = [];

if (empty($hoten)) {
    $errors[] = "Vui lòng nhập họ tên";
}

if (empty($sdt) || !preg_match('/^[0-9]{10}$/', $sdt)) {
    $errors[] = "Số điện thoại không hợp lệ";
}

if (empty($ngaygio)) {
    $errors[] = "Vui lòng chọn ngày và giờ đến";
}

if ($table_id <= 0) {
    $errors[] = "Vui lòng chọn bàn/phòng";
}

// Kiểm tra thời gian đặt phải cách hiện tại ít nhất 10 tiếng
try {
    $datetime = new DateTime($ngaygio);
    $now = new DateTime();
    $now->modify('+10 hours');
    
    if ($datetime < $now) {
        $errors[] = "Thời gian đặt bàn phải cách hiện tại ít nhất 10 tiếng";
    }
    
    // Kiểm tra giờ đến phải trong khung 10:00 - 20:00
    $hour = intval($datetime->format('H'));
    $minute = intval($datetime->format('i'));
    if ($hour < 10 || $hour > 20 || ($hour == 20 && $minute > 0)) {
        $errors[] = "Giờ đến phải trong khung 10:00 - 20:00";
    }
} catch (Exception $e) {
    $errors[] = "Định dạng ngày giờ không hợp lệ";
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Bắt đầu transaction
$ketnoi->begin_transaction();

try {
    // Kiểm tra bàn/phòng còn trống không
    if ($loaidatban == 'thuong') {
        // Kiểm tra bàn thường có trống vào ngày đã chọn không
        $sql_check = "SELECT ba.TrangThai,
                      (SELECT COUNT(*) FROM datban db 
                       WHERE db.MaBan = ? 
                       AND DATE(db.NgayGio) = DATE(?)
                       AND db.TrangThaiDatBan IN ('da_dat','dang_su_dung')
                      ) as da_dat
                      FROM banan ba
                      WHERE ba.MaBan = ?
                      FOR UPDATE";
        
        $stmt_check = $ketnoi->prepare($sql_check);
        $stmt_check->bind_param("isi", $table_id, $ngaygio, $table_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $table_status = $result->fetch_assoc();
        $stmt_check->close();
        
        if (!$table_status) {
            throw new Exception("Bàn không tồn tại.");
        }
        
        if ($table_status['TrangThai'] == 'bao_tri') {
            throw new Exception("Bàn này đang bảo trì.");
        }
        
        if ($table_status['da_dat'] > 0) {
            throw new Exception("Bàn này đã được đặt vào ngày " . date('d/m/Y', strtotime($ngaygio)) . ". Vui lòng chọn bàn khác hoặc ngày khác.");
        }
        
        // Insert đơn đặt bàn thường
        $sql_insert = "INSERT INTO datban 
            (HoTen, SDT, NgayGio, LoaiDatBan, MaBan, GhiChu, TrangThaiDatBan) 
            VALUES (?, ?, ?, 'thuong', ?, ?, 'cho_xac_nhan')";

        $stmt_insert = $ketnoi->prepare($sql_insert);
        $stmt_insert->bind_param("sssis", $hoten, $sdt, $ngaygio, $table_id, $ghichu);
        
        if (!$stmt_insert->execute()) {
            throw new Exception("Lỗi khi tạo đơn đặt bàn: " . $stmt_insert->error);
        }
        
        $madatban = $stmt_insert->insert_id;
        $stmt_insert->close();
        
    } else {
        // Đặt bàn tiệc - Kiểm tra phòng có trống vào ngày đã chọn không
        $sql_check = "SELECT pt.TrangThai, pt.SucChua,
                      (SELECT COUNT(*) FROM datban db 
                       WHERE db.MaPhong = ? 
                       AND DATE(db.NgayGio) = DATE(?)
                       AND db.TrangThaiDatBan IN ('da_dat','dang_su_dung')
                      ) as da_dat
                      FROM phongtiec pt
                      WHERE pt.MaPhong = ?
                      FOR UPDATE";
        
        $stmt_check = $ketnoi->prepare($sql_check);
        $stmt_check->bind_param("isi", $table_id, $ngaygio, $table_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $room_status = $result->fetch_assoc();
        $stmt_check->close();
        
        if (!$room_status) {
            throw new Exception("Phòng không tồn tại.");
        }
        
        if ($room_status['TrangThai'] == 'bao_tri') {
            throw new Exception("Phòng này đang bảo trì.");
        }
        
        if ($room_status['da_dat'] > 0) {
            throw new Exception("Phòng này đã được đặt vào ngày " . date('d/m/Y', strtotime($ngaygio)) . ". Vui lòng chọn phòng khác hoặc ngày khác.");
        }
        
        // Kiểm tra combo tồn tại
        if ($combo_id > 0) {
            $sql_combo = "SELECT MaCombo FROM combo WHERE MaCombo = ?";
            $stmt_combo = $ketnoi->prepare($sql_combo);
            $stmt_combo->bind_param("i", $combo_id);
            $stmt_combo->execute();
            $result_combo = $stmt_combo->get_result();
            if ($result_combo->num_rows == 0) {
                throw new Exception("Combo không tồn tại.");
            }
            $stmt_combo->close();
        }
        
        // Insert đơn đặt bàn tiệc
        if ($combo_id > 0) {
            $sql_insert = "INSERT INTO datban 
                (HoTen, SDT, NgayGio, LoaiDatBan, MaPhong, MaCombo, TienCoc, GhiChu, TrangThaiDatBan)
                VALUES (?, ?, ?, 'tiec', ?, ?, ?, ?, 'cho_xac_nhan')";

            $stmt_insert = $ketnoi->prepare($sql_insert);
            $stmt_insert->bind_param("sssiids", $hoten, $sdt, $ngaygio, $table_id, $combo_id, $tiencoc, $ghichu);
        } else {
            $sql_insert = "INSERT INTO datban
                (HoTen, SDT, NgayGio, LoaiDatBan, MaPhong, TienCoc, GhiChu, TrangThaiDatBan)
                VALUES (?, ?, ?, 'tiec', ?, ?, ?, 'cho_xac_nhan')";

            $stmt_insert = $ketnoi->prepare($sql_insert);
            $stmt_insert->bind_param("sssids", $hoten, $sdt, $ngaygio, $table_id, $tiencoc, $ghichu);
        }
        
        if (!$stmt_insert->execute()) {
            throw new Exception("Lỗi khi tạo đơn đặt bàn: " . $stmt_insert->error);
        }
        
        $madatban = $stmt_insert->insert_id;
        $stmt_insert->close();
    }
    
    // Commit transaction
    $ketnoi->commit();
    
    // Lưu thông báo thành công
    $_SESSION['success'] = "Đặt bàn thành công! Mã đặt bàn của bạn là: <strong>#" . $madatban . "</strong>";
    $_SESSION['madatban'] = $madatban;
    
    // Chuyển đến trang xác nhận
    header('Location: ../datban/confirm_booking.php?id=' . $madatban);
    exit;
    
} catch (Exception $e) {
    // Rollback nếu có lỗi
    $ketnoi->rollback();
    
    // Hiển thị lỗi chi tiết để debug
    $error_message = "Lỗi đặt bàn: " . $e->getMessage();
    
    // Thêm thông tin debug
    $error_message .= "<br><br><strong>Debug Info:</strong><br>";
    $error_message .= "- Loại đặt bàn: " . $loaidatban . "<br>";
    $error_message .= "- Họ tên: " . $hoten . "<br>";
    $error_message .= "- SĐT: " . $sdt . "<br>";
    $error_message .= "- Ngày giờ: " . $ngaygio . "<br>";
    $error_message .= "- Table ID: " . $table_id . "<br>";
    if ($loaidatban == 'tiec') {
        $error_message .= "- Combo ID: " . $combo_id . "<br>";
        $error_message .= "- Tiền cọc: " . number_format($tiencoc, 0, ',', '.') . " VNĐ<br>";
    }
    $error_message .= "- Ghi chú: " . ($ghichu ?: '(Không có)') . "<br>";
    
    $_SESSION['error'] = $error_message;
    
    // Log lỗi vào file để dễ theo dõi
    error_log("Booking Error: " . $e->getMessage() . "\nStack: " . $e->getTraceAsString());
    
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

$ketnoi->close();
?>