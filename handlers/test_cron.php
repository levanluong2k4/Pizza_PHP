<?php
require '../includes/db_connect.php';

// Set timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
$ketnoi->query("SET time_zone = '+07:00'");

echo "<h2>Test Cron Cancel Bookings</h2>";
echo "<p><strong>Current Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Lấy tất cả đơn chưa thanh toán
$sql = "SELECT 
            MaDatBan, 
            HoTen, 
            SDT, 
            NgayTao,
            TrangThaiThanhToan,
            TrangThaiDatBan,
            TIMESTAMPDIFF(MINUTE, NgayTao, NOW()) as minutes_passed
        FROM datban 
        WHERE TrangThaiThanhToan = 'chuathanhtoan' 
        AND TrangThaiDatBan = 'da_dat'
        ORDER BY NgayTao DESC";

$result = $ketnoi->query($sql);

echo "<h3>Tìm thấy: {$result->num_rows} đơn chưa thanh toán</h3>";

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr>
            <th>Mã ĐB</th>
            <th>Họ tên</th>
            <th>SĐT</th>
            <th>Ngày tạo</th>
            <th>Phút đã qua</th>
            <th>TT Thanh toán</th>
            <th>TT Đặt bàn</th>
            <th>Hành động</th>
          </tr>";
    
    while ($row = $result->fetch_assoc()) {
        $canCancel = $row['minutes_passed'] >= 5;
        $style = $canCancel ? "background-color: #ffcccc;" : "";
        
        echo "<tr style='$style'>";
        echo "<td>#{$row['MaDatBan']}</td>";
        echo "<td>{$row['HoTen']}</td>";
        echo "<td>{$row['SDT']}</td>";
        echo "<td>{$row['NgayTao']}</td>";
        echo "<td><strong>{$row['minutes_passed']} phút</strong></td>";
        echo "<td>{$row['TrangThaiThanhToan']}</td>";
        echo "<td>{$row['TrangThaiDatBan']}</td>";
        echo "<td>" . ($canCancel ? "❌ <strong>SẼ BỊ HỦY</strong>" : "✓ Còn " . (5 - $row['minutes_passed']) . " phút") . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Đếm số đơn sẽ bị hủy
    $sql_count = "SELECT COUNT(*) as total
                  FROM datban 
                  WHERE TrangThaiThanhToan = 'chuathanhtoan' 
                  AND TrangThaiDatBan = 'da_dat'
                  AND TIMESTAMPDIFF(MINUTE, NgayTao, NOW()) >= 5";
    
    $count_result = $ketnoi->query($sql_count);
    $count = $count_result->fetch_assoc()['total'];
    
    echo "<p style='color: red; font-size: 18px;'><strong>Số đơn sẽ bị hủy: $count</strong></p>";
    
    echo "<hr>";
    echo "<h3>Test hủy đơn:</h3>";
    echo "<form method='POST'>";
    echo "<button type='submit' name='test_cancel' value='1' style='padding: 10px 20px; font-size: 16px; background: red; color: white; border: none; cursor: pointer;'>
            🗑️ HỦY TẤT CẢ ĐƠN QUÁ 5 PHÚT
          </button>";
    echo "</form>";
    
} else {
    echo "<p>Không có đơn nào cần hủy</p>";
}

// Xử lý hủy đơn khi click button
if (isset($_POST['test_cancel'])) {
    echo "<hr><h3>Đang thực hiện hủy đơn...</h3>";
    
    $sql_cancel = "SELECT MaDatBan, HoTen, SDT 
                   FROM datban 
                   WHERE TrangThaiThanhToan = 'chuathanhtoan' 
                   AND TrangThaiDatBan = 'da_dat'
                   AND TIMESTAMPDIFF(MINUTE, NgayTao, NOW()) >= 5";
    
    $cancel_result = $ketnoi->query($sql_cancel);
    $cancelled = 0;
    
    while ($row = $cancel_result->fetch_assoc()) {
        $booking_id = $row['MaDatBan'];
        
        $ketnoi->begin_transaction();
        
        try {
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
                $cancelled++;
                echo "<p style='color: green;'>✓ Đã hủy đơn #{$booking_id} - {$row['HoTen']} ({$row['SDT']})</p>";
            } else {
                $ketnoi->rollback();
                echo "<p style='color: orange;'>⚠ Không thể hủy đơn #{$booking_id}</p>";
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $ketnoi->rollback();
            echo "<p style='color: red;'>✗ Lỗi hủy đơn #{$booking_id}: {$e->getMessage()}</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3 style='color: blue;'>Đã hủy thành công: $cancelled đơn</h3>";
    echo "<p><a href='test_cron.php'>🔄 Tải lại trang</a></p>";
}

$ketnoi->close();
?>
```

## Cách sử dụng:

1. **Lưu file trên** vào `/handlers/test_cron.php`

2. **Truy cập qua browser**:
```
http://localhost/unitop/backend/lesson/school/project_pizza/handlers/test_cron.php