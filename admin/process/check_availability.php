<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/../../includes/db_connect.php';

// if (!isset($_SESSION['admin'])) {
//     echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
//     exit;
// }

$date = $_GET['date'] ?? '';
$type = $_GET['type'] ?? 'thuong';

if (empty($date)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ngày']);
    exit;
}

try {
    $booked_ids = [];
    
    if ($type == 'thuong') {
        // Lấy danh sách bàn đã đặt
        $sql = "SELECT DISTINCT MaBan FROM datban 
                WHERE MaBan IS NOT NULL
                AND DATE(NgayGio) = ?
                AND TrangThaiDatBan IN ('da_dat', 'da_xac_nhan', 'dang_su_dung')";
        
        $stmt = $ketnoi->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $booked_ids[] = intval($row['MaBan']);
        }
        $stmt->close();
        
    } else {
        // Lấy danh sách phòng đã đặt
        $sql = "SELECT DISTINCT MaPhong FROM datban 
                WHERE MaPhong IS NOT NULL
                AND DATE(NgayGio) = ?
                AND TrangThaiDatBan IN ('da_dat', 'da_xac_nhan', 'dang_su_dung')";
        
        $stmt = $ketnoi->prepare($sql);
        $stmt->bind_param("s", $date);
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

$ketnoi->close();
?>