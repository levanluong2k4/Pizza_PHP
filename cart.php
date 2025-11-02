<?php
session_start();
$ketnoi = mysqli_connect("localhost", "root", "", "php_pizza");
mysqli_set_charset($ketnoi, "utf8");
   $hoten = trim($_POST['hoten'] ?? '');
    $sodt = trim($_POST['sodt'] ?? '');
    $so_nha = trim($_POST['so_nha'] ?? '');
    $diachi_full = trim($_POST['diachi'] ?? '');
    $province_code = $_POST['province'] ?? '';
    $district_code = $_POST['district'] ?? '';
    $ward_name = $_POST['ward'] ?? '';


    $_SESSION['old_address'] = [
    'province' => $province_code,
    'district' => $district_code,
    'ward' => $ward_name,
    'so_nha' => $so_nha,
];
echo '<pre>';
print_r($_SESSION);
print_r($_POST);
echo '</pre>';

// Nếu có user đăng nhập → lấy thông tin mặc định
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql_us = "SELECT * FROM khachhang WHERE MaKH='$user_id'";
    $result_us = mysqli_query($ketnoi, $sql_us);
    $user = mysqli_fetch_assoc($result_us);
   
    if (empty($_SESSION['temp_hoten'])) $_SESSION['temp_hoten'] = $user['HoTen'];
    if (empty($_SESSION['temp_sodt'])) $_SESSION['temp_sodt'] = $user['SoDT'];
    if (empty($_SESSION['temp_diachi'])) $_SESSION['temp_diachi'] = $user['Diachi'];
}
$thieuThongTin = empty($hoten) || empty($sodt) || empty($so_nha) || empty($diachi_full);

// ✅ CẬP NHẬT: Lưu thông tin vào DATABASE ngay khi nhấn nút Lưu
$saved = false;
$updateMessage = '';

