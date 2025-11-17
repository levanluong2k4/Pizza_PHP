<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/../../includes/db_connect.php';

// if (!isset($_SESSION['admin'])) {
//     echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
//     exit;
// }

$madatban = intval($_POST['madatban'] ?? 0);

if ($madatban <= 0) {
    echo json_encode(['success' => false, 'message' => 'Mã đặt bàn không hợp lệ']);
    exit;
}

try {
    $sql = "UPDATE datban SET TrangThaiDatBan = 'da_huy' WHERE MaDatBan = ?";
    $stmt = $ketnoi->prepare($sql);
    $stmt->bind_param("i", $madatban);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Đã hủy đặt bàn thành công'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Lỗi khi hủy: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}

$ketnoi->close();
?>