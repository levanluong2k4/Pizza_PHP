<?php
session_start();

if(isset($_SESSION['user_id'])){
$userid=$_SESSION['user_id'];
$sql_us = "SELECT * FROM khachhang WHERE MaKH='$userid'";
$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");
$result_us = mysqli_query($ketnoi, $sql_us);
$user = mysqli_fetch_assoc($result_us);
  if (!isset($_SESSION['temp_hoten'])) $_SESSION['temp_hoten'] = $user['HoTen'];
    if (!isset($_SESSION['temp_sodt'])) $_SESSION['temp_sodt'] = $user['SoDT'];
    if (!isset($_SESSION['temp_diachi'])) $_SESSION['temp_diachi'] = $user['DiaChi'];

}
if(isset($_POST['update_info'])){
   if (!empty($_POST['hoten'])) {
        $_SESSION['temp_hoten'] = $_POST['hoten'];
    }
    if (!empty($_POST['sodt'])) {
        $_SESSION['temp_sodt'] = $_POST['sodt'];
    }
    if (!empty($_POST['diachi'])) {
        $_SESSION['temp_diachi'] = $_POST['diachi'];
    }
}


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

header("Location: cart.php");
exit();
}

// -------------------------------
// LẤY DỮ LIỆU GIỎ HÀNG ĐỂ HIỂN THỊ
// -------------------------------
$cartItems = [];
$tongtien = 0;

// Trường hợp 1: Người dùng đã đăng nhập
if (isset($_SESSION['user_id'])) {
$sql_cart = "SELECT * FROM giohang WHERE MaKH='" . $_SESSION['user_id'] . "'";
$result = mysqli_query($ketnoi, $sql_cart);
$row = mysqli_fetch_array($result);
$cartId = $row['CartID'] ?? null;

if ($cartId) {
$sql_items = "SELECT ct.*, sp.TenSP, ss.Gia, ss.Anh, s.TenSize, s.MaSize
FROM chitietgiohang ct
JOIN sanpham_size ss ON ct.MaSP = ss.MaSP AND ct.MaSize = ss.MaSize
JOIN sanpham sp ON ss.MaSP = sp.MaSP
JOIN size s ON ss.MaSize = s.MaSize
WHERE ct.CartID = '$cartId'";
$result_items = mysqli_query($ketnoi, $sql_items);

while ($item = mysqli_fetch_assoc($result_items)) {
$subtotal = $item['Gia'] * $item['Quantity'];
$cartItems[] = [
'masp' => $item['MaSP'],
'masize' => $item['MaSize'],
'tensp' => $item['TenSP'],
'tensize' => $item['TenSize'],
'price' => $item['Gia'],
'quantity' => $item['Quantity'],
'anh' => $item['Anh'],
'subtotal' => $subtotal
];
$tongtien += $subtotal;
}
}
}
// Trường hợp 2: Người dùng chưa đăng nhập (session cart)
else {
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
foreach ($_SESSION['cart'] as $key => $item) {
$cartItems[] = [
'masp' => $item['masp'],
'masize' => $item['size_id'],
'tensp' => $item['tensp'],
'tensize' => $item['tensize'],
'price' => $item['price'],
'quantity' => $item['quantity'],
'anh' => $item['anh'],
'subtotal' => $item['subtotal']
];
$tongtien += $item['subtotal'];
}
}
}
mysqli_close($ketnoi);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Giỏ hàng - Pizza</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- animate -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="./css/animate.css">
    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />

    <!-- CSS -->
    <link rel="stylesheet" href="css/bai6.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/sign_up.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


</head>