if (isset($_POST['save_address'])) {
 
 
    // Lưu vào SESSION
    $_SESSION['temp_hoten'] = $hoten;
    $_SESSION['temp_sodt'] = $sodt;
   $_SESSION['temp_so_nha'] = $so_nha;
   $_SESSION['temp_province'] = $province_code;
   $_SESSION['temp_district'] = $district_code;
   $_SESSION['temp_ward'] = $ward_name;
    $_SESSION['temp_diachi'] = $diachi_full;



    // ✅ CẬP NHẬT VÀO DATABASE nếu user đã đăng nhập
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Escape các giá trị để tránh SQL injection
        $hoten_safe = mysqli_real_escape_string($ketnoi, $hoten);
        $sodt_safe = mysqli_real_escape_string($ketnoi, $sodt);
        $diachi_safe = mysqli_real_escape_string($ketnoi, $diachi_full);

        $sql_update = "UPDATE khachhang SET 
                       HoTen = '$hoten_safe',
                       SoDT = '$sodt_safe',
                       Diachi = '$diachi_safe'
                       WHERE MaKH = '$user_id'";
        
        if (mysqli_query($ketnoi, $sql_update)) {
            $saved = true;
            $updateMessage = 'Thông tin đã được lưu vào hệ thống!';
        } else {
            $updateMessage = 'Lỗi khi lưu thông tin: ' . mysqli_error($ketnoi);
        }
    } else {
        $saved = true;
        $updateMessage = 'Thông tin đã được lưu tạm thời!';
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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="./css/animate.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" href="css/bai6.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/sign_up.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>

<body>
    <header class="bg-icon">
        <?php include 'navbar/navbar.php'; ?>
    </header>

    <!-- ✅ Hiển thị thông báo lưu thông tin -->
    <?php if(!empty($updateMessage)): ?>
    <div class="alert alert-success alert-dismissible fade show alert-cart" role="alert">
        <i class="fa fa-check-circle"></i> <?php echo $updateMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Hiển thị thông báo giỏ hàng -->
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
        <div class="text-center">
            <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
            <h4>Giỏ hàng của bạn đang trống</h4>
            <p class="text-muted">Hãy thêm một số sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
            <a href="trangchu.php" class="btn btn-success">Tiếp tục mua sắm</a>
        </div>

        <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sản phẩm trong giỏ hàng (<?php echo count($cartItems); ?> sản phẩm)</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="row cart-item mb-3 pb-3 border-bottom align-items-center">
                            <div class="col-md-2">
                                <img src="<?php echo $item['anh']; ?>" class="img-fluid rounded" alt="<?php echo $item['tensp']; ?>">
                            </div>
                            <div class="col-md-4">
                                <h6><?php echo $item['tensp']; ?></h6>
                                <p class="text-muted mb-1">Size: <?php echo $item['tensize']; ?></p>
                                <p class="text-success mb-0"><?php echo number_format($item['price']); ?> VNĐ</p>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <a href="./cart/update_cart.php?masp=<?php echo $item['masp']; ?>&masize=<?php echo $item['masize']; ?>&type=decrease" class="btn btn-outline-secondary btn-sm">-</a>
                                    <input type="number" class="form-control form-control-sm text-center" value="<?php echo $item['quantity']; ?>" min="1" readonly>
                                    <a href="./cart/update_cart.php?masp=<?php echo $item['masp']; ?>&masize=<?php echo $item['masize']; ?>&type=increase" class="btn btn-outline-secondary btn-sm">+</a>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <p class="mb-0"><strong><?php echo number_format($item['subtotal']); ?> VNĐ</strong></p>
                            </div>
                            <div class="col-md-1">
                                <a href="./cart/update_cart.php?masp=<?php echo $item['masp']; ?>&masize=<?php echo $item['masize']; ?>&type=delete" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
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
                      <form method="POST" action="">
                            <!-- Tên người nhận -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span style="min-width: 150px;">Tên người nhận</span>
                                <span id="hoten_display" style="flex: 1; text-align: center;">
                                    <?php 
                                    if(isset($_SESSION['temp_hoten'])){
                                        echo htmlspecialchars($_SESSION['temp_hoten']);
                                    } else {
                                        echo "<span class='text-danger'>Vui lòng nhập tên người nhận</span>";
                                    }
                                    ?>
                                </span>
                                <input type="text" name="hoten" id="hoten_input" class="form-control mx-2" value="<?php echo isset($_SESSION['temp_hoten']) ? htmlspecialchars($_SESSION['temp_hoten']) : ''; ?>" style="display: none; flex: 1;">
                                <i class="fa-solid fa-pen-to-square edit-btn" style="color: #30d952; cursor: pointer;" data-field="hoten"></i>
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
                                <input type="number" min='0' name="sodt" id="sodt_input" class="form-control mx-2" value="<?php echo isset($_SESSION['temp_sodt']) ? htmlspecialchars($_SESSION['temp_sodt']) : ''; ?>" style="display: none; flex: 1;">
                                <i class="fa-solid fa-pen-to-square edit-btn" style="color: #30d952; cursor: pointer;" data-field="sodt"></i>
                            </div>

                            <!-- Địa chỉ -->
                            <div class="flex-column justify-content-between align-items-center mb-3">
                                <span style="min-width: 150px;">Địa chỉ</span>
                                <div id="address_container" style="flex: 1; text-align: center;">
                                    <div class="d-flex gap-2 mb-2">
                                        <select name="province" id="province" class="form-select" style="flex: 1;">
                                            <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                        </select>
                                        <select name="district" id="district" class="form-select" style="flex: 1;" disabled>
                                            <option value="">-- Chọn Quận/Huyện --</option>
                                        </select>
                                        <select name="ward" id="ward" class="form-select" style="flex: 1;" disabled>
                                            <option value="">-- Chọn Xã/Phường --</option>
                                        </select>
                                    </div>

                                    <input type="text" id="so_nha_input" name="so_nha" placeholder="Nhập số nhà, tên đường..." class="form-control mb-2" value="<?php echo isset($_SESSION['temp_so_nha']) ? $_SESSION['temp_so_nha'] : ''; ?>" />
                                    
                                    <input type="hidden" name="diachi" id="diachi_input" value="<?php echo isset($_SESSION['temp_diachi']) ? $_SESSION['temp_diachi'] : ''; ?>">

                                    <p id="full_address" class="text-muted mt-2">
                                        <?php echo isset($_SESSION['temp_diachi']) ? "🏠 " . $_SESSION['temp_diachi'] : ''; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="submit" name="save_address" id="saveBtn" class="btn btn-success" style="width:100%; <?php echo $saved ? 'display:none;' : ''; ?>">
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
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <span><?php echo number_format($tongtien); ?> VNĐ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <span class="text-success">Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng cộng:</strong>
                            <strong class="text-danger"><?php echo number_format($tongtien); ?> VNĐ</strong>
                        </div>
                        
                        <a href="<?php echo $thieuThongTin && isset($_SESSION['user_id']) ? '#' : 'cart/process_order.php'; ?>" 
                           class="btn btn-success w-100 mb-2 <?php echo $thieuThongTin && isset($_SESSION['user_id']) ? 'disabled' : ''; ?>"
                           <?php echo $thieuThongTin && isset($_SESSION['user_id']) ? 'style="opacity:0.5; pointer-events:none;"' : ''; ?>>
                            <i class="fas fa-shopping-bag"></i> Đặt hàng
                        </a>

                        <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo $thieuThongTin ? '#' : 'cart/process_order.php'; ?>" 
                           class="btn btn-warning order-guest w-100 mb-2 <?php echo $thieuThongTin ? 'disabled' : ''; ?>"
                           <?php echo $thieuThongTin ? 'style="opacity:0.5; pointer-events:none;"' : ''; ?>>
                            <i class="fas fa-shopping-bag"></i> Đặt hàng không cần đăng nhập
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

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
                }
                else {
                    display.textContent = input.value || (field === 'hoten' ? 'Vui lòng nhập tên người nhận' : 'Vui lòng nhập số điện thoại');

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
const oldAddress = <?php echo json_encode($_SESSION['old_address'] ?? []); ?>;

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
    <script src="./API/api_address.js"></script>

</body>
</html>