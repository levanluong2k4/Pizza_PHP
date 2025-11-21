<?php
session_start();
require '../includes/db_connect.php';
require 'atm_momo.php';
require 'atm_vpay.php';

// Bật error reporting cho mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);



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
$thanhtoan = trim($_POST['transfer_method'] ?? '');


// Ghép ngày và giờ thành datetime
$ngaygio = '';
if ($ngayden && $gioden) {
    $ngaygio = $ngayden . ' ' . $gioden . ':00';
}

// ==================== VALIDATE DỮ LIỆU ====================
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

// Nếu có lỗi validate, quay lại trang trước
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// ==================== BẮT ĐẦU TRANSACTION ====================
$ketnoi->begin_transaction();

try {
    // ==================== ĐẶT BÀN THƯỜNG ====================
    if ($loaidatban == 'thuong') {
        
        // Kiểm tra bàn thường có trống không
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
            VALUES (?, ?, ?, 'thuong', ?, ?, 'da_dat')";

        $stmt_insert = $ketnoi->prepare($sql_insert);
        $stmt_insert->bind_param("sssis", $hoten, $sdt, $ngaygio, $table_id, $ghichu);
        
        if (!$stmt_insert->execute()) {
            throw new Exception("Lỗi khi tạo đơn đặt bàn: " . $stmt_insert->error);
        }
        
        $madatban = $stmt_insert->insert_id;
        $stmt_insert->close();
        
        // Commit transaction
        $ketnoi->commit();
        
        // Lưu thông báo thành công
        $_SESSION['success'] = "Đặt bàn thành công! Mã đặt bàn của bạn là: <strong>#" . $madatban . "</strong>";
        $_SESSION['madatban'] = $madatban;
        
        // Chuyển đến trang xác nhận
        header('Location: ../datban/confirm_booking.php?id=' . $madatban);
        exit;
    }
    
    // ==================== ĐẶT BÀN TIỆC ====================
    else {
        
        // Kiểm tra phòng có trống không
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
        
        // Kiểm tra combo nếu có
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
        
        // ==================== INSERT ĐƠN ĐẶT BÀN ====================
        
        // Trường hợp có combo
 // ==================== INSERT ĐƠN ĐẶT BÀN ====================

// Trường hợp có combo
if ($combo_id > 0) {
    // Insert vào bảng datban
    $sql_insert = "INSERT INTO datban (HoTen, SDT, NgayGio, LoaiDatBan, MaPhong, MaCombo, TienCoc, GhiChu, TrangThaiDatBan, TrangThaiThanhToan) 
                   VALUES (?, ?, ?, 'tiec', ?, ?, ?, ?, 'da_dat', 'da_coc')";
    $stmt_insert = $ketnoi->prepare($sql_insert);
    $stmt_insert->bind_param("sssiids", $hoten, $sdt, $ngaygio, $table_id, $combo_id, $tiencoc, $ghichu);
    
    if (!$stmt_insert->execute()) {
        throw new Exception("Lỗi khi tạo đơn đặt bàn: " . $stmt_insert->error);
    }
    
    // Lấy MaDatBan vừa insert
    $madatban = $stmt_insert->insert_id;
    $stmt_insert->close(); // Đóng statement sau khi lấy insert_id
    
    // Lấy danh sách sản phẩm từ bảng chitietcombo
    $sql_chitiet = "SELECT cc.MaSP, cc.MaSize, cc.SoLuong, ss.Gia 
                    FROM chitietcombo cc
                    INNER JOIN sanpham_size ss ON cc.MaSP = ss.MaSP AND cc.MaSize = ss.MaSize
                    WHERE cc.MaCombo = ?";
    $stmt_chitiet = $ketnoi->prepare($sql_chitiet);
    $stmt_chitiet->bind_param("i", $combo_id);
    $stmt_chitiet->execute();
    $result_chitiet = $stmt_chitiet->get_result();
    
    // Insert từng sản phẩm vào bảng chitietdatban
    $sql_insert_chitiet = "INSERT INTO chitietdatban (MaDatBan, MaSP, MaSize, SoLuong, ThanhTien) 
                           VALUES (?, ?, ?, ?, ?)";
    $stmt_insert_chitiet = $ketnoi->prepare($sql_insert_chitiet);
    
    while ($row = $result_chitiet->fetch_assoc()) {
        $ma_sp = $row['MaSP'];
        $ma_size = $row['MaSize'];
        $so_luong = $row['SoLuong'];
        $thanh_tien = $row['Gia'] * $so_luong; // ThanhTien = đơn giá * số lượng
        
        $stmt_insert_chitiet->bind_param("iiiid", $madatban, $ma_sp, $ma_size, $so_luong, $thanh_tien);
        
        if (!$stmt_insert_chitiet->execute()) {
            throw new Exception("Lỗi khi thêm chi tiết đặt bàn: " . $stmt_insert_chitiet->error);
        }
    }
    
    $stmt_chitiet->close();
    $stmt_insert_chitiet->close();
}
// Trường hợp không có combo
else {
    $sql_insert = "INSERT INTO datban
        (HoTen, SDT, NgayGio, LoaiDatBan, MaPhong, TienCoc, GhiChu, TrangThaiDatBan)
        VALUES (?, ?, ?, 'tiec', ?, ?, ?, 'da_dat')";

    $stmt_insert = $ketnoi->prepare($sql_insert);
    $stmt_insert->bind_param("sssids", $hoten, $sdt, $ngaygio, $table_id, $tiencoc, $ghichu);
    
    if (!$stmt_insert->execute()) {
        throw new Exception("Lỗi khi tạo đơn đặt bàn: " . $stmt_insert->error);
    }
    
    $madatban = $stmt_insert->insert_id;
    $stmt_insert->close();
}


$ketnoi->commit();
        // Xử lý thanh toán tiền cọc nếu có
        if ($tiencoc > 0 && !empty($thanhtoan)) {
            
            $table_info = "Thanh toan dat ban #" . $madatban;
            
         // Thanh toán qua MoMo
if ($thanhtoan === 'momo') {
    
    try {
        $momoResult = processmomoPayment_ordertable($madatban, $tiencoc, $table_info, $thanhtoan);
        
        // DEBUG: Log response từ MoMo
        error_log("MoMo Response: " . json_encode($momoResult));
        
        // Kiểm tra resultCode trước
        if (isset($momoResult['resultCode']) && $momoResult['resultCode'] == 0) {
            if (isset($momoResult['payUrl']) && !empty($momoResult['payUrl'])) {
                header("Location: " . $momoResult['payUrl']);
                exit();
            } else {
                throw new Exception("Không nhận được URL thanh toán từ MoMo");
            }
        } else {
            // Lỗi từ MoMo API
            $errorMsg = isset($momoResult['message']) ? $momoResult['message'] : 'Lỗi không xác định';
            throw new Exception("MoMo Error: " . $errorMsg);
        }
        
    } catch (Exception $e) {
        error_log("MoMo Payment Error - Order #$madatban: " . $e->getMessage());
        $_SESSION['warning'] = "Đơn đặt bàn đã được tạo thành công (Mã #$madatban).<br>Tuy nhiên, không thể kết nối đến MoMo để thanh toán tiền cọc.<br>Lỗi: " . $e->getMessage();
        header("Location: ../datban/confirm_booking.php?id=" . $madatban);
        exit();
    }
}
            
            // Thanh toán qua VNPAY
            elseif ($thanhtoan === 'vnpay') {
                
                try {
                    $vnpayResult = processVnpayPayment_ordertable($madatban, $tiencoc, $table_info,$thanhtoan);
                    
                    if (isset($vnpayResult['payment_url']) && !empty($vnpayResult['payment_url'])) {
                        // Chuyển hướng đến trang thanh toán VNPAY
                        header("Location: " . $vnpayResult['payment_url']);
                        exit();
                    } else {
                        // Không có URL thanh toán
                        throw new Exception("Không nhận được URL thanh toán từ VNPAY");
                    }
                    
                } catch (Exception $e) {
                    // Log lỗi
                    error_log("VNPAY Payment Error - Order #$madatban: " . $e->getMessage());
                    
                    // Đơn đã tạo thành công, chỉ thanh toán bị lỗi
                    $_SESSION['warning'] = "Đơn đặt bàn đã được tạo thành công (Mã #$madatban).<br>Tuy nhiên, không thể kết nối đến VNPAY để thanh toán tiền cọc.<br>Vui lòng liên hệ nhà hàng để hoàn tất thanh toán.";
                    header("Location: ../datban/confirm_booking.php?id=" . $madatban);
                    exit();
                }
            }
            
            // Phương thức thanh toán không hợp lệ
            else {
                $_SESSION['warning'] = "Đơn đặt bàn đã được tạo thành công (Mã #$madatban).<br>Phương thức thanh toán không hợp lệ. Vui lòng liên hệ nhà hàng.";
                header("Location: ../datban/confirm_booking.php?id=" . $madatban);
                exit();
            }
        }
        
        // Không có thanh toán online - chuyển thẳng đến trang xác nhận
        else {
            $_SESSION['success'] = "Đặt bàn thành công! Mã đặt bàn của bạn là: <strong>#" . $madatban . "</strong>";
            $_SESSION['madatban'] = $madatban;
            header('Location: ../datban/confirm_booking.php?id=' . $madatban);
            exit;
        }
    }
    
} catch (Exception $e) {
    
    // Rollback nếu có lỗi
    $ketnoi->rollback();
    
    // Tạo thông báo lỗi chi tiết
    $error_message = "Lỗi đặt bàn: " . $e->getMessage();
    
    // Thêm thông tin debug (chỉ khi đang dev, nên bỏ khi production)
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        $error_message .= "<br><br><strong>Debug Info:</strong><br>";
        $error_message .= "- Loại đặt bàn: " . htmlspecialchars($loaidatban) . "<br>";
        $error_message .= "- Họ tên: " . htmlspecialchars($hoten) . "<br>";
        $error_message .= "- SĐT: " . htmlspecialchars($sdt) . "<br>";
        $error_message .= "- Ngày giờ: " . htmlspecialchars($ngaygio) . "<br>";
        $error_message .= "- Table ID: " . $table_id . "<br>";
        if ($loaidatban == 'tiec') {
            $error_message .= "- Combo ID: " . $combo_id . "<br>";
            $error_message .= "- Tiền cọc: " . number_format($tiencoc, 0, ',', '.') . " VNĐ<br>";
        }
        $error_message .= "- Ghi chú: " . ($ghichu ?: '(Không có)') . "<br>";
    }
    
    $_SESSION['error'] = $error_message;
    
    // Log lỗi vào file
    error_log("Booking Error: " . $e->getMessage() . "\nStack: " . $e->getTraceAsString());
    
    // Quay lại trang trước
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../datban.php'));
    exit;
}

// Đóng kết nối
$ketnoi->close();
?>