<body>
    <header class="bg-icon">
        <?php include 'navbar/navbar.php'; ?>
    </header>

    <!-- Hiển thị thông báo -->
    <?php if(isset($_SESSION['cart_message'])): ?>
    <?php
        $message = '';
        $alert_type = 'success';

        switch($_SESSION['cart_message']) {
      
        case 'delete_success':
        $message = 'Đã xóa sản phẩm khỏi giỏ hàng!';
        $alert_type = 'warning';
        break;
      
        }
        ?>
    <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show alert-cart" role="alert">
        <i class="fa fa-check-circle"></i> <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['cart_message']); ?>
    <?php endif; ?>

    <main class="container my-5">
        <h2 class="text-center mb-4">Giỏ hàng của bạn</h2>

        <?php if (empty($cartItems)): ?>
        <!-- Hiển thị giỏ hàng trống -->
        <div class="text-center">
            <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
            <h4>Giỏ hàng của bạn đang trống</h4>
            <p class="text-muted">Hãy thêm một số sản phẩm vào giỏ hàng để
                tiếp tục mua sắm</p>
            <a href="trangchu.php" class="btn btn-success">Tiếp tục mua
                sắm</a>
        </div>

        <?php else: ?>
        <!-- Hiển thị danh sách sản phẩm -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sản phẩm trong giỏ hàng (<?php echo
                                count($cartItems); ?> sản phẩm)</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="row cart-item mb-3 pb-3 border-bottom align-items-center">
                            <div class="col-md-2">
                                <img src="<?php echo $item['anh']; ?>" class="img-fluid rounded"
                                    alt="<?php echo $item['tensp']; ?>">
                            </div>
                            <div class="col-md-4">
                                <h6><?php echo $item['tensp']; ?></h6>
                                <p class="text-muted mb-1">Size: <?php echo
                                        $item['tensize']; ?></p>
                                <p class="text-success mb-0"><?php echo
                                        number_format($item['price']); ?>
                                    VNĐ</p>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <a href="?masp=<?php echo $item['masp']; ?>&masize=<?php echo $item['masize']; ?>&type=decrease"
                                        id="btn-decrease" class="btn btn-outline-secondary btn-sm">-</a>
                                    <input type="number" class="form-control form-control-sm text-center"
                                        value="<?php echo $item['quantity']; ?>" min="1" readonly>
                                    <a href="?masp=<?php echo $item['masp']; ?>&masize=<?php echo $item['masize']; ?>&type=increase"
                                        class="btn btn-outline-secondary btn-sm" id="btn-increase">+</a>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <p class="mb-0"><strong><?php echo
                                            number_format($item['subtotal']); ?>
                                        VNĐ</strong></p>
                            </div>
                            <div class="col-md-1">
                                <a href="?masp=<?php echo $item['masp']; ?>&masize=<?php echo $item['masize']; ?>&type=delete"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
             
                                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Thông tin người nhận</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="infoForm">
                            <!-- Tên người nhận -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span style="min-width: 150px;">Tên người nhận</span>
                                <span id="hoten_display" style="flex: 1; text-align: center;">
                                    <?php 
                    if((isset($_SESSION['temp_hoten']))){
                        echo $_SESSION['temp_hoten'];
                       } 
                    ?>
                                </span>
                                <input type="text" name="hoten" id="hoten_input" class="form-control mx-2"
                                    value="<?php echo isset($_SESSION['temp_hoten']) ? $_SESSION['temp_hoten'] : ''; ?>"
                                    style="display: none; flex: 1;">
                                <i class="fa-solid fa-pen-to-square edit-btn" style="color: #30d952; cursor: pointer;"
                                    data-field="hoten"></i>
                            </div>

                            <!-- Số điện thoại -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span style="min-width: 150px;">Số điện thoại</span>
                                <span id="sodt_display" style="flex: 1; text-align: center;">
                                    <?php 
                    if((isset($_SESSION['temp_sodt']))){
                        echo $_SESSION['temp_sodt'];
                       }
                    ?>
                                </span>
                                <input type="number" name="sodt" id="sodt_input" class="form-control mx-2" value="<?php if((isset($_SESSION['temp_sodt']))){
                        echo $_SESSION['temp_sodt'];
                       } ?>" style="display: none; flex: 1;">
                                <i class="fa-solid fa-pen-to-square edit-btn" style="color: #30d952; cursor: pointer;"
                                    data-field="sodt"></i>
                            </div>

                            <!-- Địa chỉ -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span style="min-width: 150px;">Địa chỉ</span>
                                <span id="diachi_display" style="flex: 1; text-align: center;">
                                    <?php 
                    if((isset($_SESSION['temp_diachi']))){
                        echo $_SESSION['temp_diachi'];
                    }
                    ?>
                                </span>
                                <input type="text" name="diachi" id="diachi_input" class="form-control mx-2" value="<?php if((isset($_SESSION['temp_diachi']))){
                        echo $_SESSION['temp_diachi'];
                       } ?>" style="display: none; flex: 1;">
                                <i class="fa-solid fa-pen-to-square edit-btn" style="color: #30d952; cursor: pointer;"
                                    data-field="diachi"></i>
                            </div>

                            <hr>

                            <!-- Nút lưu -->
                            <button type="submit" name="update_info" id="saveBtn" class="btn btn-success"
                                style="display: none; width: 100%;">
                                <i class="fa-solid fa-floppy-disk"></i> Lưu thông tin
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tổng kết đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <span><?php echo number_format($tongtien);
                                    ?> VNĐ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <span class="text-success">Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng cộng:</strong>
                            <strong class="text-danger"><?php echo
                                    number_format($tongtien); ?>
                                VNĐ</strong>
                        </div>
                        <a href="cart/process_order.php" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-shopping-bag"></i> Đặt hàng
                        </a>

                    </div>
                </div>

          


            </div>
            <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <script>
    // Tự động ẩn thông báo sau 3 giây
    setTimeout(function() {
        var alert = document.querySelector('.alert-cart');
        if (alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 3000);


    document.addEventListener('DOMContentLoaded', function() {
        const editBtns = document.querySelectorAll('.edit-btn');
        const saveBtn = document.getElementById('saveBtn');
        let isEditing = false;

        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const field = this.getAttribute('data-field');
                const display = document.getElementById(field + '_display');
                const input = document.getElementById(field + '_input');

                if (!isEditing) {
                    // Chuyển sang chế độ chỉnh sửa
                    display.style.display = 'none';
                    input.style.display = 'block';
                    input.focus();
                    saveBtn.style.display = 'block';
                    isEditing = true;

                    // Đổi icon thành check
                    this.classList.remove('fa-pen-to-square');
                    this.classList.add('fa-check');
                    this.style.color = '#ffc107';
                }
            });
        });

        // Có thể thêm nút hủy nếu muốn
        saveBtn.addEventListener('click', function() {
            isEditing = false;
        });
    });
    </script>

</body>

</html>