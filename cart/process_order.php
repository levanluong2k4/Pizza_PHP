<?php 
session_start();
$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

if (!isset($_SESSION['user_id']) && (!isset($_POST['order_guest']) || !isset($_SESSION['cart']))) {
    header("Location: ../sign_in.php");
    exit();
}

$tennguoinhan = $_SESSION['temp_hoten'];
$sdtnguoinhan = $_SESSION['temp_sodt'];
$diachinguoinhan = $_SESSION['temp_diachi'];

// --- Trường hợp khách vãng lai ---
if (isset($_POST['order_guest'])) {

    $total_price = 0;
    foreach ($_SESSION['cart'] as $item) {
        $maSP = $item['masp'];
        $maSize = $item['size_id'];
        $soLuong = $item['quantity'];
        $sql_product = "SELECT Gia FROM sanpham_size WHERE MaSP='$maSP' AND MaSize='$maSize'";
        $res = mysqli_query($ketnoi, $sql_product);
        $row = mysqli_fetch_assoc($res);
        $price = $row['Gia'];
        $total_price += $price * $soLuong;
    }

    $sql_order = "INSERT INTO donhang (TongTien, diachinguoinhan, sdtnguoinhan, Tennguoinhan, is_guest)
                  VALUES ('$total_price', '$diachinguoinhan', '$sdtnguoinhan', '$tennguoinhan', 1)";
    mysqli_query($ketnoi, $sql_order);
    $order_id = mysqli_insert_id($ketnoi);

    foreach ($_SESSION['cart'] as $item) {
        $maSP = $item['masp'];
        $maSize = $item['size_id'];
        $soLuong = $item['quantity'];
        $sql_product = "SELECT Gia FROM sanpham_size WHERE MaSP='$maSP' AND MaSize='$maSize'";
        $res = mysqli_query($ketnoi, $sql_product);
        $row = mysqli_fetch_assoc($res);
        $price = $row['Gia'];
        $thanh_tien = $price * $soLuong;

        mysqli_query($ketnoi, "INSERT INTO chitietdonhang (MaDH, MaSP, MaSize, SoLuong, ThanhTien)
                               VALUES ('$order_id', '$maSP', '$maSize', '$soLuong', '$thanh_tien')");
    }

    unset($_SESSION['cart']);
    header("Location: ../order_confirmation.php?order_id=$order_id");
    exit();
}

// --- Trường hợp khách đăng nhập ---
else {
    $user_id = $_SESSION['user_id'];
    $total_price = 0;

    $res_cart = mysqli_query($ketnoi, "SELECT * FROM giohang WHERE MaKH='$user_id'");
    $cart = mysqli_fetch_assoc($res_cart);
    $cartId = $cart['CartID'];

    $res_items = mysqli_query($ketnoi, "SELECT * FROM chitietgiohang WHERE CartID='$cartId'");
    while ($item = mysqli_fetch_assoc($res_items)) {
        $maSP = $item['MaSP'];
        $maSize = $item['MaSize'];
        $quantity = $item['Quantity'];
        $res_price = mysqli_query($ketnoi, "SELECT Gia FROM sanpham_size WHERE MaSP='$maSP' AND MaSize='$maSize'");
        $p = mysqli_fetch_assoc($res_price);
        $price = $p['Gia'];
        $total_price += $price * $quantity;
    }

    mysqli_query($ketnoi, "INSERT INTO donhang (MaKH, TongTien, diachinguoinhan, sdtnguoinhan, Tennguoinhan, is_guest)
                           VALUES ('$user_id', '$total_price', '$diachinguoinhan', '$sdtnguoinhan', '$tennguoinhan', 0)");
    $order_id = mysqli_insert_id($ketnoi);

    mysqli_data_seek($res_items, 0); // reset con trỏ
    while ($item = mysqli_fetch_assoc($res_items)) {
        $maSP = $item['MaSP'];
        $maSize = $item['MaSize'];
        $quantity = $item['Quantity'];
        $res_price = mysqli_query($ketnoi, "SELECT Gia FROM sanpham_size WHERE MaSP='$maSP' AND MaSize='$maSize'");
        $p = mysqli_fetch_assoc($res_price);
        $price = $p['Gia'];
        $thanh_tien = $price * $quantity;
        mysqli_query($ketnoi, "INSERT INTO chitietdonhang (MaDH, MaSP, MaSize, SoLuong, ThanhTien)
                               VALUES ('$order_id', '$maSP', '$maSize', '$quantity', '$thanh_tien')");
    }

    // Xóa giỏ hàng (đúng thứ tự)
    mysqli_query($ketnoi, "DELETE FROM chitietgiohang WHERE CartID='$cartId'");
    mysqli_query($ketnoi, "DELETE FROM giohang WHERE MaKH='$user_id'");

    unset($_SESSION['temp_hoten'], $_SESSION['temp_sodt'], $_SESSION['temp_diachi'], $_SESSION['temp_so_nha']);

    header("Location: ../order_confirmation.php?order_id=$order_id");
    exit();
}




?>