<?php
session_start();
require '../includes/db_connect.php';

header('Content-Type: application/json');

$response = array(
    'success' => false,
    'message' => ''
);

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Vui lòng đăng nhập';
    echo json_encode($response);
    exit();
}

// Kiểm tra dữ liệu đầu vào
if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
    $response['message'] = 'Thiếu mã đơn hàng';
    echo json_encode($response);
    exit();
}

$user_id = mysqli_real_escape_string($ketnoi, $_SESSION['user_id']);
$madh = mysqli_real_escape_string($ketnoi, $_POST['order_id']);

// Kiểm tra đơn hàng có tồn tại và thuộc về user không
$sql_check = "SELECT trangthai FROM donhang 
              WHERE MaDH = '$madh' AND MaKH = '$user_id'";
$result_check = mysqli_query($ketnoi, $sql_check);

if (mysqli_num_rows($result_check) == 0) {
    $response['message'] = 'Đơn hàng không tồn tại hoặc không thuộc về bạn';
    echo json_encode($response);
    exit();
}

$order = mysqli_fetch_assoc($result_check);

// Chỉ cho phép hủy khi đơn hàng đang "Chờ xử lý"
if ($order['trangthai'] != 'Chờ xử lý') {
    $response['message'] = 'Chỉ có thể hủy đơn hàng đang chờ xử lý';
    echo json_encode($response);
    exit();
}

// Cập nhật trạng thái thành "Hủy đơn" thay vì xóa
$sql_cancel = "UPDATE donhang 
               SET trangthai = 'Hủy đơn' 
               WHERE MaDH = '$madh' AND MaKH = '$user_id'";

if (mysqli_query($ketnoi, $sql_cancel)) {
    $response['success'] = true;
    $response['message'] = 'Hủy đơn hàng thành công';
} else {
    $response['message'] = 'Lỗi: ' . mysqli_error($ketnoi);
}

echo json_encode($response);
mysqli_close($ketnoi);
?>