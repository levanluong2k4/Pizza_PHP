<?php
session_start();
require '../includes/db_connect.php';

// Set timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
$ketnoi->query("SET time_zone = '+07:00'");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit('Access denied');
}

$booking_id = intval($_POST['booking_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($booking_id <= 0 || $action !== 'cancel_unpaid') {
    http_response_code(400);
    exit('Invalid request');
}

$ketnoi->begin_transaction();

try {
    // Check với TIMESTAMPDIFF
    $sql_check = "SELECT MaDatBan, TrangThaiThanhToan, TrangThaiDatBan, NgayTao,
                         TIMESTAMPDIFF(SECOND, NgayTao, NOW()) as seconds_passed
                  FROM datban 
                  WHERE MaDatBan = ? 
                  AND TrangThaiThanhToan = 'chuathanhtoan'
                  AND TrangThaiDatBan = 'da_dat'
                  FOR UPDATE";
    
    $stmt_check = $ketnoi->prepare($sql_check);
    $stmt_check->bind_param("i", $booking_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $booking = $result->fetch_assoc();
    $stmt_check->close();
    
    if (!$booking) {
        throw new Exception("Đơn đặt bàn không tồn tại hoặc đã thanh toán");
    }
    
    // 300 giây = 5 phút
    if ($booking['seconds_passed'] < 300) {
        $remaining = 300 - $booking['seconds_passed'];
        throw new Exception("Chưa đủ 5 phút để hủy đơn. Còn lại: " . ceil($remaining / 60) . " phút");
    }
    
    // Cập nhật trạng thái thành 'da_huy'
    $sql_update = "UPDATE datban 
                  SET TrangThaiDatBan = 'da_huy',
                      GhiChu = CONCAT(IFNULL(GhiChu, ''), ' [Tự động hủy: Không thanh toán sau 5 phút]')
                  WHERE MaDatBan = ?
                  AND TrangThaiThanhToan = 'chuathanhtoan'
                  AND TrangThaiDatBan = 'da_dat'";
    
    $stmt_update = $ketnoi->prepare($sql_update);
    $stmt_update->bind_param("i", $booking_id);
    
    if (!$stmt_update->execute() || $stmt_update->affected_rows == 0) {
        throw new Exception("Không thể hủy đơn đặt bàn (có thể đã bị thay đổi)");
    }
    
    $stmt_update->close();
    $ketnoi->commit();
    
    error_log("Auto cancelled booking #$booking_id - Unpaid after 5 minutes");
    
    echo json_encode([
        'success' => true,
        'message' => 'Đơn đặt bàn đã bị hủy'
    ]);
    
} catch (Exception $e) {
    $ketnoi->rollback();
    error_log("Error auto cancel booking #$booking_id: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$ketnoi->close();
?>