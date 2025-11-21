<?php
session_start();
require __DIR__ . '/../../includes/db_connect.php';

header('Content-Type: application/json');

// Lấy tham số
$date = $_GET['date'] ?? '';
$type = $_GET['type'] ?? 'thuong';
$exclude_booking = intval($_GET['exclude_booking'] ?? 0);

// Validate
if (empty($date)) {
    echo json_encode([
        'success' => false,
        'message' => 'Ngày không hợp lệ'
    ]);
    exit;
}

try {
    $booked_ids = [];
    
    if ($type === 'thuong') {
        // Kiểm tra bàn thường
        $sql = "SELECT DISTINCT MaBan 
                FROM datban 
                WHERE MaBan IS NOT NULL 
                AND DATE(NgayGio) = ? 
                AND TrangThai NOT IN ('da_huy', 'hoan_thanh')";
        
        if ($exclude_booking > 0) {
            $sql .= " AND MaDatBan != ?";
            $stmt = $ketnoi->prepare($sql);
            $stmt->bind_param("si", $date, $exclude_booking);
        } else {
            $stmt = $ketnoi->prepare($sql);
            $stmt->bind_param("s", $date);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $booked_ids[] = intval($row['MaBan']);
        }
        
        $stmt->close();
        
    } elseif ($type === 'tiec') {
        // Kiểm tra phòng tiệc
        $sql = "SELECT DISTINCT MaPhong 
                FROM datban 
                WHERE MaPhong IS NOT NULL 
                AND DATE(NgayGio) = ? 
                AND TrangThai NOT IN ('da_huy', 'hoan_thanh')";
        
        if ($exclude_booking > 0) {
            $sql .= " AND MaDatBan != ?";
            $stmt = $ketnoi->prepare($sql);
            $stmt->bind_param("si", $date, $exclude_booking);
        } else {
            $stmt = $ketnoi->prepare($sql);
            $stmt->bind_param("s", $date);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $booked_ids[] = intval($row['MaPhong']);
        }
        
        $stmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'booked' => $booked_ids,
        'date' => $date,
        'type' => $type
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}
?>