<?php


session_start();
$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");
if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
}
echo '<pre>';
print_r($_SESSION);
print_r($_POST);

echo '</pre>';




$hoten = trim($_POST['hoten'] ?? '');
$sodt = trim($_POST['sodt'] ?? '');
$so_nha = trim($_POST['so_nha'] ?? '');
$diachi_full = trim($_POST['diachi'] ?? ''); // có thể là chuỗi tổng hợp từ client
$province_code = $_POST['province'] ?? '';
$district_code = $_POST['district'] ?? '';
$ward_name = $_POST['ward'] ?? ''; // frontend đang gửi tên xã/phường (theo api script của bạn)











function getLocationNameFromCode($endpoint) {
    // endpoint ví dụ: "p/01" hoặc "d/001" — trả về JSON
    $apiBase = "https://provinces.open-api.vn/api/";
    $url = $apiBase . $endpoint;
    // dùng @file_get_contents để tránh warning nếu lỗi; kiểm tra sau
    $json = @file_get_contents($url);
    if (!$json) return null;
    $data = json_decode($json, true);
    if (!$data) return null;

    return $data['name'] ?? null;
}

// Chuyển mã -> tên (nếu có)
$province_name = null;
$district_name = null;

if (!empty($province_code)) {
    $tmp = getLocationNameFromCode("p/" . urlencode($province_code));
    if ($tmp) $province_name = $tmp;
    else $province_name = $province_code; // fallback: giữ code nếu api fail
}

if (!empty($district_code)) {
    $tmp = getLocationNameFromCode("d/" . urlencode($district_code));
    if ($tmp) $district_name = $tmp;
    else $district_name = $district_code;
}

// Lưu dữ liệu tạm vào SESSION (dùng tên để hiển thị)
$_SESSION['temp_hoten'] = $hoten !== '' ? $hoten : ($_SESSION['temp_hoten'] ?? '');
$_SESSION['temp_sodt'] = $sodt !== '' ? $sodt : ($_SESSION['temp_sodt'] ?? '');
$_SESSION['temp_so_nha'] = $so_nha !== '' ? $so_nha : ($_SESSION['temp_so_nha'] ?? '');
$_SESSION['temp_province'] = $province_name ?? ($_SESSION['temp_province'] ?? '');
$_SESSION['temp_district'] = $district_name ?? ($_SESSION['temp_district'] ?? '');
$_SESSION['temp_ward'] = $ward_name !== '' ? $ward_name : ($_SESSION['temp_ward'] ?? '');
$_SESSION['temp_diachi'] =$_SESSION['temp_so_nha'].",".$_SESSION['temp_ward'].",".$_SESSION['temp_district'].",".$_SESSION['temp_province'] ;


// Kiểm tra thiếu thông tin (dùng để disable nút đặt hàng)
$thieuThongTin = empty($_SESSION['temp_hoten']) || empty($_SESSION['temp_sodt']) || empty($_SESSION['temp_so_nha']) || empty($_SESSION['temp_province'])|| empty($_SESSION['temp_district'])|| empty($_SESSION['temp_ward']);

// Lưu vào DB khi nhấn Lưu
$saved = false;
$updateMessage = '';

