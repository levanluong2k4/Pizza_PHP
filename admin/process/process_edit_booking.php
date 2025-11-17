<?php
session_start();
require __DIR__ . '/../../includes/db_connect.php';

// if (!isset($_SESSION['admin'])) {
//     header('Location: ../login.php');
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../datban.php');
    exit;
}

// Lấy dữ liệu
$madatban = intval($_POST['madatban'] ?? 0);
$hoten = trim($_POST['hoten'] ?? '');
$sdt = trim($_POST['sdt'] ?? '');
$ngayden = $_POST['ngayden'] ?? '';
$gioden = $_POST['gioden'] ?? '';
$loaidatban = $_POST['loaidatban'] ?? '';
$table_id = $_POST['table_id'] ?? '';
$combo_id = intval($_POST['combo_id'] ?? 0);
$tiencoc = floatval($_POST['tiencoc'] ?? 0);
$ghichu = trim($_POST['ghichu'] ?? '');

// Validate
$errors = [];

if ($madatban <= 0) {
    $errors[] = "Mã đặt bàn không hợp lệ";
}

if (empty($hoten)) {
    $errors[] = "Vui lòng nhập họ tên";
}

if (empty($sdt) || !preg_match('/^[0-9]{10}$/', $sdt)) {
    $errors[] = "Số điện thoại không hợp lệ";
}

if (empty($ngayden) || empty($gioden)) {
    $errors[] = "Vui lòng chọn ngày giờ";
}

if (empty($table_id)) {
    $errors[] = "Vui lòng chọn bàn/phòng";
}

if (!in_array($loaidatban, ['thuong', 'tiec'])) {
    $errors[] = "Loại đặt bàn không hợp lệ";
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: ../view/edit_booking.php?id=' . $madatban);
    exit;
}

// Ghép ngày giờ
$ngaygio = $ngayden . ' ' . $gioden . ':00';

// Parse table_id (format: "ban_1" hoặc "phong_2")
list($type_prefix, $id) = explode('_', $table_id);
$id = intval($id);

$maban = null;
$maphong = null;

if ($type_prefix == 'ban') {
    $maban = $id;
} else {
    $maphong = $id;
}

// Begin transaction
$ketnoi->begin_transaction();

try {
    // Kiểm tra đơn đặt bàn tồn tại
    $sql_check = "SELECT * FROM datban WHERE MaDatBan = ? FOR UPDATE";
    $stmt_check = $ketnoi->prepare($sql_check);
    $stmt_check->bind_param("i", $madatban);
    $stmt_check->execute();
    $existing = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();
    
    if (!$existing) {
        throw new Exception("Đơn đặt bàn không tồn tại");
    }
    
    // Kiểm tra bàn/phòng có trống không (trừ đơn hiện tại)
    if ($loaidatban == 'thuong') {
        $sql_available = "SELECT COUNT(*) as count FROM datban 
                          WHERE MaBan = ? 
                          AND DATE(NgayGio) = DATE(?)
                          AND TrangThaiDatBan IN ('da_dat', 'da_xac_nhan', 'dang_su_dung')
                          AND MaDatBan != ?";
        $stmt_available = $ketnoi->prepare($sql_available);
        $stmt_available->bind_param("isi", $maban, $ngaygio, $madatban);
        $stmt_available->execute();
        $result = $stmt_available->get_result()->fetch_assoc();
        $stmt_available->close();
        
        if ($result['count'] > 0) {
            throw new Exception("Bàn này đã được đặt vào ngày " . date('d/m/Y', strtotime($ngaygio)));
        }
        
        // Update
        $sql_update = "UPDATE datban SET 
                       HoTen = ?, 
                       SDT = ?, 
                       NgayGio = ?,
                       LoaiDatBan = 'thuong',
                       MaBan = ?,
                       MaPhong = NULL,
                       MaCombo = NULL,
                       TienCoc = 0,
                       GhiChu = ?
                       WHERE MaDatBan = ?";
        
        $stmt_update = $ketnoi->prepare($sql_update);
        $stmt_update->bind_param("sssisi", $hoten, $sdt, $ngaygio, $maban, $ghichu, $madatban);
        
    } else {
        // Bàn tiệc
        $sql_available = "SELECT COUNT(*) as count FROM datban 
                          WHERE MaPhong = ? 
                          AND DATE(NgayGio) = DATE(?)
                          AND TrangThaiDatBan IN ('da_dat', 'da_xac_nhan', 'dang_su_dung')
                          AND MaDatBan != ?";
        $stmt_available = $ketnoi->prepare($sql_available);
        $stmt_available->bind_param("isi", $maphong, $ngaygio, $madatban);
        $stmt_available->execute();
        $result = $stmt_available->get_result()->fetch_assoc();
        $stmt_available->close();
        
        if ($result['count'] > 0) {
            throw new Exception("Phòng này đã được đặt vào ngày " . date('d/m/Y', strtotime($ngaygio)));
        }
        
        // Update
        if ($combo_id > 0) {
            $sql_update = "UPDATE datban SET 
                           HoTen = ?, 
                           SDT = ?, 
                           NgayGio = ?,
                           LoaiDatBan = 'tiec',
                           MaBan = NULL,
                           MaPhong = ?,
                           MaCombo = ?,
                           TienCoc = ?,
                           GhiChu = ?
                           WHERE MaDatBan = ?";
            
            $stmt_update = $ketnoi->prepare($sql_update);
            $stmt_update->bind_param("sssiidsi", $hoten, $sdt, $ngaygio, $maphong, $combo_id, $tiencoc, $ghichu, $madatban);
        } else {
            $sql_update = "UPDATE datban SET 
                           HoTen = ?, 
                           SDT = ?, 
                           NgayGio = ?,
                           LoaiDatBan = 'tiec',
                           MaBan = NULL,
                           MaPhong = ?,
                           MaCombo = NULL,
                           TienCoc = ?,
                           GhiChu = ?
                           WHERE MaDatBan = ?";
            
            $stmt_update = $ketnoi->prepare($sql_update);
            $stmt_update->bind_param("sssidsi", $hoten, $sdt, $ngaygio, $maphong, $tiencoc, $ghichu, $madatban);
        }
    }
    
    if (!$stmt_update->execute()) {
        throw new Exception("Lỗi khi cập nhật: " . $stmt_update->error);
    }
    
    $stmt_update->close();
    
    // Commit
    $ketnoi->commit();
    
    $_SESSION['success'] = "Cập nhật đặt bàn thành công!";
    header('Location: ../view/datban.php?date=' . $ngayden);
    exit;
    
} catch (Exception $e) {
    $ketnoi->rollback();
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../view/edit_booking.php?id=' . $madatban);
    exit;
}

$ketnoi->close();
?>