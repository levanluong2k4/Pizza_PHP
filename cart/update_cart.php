<?php
session_start();
$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");
$id_product = $_GET['masp'] ?? '';
$maSize = $_GET['masize'] ?? '';
$type = $_GET['type'] ?? '';



if ($id_product && $type) {
// Trường hợp 1: Người dùng đã đăng nhập
if (isset($_SESSION['user_id'])) {
$user_id = $_SESSION['user_id'];
$sql_cart_id = "SELECT CartID FROM giohang WHERE MaKH='$user_id'";
$result_cart = mysqli_query($ketnoi, $sql_cart_id);
$cart_row = mysqli_fetch_assoc($result_cart);
$cartId = $cart_row['CartID'] ?? null;

if ($cartId) {
if ($type === 'increase') {
// Tăng số lượng
$sql_update = "UPDATE chitietgiohang
SET Quantity = Quantity + 1
WHERE CartID='$cartId' AND MaSP='$id_product' AND MaSize='$maSize'";
mysqli_query($ketnoi, $sql_update);
$_SESSION['cart_message'] = 'increase_success';

} elseif ($type === 'decrease') {
// Giảm số lượng (kiểm tra không cho giảm xuống dưới 1)
$sql_check = "SELECT Quantity FROM chitietgiohang
WHERE CartID='$cartId' AND MaSP='$id_product' AND MaSize='$maSize'";
$result_check = mysqli_query($ketnoi, $sql_check);
$item = mysqli_fetch_assoc($result_check);

if ($item['Quantity'] > 1) {
$sql_update = "UPDATE chitietgiohang
SET Quantity = Quantity - 1
WHERE CartID='$cartId' AND MaSP='$id_product' AND MaSize='$maSize'";
mysqli_query($ketnoi, $sql_update);
$_SESSION['cart_message'] = 'decrease_success';
} else {
$_SESSION['cart_message'] = 'min_quantity';
}

} elseif ($type === 'delete') {
// Xóa sản phẩm
$sql_delete = "DELETE FROM chitietgiohang
WHERE CartID='$cartId' AND MaSP='$id_product' AND MaSize='$maSize'";
mysqli_query($ketnoi, $sql_delete);
$_SESSION['cart_message'] = 'delete_success';
}
}

}







// Trường hợp 2: Người dùng chưa đăng nhập (session cart)
else {
$cart_key = $id_product . '_' . $maSize;

if (isset($_SESSION['cart'][$cart_key])) {
if ($type === 'increase') {
// Tăng số lượng
$_SESSION['cart'][$cart_key]['quantity']++;
$_SESSION['cart'][$cart_key]['subtotal'] =
$_SESSION['cart'][$cart_key]['price'] *
$_SESSION['cart'][$cart_key]['quantity'];
$_SESSION['cart_message'] = 'increase_success';

} elseif ($type === 'decrease') {
// Giảm số lượng
if ($_SESSION['cart'][$cart_key]['quantity'] > 1) {
$_SESSION['cart'][$cart_key]['quantity']--;
$_SESSION['cart'][$cart_key]['subtotal'] =
$_SESSION['cart'][$cart_key]['price'] *
$_SESSION['cart'][$cart_key]['quantity'];
$_SESSION['cart_message'] = 'decrease_success';
} else {
$_SESSION['cart_message'] = 'min_quantity';
}

} elseif ($type === 'delete') {
// Xóa sản phẩm
unset($_SESSION['cart'][$cart_key]);
$_SESSION['cart_message'] = 'delete_success';
}
}
}

header("Location: ../cart.php");
exit();
}

?>