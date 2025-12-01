<?php
$conn = new mysqli("localhost", "root", "", "php_pizza");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maDH   = isset($_POST['MaDH']) ? (int)$_POST['MaDH'] : 0;
    $action = $_POST['action'] ?? '';

    if ($maDH <= 0 || $action === '') {
        die("Yêu cầu không hợp lệ.");
    }

    $sql  = "SELECT trangthai FROM donhang WHERE MaDH = $maDH";
    $res  = $conn->query($sql);
    if (!$res || $res->num_rows === 0) {
        die("Không tìm thấy đơn hàng.");
    }
    $row         = $res->fetch_assoc();
    $currentStat = $row['trangthai'];

    $newStatus = null;
    $error     = '';
    if (strcasecmp($currentStat, 'Chờ xử lý') == 0) {
        if ($action === 'next') {
            $newStatus = 'Đang giao';
        } elseif ($action === 'cancel') {
            $newStatus = 'Đã huỷ';
        }
    } elseif (strcasecmp($currentStat, 'Đang giao') == 0) {
        if ($action === 'next') {
            $newStatus = 'Hoàn thành';
        } elseif ($action === 'cancel') {
            $error = "Đơn đang giao, không thể huỷ.";
        }
    } else {
        $error = "Đơn ở trạng thái '$currentStat' không thể cập nhật nữa.";
    }

    if ($newStatus === null && $error === '') {
        $error = "Hành động không hợp lệ.";
    }

    if ($error !== '') {
        echo "<script>alert('$error'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("UPDATE donhang SET trangthai = ? WHERE MaDH = ?");
    $stmt->bind_param("si", $newStatus, $maDH);

    if ($stmt->execute()) {
        header("Location: donhang_list.php?msg=Cập nhật trạng thái thành công");
        exit;
    } else {
        echo "Lỗi khi cập nhật: " . $stmt->error;
    }
}