if (isset($_POST['save_address'])) {


    // Lưu tạm old_address2 (giữ codes để frontend có thể prefill select)
        $_SESSION['old_address2'] = [
            'province' => $province_code,
            'district' => $district_code,
            'ward' => $ward_name,
            'so_nha' => $so_nha,
        ];
    // Nếu user đăng nhập, cập nhật vào bảng khachhang
    if (isset($_SESSION['user_id'])) {
        

        

        // Chuẩn bị giá trị an toàn
        $hoten_safe = mysqli_real_escape_string($ketnoi, $_SESSION['temp_hoten']);
        $sodt_safe = mysqli_real_escape_string($ketnoi, $_SESSION['temp_sodt']);
        $diachi_safe = mysqli_real_escape_string($ketnoi, $_SESSION['temp_diachi']);
        $sonha_safe = mysqli_real_escape_string($ketnoi, $so_nha);
        $tinh_safe = mysqli_real_escape_string($ketnoi, $province_name ?? '');
        $huyen_safe = mysqli_real_escape_string($ketnoi, $district_name ?? '');
        $xaphuong_safe = mysqli_real_escape_string($ketnoi, $ward_name);

            // Lấy dữ liệu hiện tại từ DB
    $sql_check = "SELECT sonha, tinhthanhpho, huyenquan, xaphuong FROM khachhang WHERE MaKH = '$user_id' LIMIT 1";
    $result_check = mysqli_query($ketnoi, $sql_check);
    $row = mysqli_fetch_assoc($result_check);

    // Xác định xem địa chỉ hiện tại có rỗng không
    $hasAddress =
        !empty($row['sonha']) ||
        !empty($row['tinhthanhpho']) ||
        !empty($row['huyenquan']) ||
        !empty($row['xaphuong']);

    if (!$hasAddress) {
    $sql_update = "
        UPDATE khachhang SET
            sonha = '$sonha_safe',
            tinhthanhpho = '$tinh_safe',
            tinh_code = '$province_code',
            huyenquan = '$huyen_safe',
            huyen_code = '$district_code',
            xaphuong = '$xaphuong_safe'
        WHERE MaKH = '$user_id'
    ";

    if (mysqli_query($ketnoi, $sql_update)) {
        $saved = true;
        $updateMessage = 'Thông tin địa chỉ đã được lưu!';
    } else {
        $updateMessage = 'Lỗi khi lưu: ' . mysqli_error($ketnoi);
    }
    } 
    else {
    $updateMessage = 'Địa chỉ đã tồn tại, không cập nhật.';

}

}
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
                'size_id' => $item['MaSize'],
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
           $sql = "SELECT sp.TenSP, ss.Gia, ss.Anh, s.TenSize, s.MaSize
        FROM sanpham_size ss
        JOIN sanpham sp ON ss.MaSP = sp.MaSP
        JOIN size s ON ss.MaSize = s.MaSize
        WHERE ss.MaSP = {$item['masp']}
        AND ss.MaSize = {$item['size_id']}";
        $result_item=mysqli_query($ketnoi,$sql);
        $sanphamsize=mysqli_fetch_array($result_item);
            
            $subtotal = $sanphamsize['Gia'] * $item['quantity'];
            print_r($subtotal);
            $cartItems[] = [
                'masp' => $item['masp'],
                'size_id' => $item['size_id'],
                'tensp' => $sanphamsize['TenSP'],
                'tensize' => $sanphamsize['TenSize'],
                'price' => $sanphamsize['Gia'],
                'quantity' => $item['quantity'],
                'anh' => $sanphamsize['Anh'],
                'subtotal' => $subtotal,
            ];
            $tongtien += $subtotal;
        }
    }
}


mysqli_close($ketnoi);
?>
<?php if ($updateMessage): ?>
<div class="alert alert-<?php echo $saved ? 'success' : 'danger'; ?>">
    <?php echo $updateMessage; ?>
</div>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Giỏ hàng - Pizza</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="./css/animate.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" href="css/pizza.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/sign_up.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>

