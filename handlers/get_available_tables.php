<?php
header('Content-Type: application/json');
require '../includes/db_connect.php';

$ngayden = $_GET['ngayden'] ?? '';
$loaidatban = $_GET['loaidatban'] ?? 'thuong';

if (empty($ngayden)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng chọn ngày'
    ]);
    exit;
}

try {
    $result = [];
    
    if ($loaidatban == 'thuong') {
        // Lấy danh sách bàn trống cho ngày đã chọn
        $sql = "SELECT ba.* FROM banan ba
                WHERE ba.MaBan NOT IN (
                    SELECT db.MaBan FROM datban db
                    WHERE db.MaBan IS NOT NULL
                    AND DATE(db.NgayGio) = ?
                    AND db.TrangThaiDatBan IN ('cho_xac_nhan', 'da_xac_nhan', 'dang_su_dung')
                )
                AND ba.TrangThai != 'bao_tri'
                ORDER BY ba.SoBan";
        
        $stmt = $ketnoi->prepare($sql);
        $stmt->bind_param("s", $ngayden);
        $stmt->execute();
        $query_result = $stmt->get_result();
        
        while ($row = $query_result->fetch_assoc()) {
            $result[] = $row;
        }
        $stmt->close();
        
    } else {
        // Lấy danh sách phòng trống cho ngày đã chọn
        $sql = "SELECT pt.* FROM phongtiec pt
                WHERE pt.MaPhong NOT IN (
                    SELECT db.MaPhong FROM datban db
                    WHERE db.MaPhong IS NOT NULL
                    AND DATE(db.NgayGio) = ?
                    AND db.TrangThaiDatBan IN ('cho_xac_nhan', 'da_xac_nhan', 'dang_su_dung')
                )
                AND pt.TrangThai != 'bao_tri'
                ORDER BY pt.SoPhong";
        
        $stmt = $ketnoi->prepare($sql);
        $stmt->bind_param("s", $ngayden);
        $stmt->execute();
        $query_result = $stmt->get_result();
        
        while ($row = $query_result->fetch_assoc()) {
            $result[] = $row;
        }
        $stmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'data' => $result,
        'message' => 'Lấy dữ liệu thành công',
        'count' => count($result)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}

$ketnoi->close();
?>
