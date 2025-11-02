<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../sign_in.php");
    exit();
}


$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

$user_id = $_SESSION['user_id'];

$sql_user = "UPDATE `khachhang` SET `Diachi`='{$_SESSION['temp_diachi']}' WHERE MaKH='$user_id'";
mysqli_query($ketnoi, $sql_user);

// Lấy giỏ hàng
$sql_cart = "SELECT * FROM giohang WHERE MaKH='$user_id'";
$cart = mysqli_query($ketnoi, $sql_cart);
$result_cart = mysqli_fetch_array($cart);

// Lấy chi tiết giỏ hàng
$sql_card_detail = "SELECT * FROM chitietgiohang WHERE CartID='{$result_cart['CartID']}'";
$card_detail = mysqli_query($ketnoi, $sql_card_detail);

// --- Tính tổng tiền ---
$total_price = 0;
foreach ($card_detail as $products) {
    $product_id = $products['MaSP'];
    $product_size = $products['MaSize'];
    $quantity = $products['Quantity'];

    $sql_product = "SELECT * FROM sanpham_size WHERE MaSP='$product_id' AND MaSize='$product_size'";
    $product = mysqli_query($ketnoi, $sql_product);
    $result_product = mysqli_fetch_array($product);

    $price = $result_product['Gia'];
    $total_price += $price * $quantity; // cộng dồn
}

$tennguoinhan = $_SESSION['temp_hoten'];
$sdtnguoinhan = $_SESSION['temp_sodt'];
$diachinguoinhan = $_SESSION['temp_diachi'];


// --- Chèn đơn hàng ---
$sql_order = "INSERT INTO donhang (MaKH, TongTien, Tennguoinhan, sdtnguoinhan, diachinguoinhan)
VALUES ('$user_id', '$total_price', '$tennguoinhan', '$sdtnguoinhan', '$diachinguoinhan')";
$result_order = mysqli_query($ketnoi, $sql_order);

if ($result_order) {
    $order_id = mysqli_insert_id($ketnoi);

    // --- Chèn chi tiết đơn hàng ---
    foreach ($card_detail as $products) {
        $product_id = $products['MaSP'];
        $product_size = $products['MaSize'];
        $quantity = $products['Quantity'];

        $sql_product = "SELECT * FROM sanpham_size WHERE MaSP='$product_id' AND MaSize='$product_size'";
        $product = mysqli_query($ketnoi, $sql_product);
        $result_product = mysqli_fetch_array($product);
        $price = $result_product['Gia'];
        $thanh_tien = $price * $quantity;

        $sql_order_detail = "INSERT INTO chitietdonhang (MaDH, MaSP, MaSize, SoLuong, ThanhTien)
                             VALUES ('$order_id', '$product_id', '$product_size', '$quantity', '$thanh_tien')";
        mysqli_query($ketnoi, $sql_order_detail);
    }

    // --- Xóa giỏ hàng ---
    $sql_delete_cart_detail = "DELETE FROM chitietgiohang WHERE CartID='{$result_cart['CartID']}'";
    mysqli_query($ketnoi, $sql_delete_cart_detail);

    $sql_delete_cart = "DELETE FROM giohang WHERE MaKH='$user_id'";
    mysqli_query($ketnoi, $sql_delete_cart);
    unset($_SESSION['temp_hoten']);
    unset($_SESSION['temp_sodt']);
    unset($_SESSION['temp_diachi']);
    unset($_SESSION['temp_so_nha']);
echo '<pre>';
print_r($_SESSION);
echo '</pre>';

    // --- Chuyển hướng ---
    header("Location: ../order_confirmation.php?order_id=$order_id");
    exit();
} else {
    echo "Lỗi khi chèn đơn hàng: " . mysqli_error($ketnoi);
}
?>
