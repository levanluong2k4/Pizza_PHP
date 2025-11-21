<?php
require '../includes/db_connect.php';

// Set timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
$ketnoi->query("SET time_zone = '+07:00'");

// Log file
$logFile = __DIR__ . '/cron_cancel_bookings.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($logFile, "$timestamp $message\n", FILE_APPEND);
}

try {
    logMessage("Starting auto-cancel job");
    
    // Debug: Log thời gian hiện tại
    $currentTime = date('Y-m-d H:i:s');
    logMessage("Current server time: $currentTime");
    
    // Tìm các đơn chưa thanh toán và đã quá 5 phút
    $sql = "SELECT MaDatBan, HoTen, SDT, NgayTao,
                   TIMESTAMPDIFF(MINUTE, NgayTao, NOW()) as minutes_passed
            FROM datban 
            WHERE TrangThaiThanhToan = 'chuathanhtoan' 
            AND TrangThaiDatBan = 'da_dat'
            AND TIMESTAMPDIFF(MINUTE, NgayTao, NOW()) >= 5";
    
    logMessage("SQL Query: $sql");
    
    $result = $ketnoi->query($sql);
    
    logMessage("Found {$result->num_rows} bookings to check");
    
    if ($result->num_rows > 0) {
        $cancelledCount = 0;
        
        while ($row = $result->fetch_assoc()) {
            $booking_id = $row['MaDatBan'];
            
            logMessage("Processing booking #$booking_id - Created: {$row['NgayTao']} - Minutes passed: {$row['minutes_passed']}");
            
            // Bắt đầu transaction cho mỗi đơn
            $ketnoi->begin_transaction();
            
            try {
                // Cập nhật trạng thái thành 'da_huy'
                $sql_update = "UPDATE datban 
                              SET TrangThaiDatBan = 'da_huy',
                                  GhiChu = CONCAT(IFNULL(GhiChu, ''), ' [Tự động hủy: Không thanh toán sau 5 phút]')
                              WHERE MaDatBan = ?
                              AND TrangThaiThanhToan = 'chuathanhtoan'
                              AND TrangThaiDatBan = 'da_dat'";
                
                $stmt = $ketnoi->prepare($sql_update);
                $stmt->bind_param("i", $booking_id);
                
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $ketnoi->commit();
                    $cancelledCount++;
                    logMessage("✓ Cancelled booking #$booking_id - Customer: {$row['HoTen']} ({$row['SDT']})");
                } else {
                    $ketnoi->rollback();
                    logMessage("✗ Booking #$booking_id was not updated (already changed or locked)");
                }
                
                $stmt->close();
                
            } catch (Exception $e) {
                $ketnoi->rollback();
                logMessage("ERROR cancelling booking #$booking_id: " . $e->getMessage());
            }
        }
        
        logMessage("Successfully cancelled $cancelledCount bookings");
    } else {
        logMessage("No unpaid bookings to cancel");
    }
    
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
}

if (isset($ketnoi)) {
    $ketnoi->close();
}

logMessage("Cron job completed\n");
?>