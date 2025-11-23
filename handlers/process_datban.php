<?php
session_start();
require '../includes/db_connect.php';
require 'atm_momo.php';
require 'atm_vpay.php';

// Bật error reporting cho mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$user_id = $_SESSION['user_id'] ?? null;

// Lấy dữ liệu từ form
$loaidatban = $_POST['loaidatban'] ?? 'thuong';
$hoten = trim($_POST['hoten'] ?? '');
$sdt = trim($_POST['sdt'] ?? '');
$ngayden = $_POST['ngayden'] ?? '';
$gioden = $_POST['gioden'] ?? '';
$table_id = intval($_POST['table_id'] ?? 0);
$combo_id = intval($_POST['combo_id'] ?? 0);
$ghichu = trim($_POST['ghichu'] ?? '');
$thanhtoan = trim($_POST['transfer_method'] ?? '');
$posted_price = floatval($_POST['final_price'] ?? 0);

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
        
        // ✅ TÍNH LẠI GIÁ TỪ DATABASE (BẢO MẬT)
        $verified_total = 0;
        $verified_final_price = 0;
        $discount_percent = 0;
        
        if ($combo_id > 0) {
            // Lấy thông tin combo và % giảm giá
            $sql_combo = "SELECT giamgia FROM combo WHERE MaCombo = ?";
            $stmt_combo = $ketnoi->prepare($sql_combo);
            $stmt_combo->bind_param("i", $combo_id);
            $stmt_combo->execute();
            $result_combo = $stmt_combo->get_result();
            
            if ($result_combo->num_rows == 0) {
                throw new Exception("Combo không tồn tại.");
            }
            
            $combo_info = $result_combo->fetch_assoc();
            $discount_percent = $combo_info['giamgia'];
            $stmt_combo->close();
            
            // ✅ TÍNH TỔNG TIỀN TỪ DATABASE
            // Kiểm tra xem có thay đổi sản phẩm không (trong session)
            if (isset($_SESSION['combo_order']['items'])) {
                // Có thay đổi sản phẩm
                foreach ($_SESSION['combo_order']['items'] as $item) {
                    $sql_price = "SELECT Gia FROM sanpham_size WHERE MaSP = ? AND MaSize = ?";
                    $stmt_price = $ketnoi->prepare($sql_price);
                    $stmt_price->bind_param("ii", $item['masp'], $item['masize']);
                    $stmt_price->execute();
                    $result_price = $stmt_price->get_result()->fetch_assoc();
                    
                    if ($result_price) {
                        $verified_total += $result_price['Gia'] * $item['soluong'];
                    }
                    $stmt_price->close();
                }
            } else {
                // Không thay đổi - lấy từ chitietcombo gốc
                $sql_total = "SELECT SUM(sps.Gia * ct.SoLuong) as total
                             FROM chitietcombo ct
                             INNER JOIN sanpham_size sps ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize
                             WHERE ct.MaCombo = ?";
                $stmt_total = $ketnoi->prepare($sql_total);
                $stmt_total->bind_param("i", $combo_id);
                $stmt_total->execute();
                $result_total = $stmt_total->get_result()->fetch_assoc();
                $verified_total = $result_total['total'] ?? 0;
                $stmt_total->close();
            }
            
            // ✅ ÁP DỤNG GIẢM GIÁ
            $discount_amount = $verified_total * ($discount_percent / 100);
            $verified_final_price = $verified_total - $discount_amount;
            
            // ✅ SO SÁNH VỚI GIÁ GỬI LÊN
            if ($posted_price > 0 && abs($verified_final_price - $posted_price) > 1) {
                error_log("Price mismatch - Posted: $posted_price, Verified: $verified_final_price");
                throw new Exception("Giá không hợp lệ! Vui lòng thử lại.");
            }
            
            // ✅ DÙNG GIÁ ĐÃ XÁC THỰC
            $tongtien = $verified_final_price;
            
        } else {
            $tongtien = 0;
        }
        
        // ==================== INSERT ĐƠN ĐẶT BÀN ====================
        
        // Trường hợp có combo
        if ($combo_id > 0) {
            // Insert vào bảng datban
            $sql_insert = "INSERT INTO datban 
            (Tongtien, MaKH, HoTen, SDT, NgayGio, LoaiDatBan, MaPhong, MaCombo, GhiChu, TrangThaiDatBan, TrangThaiThanhToan) 
            VALUES (?, ?, ?, ?, ?, 'tiec', ?, ?, ?, 'da_dat', 'chuathanhtoan')";

            $stmt_insert = $ketnoi->prepare($sql_insert);
            $stmt_insert->bind_param("disssiis", $tongtien, $user_id, $hoten, $sdt, $ngaygio, $table_id, $combo_id, $ghichu);
            
            if (!$stmt_insert->execute()) {
                throw new Exception("Lỗi khi tạo đơn đặt bàn: " . $stmt_insert->error);
            }
            
            // Lấy MaDatBan vừa insert
            $madatban = $stmt_insert->insert_id;
            $stmt_insert->close();
            
            // Lấy danh sách sản phẩm để insert vào chitietdatban
            if (isset($_SESSION['combo_order']['items'])) {
                // Nếu có thay đổi sản phẩm
                foreach ($_SESSION['combo_order']['items'] as $item) {
                    $sql_price = "SELECT Gia FROM sanpham_size WHERE MaSP = ? AND MaSize = ?";
                    $stmt_price = $ketnoi->prepare($sql_price);
                    $stmt_price->bind_param("ii", $item['masp'], $item['masize']);
                    $stmt_price->execute();
                    $result_price = $stmt_price->get_result()->fetch_assoc();
                    
                    if ($result_price) {
                        $thanh_tien = $result_price['Gia'] * $item['soluong'];
                        
                        $sql_insert_chitiet = "INSERT INTO chitietdatban (MaDatBan, MaSP, MaSize, SoLuong, ThanhTien) 
                                              VALUES (?, ?, ?, ?, ?)";
                        $stmt_insert_chitiet = $ketnoi->prepare($sql_insert_chitiet);
                        $stmt_insert_chitiet->bind_param("iiiid", $madatban, $item['masp'], $item['masize'], $item['soluong'], $thanh_tien);
                        $stmt_insert_chitiet->execute();
                        $stmt_insert_chitiet->close();
                    }
                    $stmt_price->close();
                }
            } else {
                // Không thay đổi - copy từ chitietcombo
                $sql_chitiet = "SELECT cc.MaSP, cc.MaSize, cc.SoLuong, ss.Gia 
                               FROM chitietcombo cc
                               INNER JOIN sanpham_size ss ON cc.MaSP = ss.MaSP AND cc.MaSize = ss.MaSize
                               WHERE cc.MaCombo = ?";
                $stmt_chitiet = $ketnoi->prepare($sql_chitiet);
                $stmt_chitiet->bind_param("i", $combo_id);
                $stmt_chitiet->execute();
                $result_chitiet = $stmt_chitiet->get_result();
                
                $sql_insert_chitiet = "INSERT INTO chitietdatban (MaDatBan, MaSP, MaSize, SoLuong, ThanhTien) 
                                      VALUES (?, ?, ?, ?, ?)";
                $stmt_insert_chitiet = $ketnoi->prepare($sql_insert_chitiet);
                
                while ($row = $result_chitiet->fetch_assoc()) {
                    $thanh_tien = $row['Gia'] * $row['SoLuong'];
                    $stmt_insert_chitiet->bind_param("iiiid", $madatban, $row['MaSP'], $row['MaSize'], $row['SoLuong'], $thanh_tien);
                    $stmt_insert_chitiet->execute();
                }
                
                $stmt_chitiet->close();
                $stmt_insert_chitiet->close();
            }
            
            // ✅ XÓA SESSION SAU KHI DÙNG XONG
            unset($_SESSION['combo_order']);
            
        } else {
            // Trường hợp không có combo
            $sql_insert = "INSERT INTO datban
                (MaKH, HoTen, SDT, NgayGio, LoaiDatBan, MaPhong, GhiChu, TrangThaiDatBan, TrangThaiThanhToan)
                VALUES (?, ?, ?, ?, 'tiec', ?, ?, 'da_dat', 'chuathanhtoan')";

            $stmt_insert = $ketnoi->prepare($sql_insert);
            $stmt_insert->bind_param("isssiss", $user_id, $hoten, $sdt, $ngaygio, $table_id, $ghichu);
            
            if (!$stmt_insert->execute()) {
                throw new Exception("Lỗi khi tạo đơn đặt bàn: " . $stmt_insert->error);
            }
            
            $madatban = $stmt_insert->insert_id;
            $stmt_insert->close();
        }

        // Commit transaction
        $ketnoi->commit();
        
        // ==================== XỬ LÝ THANH TOÁN ====================
        if ($tongtien > 0 && !empty($thanhtoan)) {
            
            $table_info = "Thanh toan dat ban #" . $madatban;
            
            // Thanh toán qua MoMo
            if ($thanhtoan === 'momo') {
                try {
                    $momoResult = processmomoPayment_ordertable($madatban, $tongtien, $table_info, $thanhtoan);
                    
                    if (isset($momoResult['resultCode']) && $momoResult['resultCode'] == 0) {
                        if (isset($momoResult['payUrl']) && !empty($momoResult['payUrl'])) {
                            header("Location: " . $momoResult['payUrl']);
                            exit();
                        } else {
                            throw new Exception("Không nhận được URL thanh toán từ MoMo");
                        }
                    } else {
                        $errorMsg = isset($momoResult['message']) ? $momoResult['message'] : 'Lỗi không xác định';
                        throw new Exception("MoMo Error: " . $errorMsg);
                    }
                } catch (Exception $e) {
                    error_log("MoMo Payment Error - Order #$madatban: " . $e->getMessage());
                    $_SESSION['warning'] = "Đơn đặt bàn đã được tạo thành công (Mã #$madatban).<br>Tuy nhiên, không thể kết nối đến MoMo để thanh toán.<br>Lỗi: " . $e->getMessage();
                    header("Location: ../datban/confirm_booking.php?id=" . $madatban);
                    exit();
                }
            }
            
            // Thanh toán qua VNPAY
            elseif ($thanhtoan === 'vnpay') {
                try {
                    $vnpayResult = processVnpayPayment_ordertable($madatban, $tongtien, $table_info, $thanhtoan);
                    
                    if (isset($vnpayResult['payment_url']) && !empty($vnpayResult['payment_url'])) {
                        header("Location: " . $vnpayResult['payment_url']);
                        exit();
                    } else {
                        throw new Exception("Không nhận được URL thanh toán từ VNPAY");
                    }
                } catch (Exception $e) {
                    error_log("VNPAY Payment Error - Order #$madatban: " . $e->getMessage());
                    $_SESSION['warning'] = "Đơn đặt bàn đã được tạo thành công (Mã #$madatban).<br>Tuy nhiên, không thể kết nối đến VNPAY để thanh toán.<br>Vui lòng liên hệ nhà hàng để hoàn tất thanh toán.";
                    header("Location: ../datban/confirm_booking.php?id=" . $madatban);
                    exit();
                }
            } else {
                $_SESSION['warning'] = "Đơn đặt bàn đã được tạo thành công (Mã #$madatban).<br>Phương thức thanh toán không hợp lệ. Vui lòng liên hệ nhà hàng.";
                header("Location: ../datban/confirm_booking.php?id=" . $madatban);
                exit();
            }
        } else {
            $_SESSION['success'] = "Đặt bàn thành công! Mã đặt bàn của bạn là: <strong>#" . $madatban . "</strong>";
            $_SESSION['madatban'] = $madatban;
            header('Location: ../datban/confirm_booking.php?id=' . $madatban);
            exit;
        }
    }
    
} catch (Exception $e) {
    $ketnoi->rollback();
    
    $error_message = "Lỗi đặt bàn: " . $e->getMessage();
    $_SESSION['error'] = $error_message;
    error_log("Booking Error: " . $e->getMessage() . "\nStack: " . $e->getTraceAsString());
    
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../datban.php'));
    exit;
}

$ketnoi->close();
?>