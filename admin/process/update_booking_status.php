<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/../../includes/db_connect.php';

// if (!isset($_SESSION['admin'])) {
//     echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
//     exit;
// }

$madatban = intval($_POST['madatban'] ?? 0);
$status = $_POST['status'] ?? '';

$allowed_status = ['da_dat','da_xac_nhan','dang_su_dung', 'thanh_cong', 'da_huy'];

if ($madatban <= 0) {
    echo json_encode(['success' => false, 'message' => 'Mã đặt bàn không hợp lệ']);
    exit;
}

if (!in_array($status, $allowed_status)) {
    echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
    exit;
}

try {

    if ($status == 'thanh_cong' || $status == 'hoan_thanh') {
        $sql = "UPDATE datban SET TrangThaiDatBan = ?, TrangThaiThanhToan = 'da_thanh_toan' WHERE MaDatBan = ?";
        $stmt = $ketnoi->prepare($sql);
        $stmt->bind_param("si", $status, $madatban);
    } else {
        // Các trạng thái khác chỉ cập nhật trạng thái đặt bàn
        $sql = "UPDATE datban SET TrangThaiDatBan = ? WHERE MaDatBan = ?";
        $stmt = $ketnoi->prepare($sql);
        $stmt->bind_param("si", $status, $madatban);
    }
   
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Cập nhật trạng thái thành công'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Lỗi khi cập nhật: ' . $stmt->error
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