<body>
    <header class="bg-icon pt-5">
        <?php include 'components/navbar.php'; ?>


        <main class="container my-5">
            <h2 class="text-center mb-4">Giỏ hàng của bạn</h2>

            <?php if (empty($cartItems)): ?>
            <div class="text-center">
                <img src="./img/cart.png" alt="" style="width: 150px; height: 150px; object-fit: contain;">
                <h4>Giỏ hàng của bạn đang trống</h4>
                <p class="text-muted">Hãy thêm một số sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
                <a href="trangchu.php" class="btn btn-outline-success">Tiếp tục mua sắm</a>
            </div>




            <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Sản phẩm trong giỏ hàng (<?php echo count($cartItems); ?> sản phẩm)</h5>
                        </div>
                        <div class="card-body scrollable-menu" style="max-height: 500px; overflow-y: auto;">
                            <?php foreach ($cartItems as $item): ?>
                            <div class="row cart-item mb-3 pb-3 border-bottom align-items-center"
                                id="cart-item-<?php echo $item['masp']; ?>-<?php echo $item['size_id']; ?>">
                                <div class="col-md-2">
                                    <img src="<?php echo $item['anh']; ?>" class="img-fluid rounded"
                                        alt="<?php echo $item['tensp']; ?>">
                                </div>
                                <div class="col-md-4">
                                    <h6><?php echo $item['tensp']; ?></h6>
                                    <p class="text-muted mb-1">Size: <?php echo $item['tensize']; ?></p>
                                    <p class="text-success mb-0"><?php echo number_format($item['price']); ?> VNĐ</p>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <!-- ❌ XÓA CÁC THẺ <a> CŨ VÀ THAY BẰNG <button> -->
                                        <button type="button" class="btn btn-outline-secondary btn-sm btn-update-cart"
                                            data-masp="<?php echo $item['masp']; ?>"
                                            data-masize="<?php echo $item['size_id']; ?>" data-type="decrease">
                                            -
                                        </button>
                                        <input type="number"
                                            class="form-control form-control-sm text-center quantity-display"
                                            value="<?php echo $item['quantity']; ?>" min="1" readonly>
                                        <button type="button" class="btn btn-outline-secondary btn-sm btn-update-cart"
                                            data-masp="<?php echo $item['masp']; ?>"
                                            data-masize="<?php echo $item['size_id']; ?>" data-type="increase">
                                            +
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <p class="mb-0 subtotal-display">
                                        <strong><?php echo number_format($item['subtotal']); ?> VNĐ</strong>
                                    </p>
                                </div>
                                <div class="col-md-1">
                                    <!-- ❌ XÓA THẺ <a> CŨ VÀ THAY BẰNG <button> -->
                                    <button type="button" class="btn btn-danger btn-sm btn-delete-cart"
                                        data-masp="<?php echo $item['masp']; ?>"
                                        data-masize="<?php echo $item['size_id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

                            <form method="POST" action="">
                                <!-- Tên người nhận -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span style="min-width: 150px;">Tên người nhận</span>
                                    <span id="hoten_display" style="flex: 1; text-align: center;">
                                        <?php 
                                   if(isset($_SESSION['temp_hoten'])){
                                        echo htmlspecialchars($_SESSION['temp_hoten']);
                                    }
                                    else  {
                                        echo "<span class='text-danger'>Vui lòng nhập tên người nhận</span>";
                                    }
                                    ?>
                                    </span>
                                    <input type="text" name="hoten" id="hoten_input" class="form-control mx-2"
                                        value="<?php echo isset($_SESSION['temp_hoten']) ? htmlspecialchars($_SESSION['temp_hoten']) : ''; ?>"
                                        style="display: none; flex: 1;">
                                    <i class="fa-solid fa-pen-to-square edit-btn"
                                        style="color: #30d952; cursor: pointer;" data-field="hoten"></i>
                                </div>

                                <!-- Số điện thoại -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span style="min-width: 150px;">Số điện thoại</span>
                                    <span id="sodt_display" style="flex: 1; text-align: center;">
                                        <?php 
                                    if(isset($_SESSION['temp_sodt'])){
                                        echo htmlspecialchars($_SESSION['temp_sodt']);
                                    } else {
                                        echo "<span class='text-danger'>Vui lòng nhập số điện thoại</span>";
                                    }
                                    ?>
                                    </span>
                                    <input oninput="this.value = this.value.replace(/[^0-9]/g, '')" type="number"
                                        maxlenght='10' name="sodt" id="sodt_input" class="form-control mx-2"
                                        value="<?php echo isset($_SESSION['temp_sodt']) ? htmlspecialchars($_SESSION['temp_sodt']) : ''; ?>"
                                        style="display: none; flex: 1;">
                                    <i class="fa-solid fa-pen-to-square edit-btn"
                                        style="color: #30d952; cursor: pointer;" data-field="sodt"></i>
                                </div>

                                <!-- Địa chỉ -->
                                <div class="flex-column justify-content-between align-items-center mb-3">
                                    <span style="min-width: 150px;">Địa chỉ</span>
                                    <div id="address_container" style="flex: 1; text-align: center;">
                                        <div class="d-flex gap-2 mb-2">
                                            <select name="province" id="province" class="form-select" style="flex: 1;">
                                                <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                            </select>
                                            <select name="district" id="district" class="form-select" style="flex: 1;"
                                                disabled>
                                                <option value="">-- Chọn Quận/Huyện --</option>
                                            </select>
                                            <select name="ward" id="ward" class="form-select" style="flex: 1;" disabled>
                                                <option value="">-- Chọn Xã/Phường --</option>
                                            </select>
                                        </div>

                                        <input type="text" id="so_nha_input" name="so_nha"
                                            placeholder="Nhập số nhà, tên đường..." class="form-control mb-2"
                                            value="<?php echo isset($_SESSION['temp_so_nha']) ? $_SESSION['temp_so_nha'] : ''; ?>" />

                                        <input type="hidden" name="diachi" id="diachi_input"
                                            value="<?php echo isset($_SESSION['temp_diachi']) ? $_SESSION['temp_diachi'] : ''; ?>">

                                        <p id="full_address" class="text-muted mt-2">
                                            <?php echo isset($_SESSION['temp_diachi']) ? "🏠 " . $_SESSION['temp_diachi'] : ''; ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="save_address" id="saveBtn" class="btn btn-success"
                                        style="width:100%; <?php echo $saved ? 'display:none;' : ''; ?>">
                                        <i class="fa-solid fa-floppy-disk"></i> Lưu thông tin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <hr>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Tổng kết đơn hàng</h5>
                        </div>
                        <div class="card-body">
                            <form action="./cart/process_order.php" method="post">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tạm tính:</span>
                                    <span class="total-amount"><?php echo number_format($tongtien); ?> VNĐ</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Phí vận chuyển:</span>
                                    <span class="text-success">Miễn phí</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Tổng cộng:</strong>
                                    <strong class="text-danger total-amount"><?php echo number_format($tongtien); ?>
                                        VNĐ</strong>
                                </div>

                                <!-- Phương thức thanh toán -->
                                <div class="mb-3">
                                    <label class="form-label"><strong>Phương thức thanh toán:</strong></label>

                                    <!-- Tiền mặt -->
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cash"
                                            value="Tiền mặt" checked>
                                        <label class="form-check-label" for="cash">
                                            <i class="fas fa-money-bill-wave text-success"></i> Thanh toán tiền mặt khi
                                            nhận hàng (COD)
                                        </label>
                                    </div>

                                    <!-- Chuyển khoản -->
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="transfer"
                                            value="Chuyển khoản">
                                        <label class="form-check-label" for="transfer">
                                            <i class="fas fa-credit-card text-primary"></i> Chuyển khoản ngân hàng
                                        </label>
                                    </div>

                                    <!-- Tùy chọn chuyển khoản (ẩn mặc định) -->
                                    <div id="transfer-options" class="ms-4 mt-2" style="display: none;">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="transfer_method"
                                                id="momo" value="momo"  >
                                            <label class="form-check-label" for="momo">
                                                <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png"
                                                    alt="MoMo" style="height: 20px; vertical-align: middle;"> MoMo
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="transfer_method"
                                                id="vnpay" value="vnpay">
                                            <label class="form-check-label" for="vnpay">
                                                <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png"
                                                    alt="VNPay" style="height: 20px; vertical-align: middle;"> VNPay
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <button name="order" type="submit"
                                    class="btn btn-success w-100 mb-2 <?php echo $thieuThongTin && isset($_SESSION['user_id']) ? 'disabled' : ''; ?>"
                                    <?php echo $thieuThongTin && isset($_SESSION['user_id']) ? 'style="opacity:0.5; pointer-events:none;"' : ''; ?>>
                                    <i class="fas fa-shopping-bag"></i> Đặt hàng
                                </button>

                                <?php if(!isset($_SESSION['user_id'])): ?>
                                <button name="order_guest" type="submit"
                                    class="btn btn-warning order-guest w-100 mb-2 <?php echo $thieuThongTin ? 'disabled' : ''; ?>"
                                    <?php echo $thieuThongTin ? 'style="opacity:0.5; pointer-events:none;"' : ''; ?>>
                                    <i class="fas fa-shopping-bag"></i> Đặt hàng không cần đăng nhập
                                </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
           <!-- Modal chọn size -->
        <?php require "includes/modal_size.php" ?>
    </header>




    <?php include './components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
 
    <script>
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
                    display.style.display = 'none';
                    input.style.display = 'block';
                    input.focus();
                    saveBtn.style.display = 'block';
                    isEditing = true;

                    this.classList.remove('fa-pen-to-square');
                    this.classList.add('fa-check');
                    this.style.color = '#ffc107';
                } else {
                    display.textContent = input.value || (field === 'hoten' ?
                        'Vui lòng nhập tên người nhận' : 'Vui lòng nhập số điện thoại');

                    display.style.display = 'block';
                    input.style.display = 'none';
                    saveBtn.style.display = 'block';
                    isEditing = false;

                    this.classList.remove('fa-check');
                    this.classList.add('fa-pen-to-square');
                    this.style.color = '#30d952';
                }

            });
        });

        saveBtn.addEventListener('click', function() {
            isEditing = false;
        });
    });
    </script>

    <script>
    const oldAddress = <?php echo json_encode($_SESSION['old_address2'] ?? []); ?>;

    window.addEventListener('load', function() {
        if (oldAddress.province) {
            document.getElementById('province').value = oldAddress.province;
            const event = new Event('change');
            document.getElementById('province').dispatchEvent(event);

            if (oldAddress.district) {
                setTimeout(() => {
                    document.getElementById('district').value = oldAddress.district;
                    const eventDistrict = new Event('change');
                    document.getElementById('district').dispatchEvent(eventDistrict);

                    if (oldAddress.ward) {
                        setTimeout(() => {
                            document.getElementById('ward').value = oldAddress.ward;
                        }, 500);
                    }
                }, 500);
            }
        }
    });
    </script>
 <script>
// JavaScript để hiển thị/ẩn tùy chọn chuyển khoản
document.addEventListener('DOMContentLoaded', function() {
    const cashRadio = document.getElementById('cash');
    const transferRadio = document.getElementById('transfer');
    const transferOptions = document.getElementById('transfer-options');
    const momoRadio = document.getElementById('momo');
    const vnpayRadio = document.getElementById('vnpay');

    // Lắng nghe sự thay đổi phương thức thanh toán
    transferRadio.addEventListener('change', function() {
        if (this.checked) {
            transferOptions.style.display = 'block';
            // Tự động chọn MoMo làm mặc định
            momoRadio.checked = true;
        }
    });

    cashRadio.addEventListener('change', function() {
        if (this.checked) {
            transferOptions.style.display = 'none';
            // Bỏ chọn các phương thức chuyển khoản
            momoRadio.checked = false;
            vnpayRadio.checked = false;
        }
    });
});
</script>


  <script src="js/search.js"></script>
    <script src="js/add_to_cart.js"></script>
    <script src="./js/cart.js"></script>
    <script src="./API/api_address.js"></script>
 
</body>

</html>