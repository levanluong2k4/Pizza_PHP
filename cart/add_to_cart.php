<?php
session_start();

$maSP = $_GET['id'] ?? '';
$maSize = $_GET['masize'] ?? '';
$soLuong = $_GET['soluong'] ?? 1;

$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");

// --- Trường hợp 1: Người dùng CHƯA đăng nhập ---
if (!isset($_SESSION['user_id'])) {
    // Nếu sản phẩm chưa có trong session
    if (empty($_SESSION['cart'][$maSP . '_' . $maSize])) {
        $sql = "SELECT sp.MaSP, sp.TenSP, ss.Gia, ss.Anh, s.TenSize, s.MaSize
                FROM sanpham sp
                JOIN sanpham_size ss ON sp.MaSP = ss.MaSP
                JOIN size s ON ss.MaSize = s.MaSize
                WHERE sp.MaSP = '$maSP' AND s.MaSize = '$maSize'";
        $result = mysqli_query($ketnoi, $sql);
        $item = mysqli_fetch_array($result);

      $_SESSION['cart'][$maSP . '_' . $maSize] = array(
    'masp' => $item['MaSP'],
    'tensp' => $item['TenSP'],
    'tensize' => $item['TenSize'],
    'size_id' => $item['MaSize'],
    'price' => $item['Gia'],
    'quantity' => $soLuong,
    'anh' => $item['Anh'],
    'subtotal' => $item['Gia'] * $soLuong
        );

    } else {
        // Nếu đã có trong giỏ thì tăng số lượng
        $_SESSION['cart'][$maSP . '_' . $maSize]['quantity'] += $soLuong;
$_SESSION['cart'][$maSP . '_' . $maSize]['subtotal'] =
    $_SESSION['cart'][$maSP . '_' . $maSize]['price'] * $_SESSION['cart'][$maSP . '_' . $maSize]['quantity'];

    }

    // Trả về tổng số lượng trong giỏ cho JS (ajax)
    $totalQuantity = 0;
    foreach ($_SESSION['cart'] as $item) {
        $totalQuantity += $item['quantity'];
    }

       // Lưu thông báo thành công vào session
    $_SESSION['cart_message'] = 'success';
        if (!empty($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: ../trangchu.php"); // fallback nếu không có referer
        }
    exit();
}

else {


    // --- Trường hợp 2: Người dùng ĐÃ đăng nhập ---
$user_id = $_SESSION['user_id'];



// --- Giờ thêm sản phẩm mới vào giỏ hàng trong DB ---
$sql_cart = "SELECT * FROM giohang WHERE MaKH='$user_id'";
$result = mysqli_query($ketnoi, $sql_cart);
if (mysqli_num_rows($result) == 0) {
    mysqli_query($ketnoi, "INSERT INTO giohang(MaKH) VALUES('$user_id')");
}

$sql_cart_detail = "SELECT * FROM chitietgiohang 
                    WHERE CartID=(SELECT CartID FROM giohang WHERE MaKH='$user_id')
                    AND MaSP='$maSP' AND MaSize='$maSize'";
$result_detail = mysqli_query($ketnoi, $sql_cart_detail);

if (mysqli_num_rows($result_detail) == 0) {
    $sql_insert_detail = "INSERT INTO chitietgiohang(CartID, MaSP, MaSize, Quantity)
                          VALUES((SELECT CartID FROM giohang WHERE MaKH='$user_id'),
                                 '$maSP','$maSize','$soLuong')";
    mysqli_query($ketnoi, $sql_insert_detail);
} else {
    $sql_update_detail = "UPDATE chitietgiohang 
                          SET Quantity = Quantity + '$soLuong'
                          WHERE CartID=(SELECT CartID FROM giohang WHERE MaKH='$user_id')
                          AND MaSP='$maSP' AND MaSize='$maSize'";
    mysqli_query($ketnoi, $sql_update_detail);
}

   $_SESSION['cart_message'] = 'success';
    header("location:../trangchu.php");
exit();
}
?>


