<?php 
session_start(); 

require "includes/db_connect.php";

// Kiểm tra đăng nhập
if(!isset($_SESSION['user_id'])&&$_SESSION['role']=='user'){
    header("Location: sign_in.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// echo '<pre>';
// print_r($_SESSION);
// print_r($_POST);

// echo '</pre>';
// Lấy thông tin người dùng
$sql_user = "SELECT * FROM `khachhang` WHERE MaKH='$user_id'";
$result_user = mysqli_query($ketnoi, $sql_user);
$user = mysqli_fetch_array($result_user);

    // Lưu thông tin địa chỉ (NAME để hiển thị)
        $_SESSION['temp_ward'] = $user['xaphuong'];
        $_SESSION['temp_district'] = $user['huyenquan'];
        $_SESSION['temp_province'] = $user['tinhthanhpho'];
        $_SESSION['temp_so_nha'] = $user['sonha'];
        $_SESSION['temp_sodt'] = $user['SoDT'];
        $_SESSION['temp_hoten'] = $user['HoTen'];
        $_SESSION['temp_diachi'] =$user['sonha'].",".$user['xaphuong'].",".$user['huyenquan'].",".$user['tinhthanhpho'] ;
        
        // Lưu CODE để prefill select (nếu có cột mới)
        $_SESSION['old_address2'] = [
            'province' => $user['tinh_code'] ?? '', // ⬅️ CODE
            'district' => $user['huyen_code'] ?? '', // ⬅️ CODE
            'ward' => $user['xaphuong'], // ⬅️ NAME (ward không có code)
            'so_nha' => $user['sonha'],
        ];




$message = '';
$message_type = '';

$hoten = trim($_POST['hoten'] ?? '');
$sodt = trim($_POST['sodt'] ?? '');
$so_nha = trim($_POST['so_nha'] ?? '');
$diachi_full = trim($_POST['diachi'] ?? ''); // có thể là chuỗi tổng hợp từ client
$province_code = $_POST['province'] ?? '';
$district_code = $_POST['district'] ?? '';
$ward_name = $_POST['ward'] ?? ''; 










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
// Nếu client gửi chuỗi diachi (hidden), ưu tiên dùng chuỗi đó; nếu rỗng, ghép từ các phần
if (!empty($diachi_full)) {
    $_SESSION['temp_diachi'] = $diachi_full;
} else {
    $parts = array_filter([$so_nha, $ward_name, $district_name, $province_name]);
    $_SESSION['temp_diachi'] = implode(', ', $parts);
}

// Kiểm tra thiếu thông tin (dùng để disable nút đặt hàng)
$thieuThongTin = empty($_SESSION['temp_hoten']) || empty($_SESSION['temp_sodt']) || empty($_SESSION['temp_so_nha']) || empty($_SESSION['temp_province'])|| empty($_SESSION['temp_district'])|| empty($_SESSION['temp_ward']);

// Lưu vào DB khi nhấn Lưu
$saved = false;
$updateMessage = '';

if (isset($_POST['save_address'])) {


    // Lưu tạm old_address (giữ codes để frontend có thể prefill select)
        $_SESSION['old_address'] = [
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

    
    $sql_update = "
        UPDATE khachhang SET
            HoTen='$hoten_safe',
            SoDT='$sodt_safe',
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

            // ✅ QUAN TRỌNG: Query lại dữ liệu mới từ database
            $sql_user = "SELECT * FROM `khachhang` WHERE MaKH='$user_id'";
            $result_user = mysqli_query($ketnoi, $sql_user);
            $user = mysqli_fetch_array($result_user);

     
        
        

    } else {
        $updateMessage = 'Lỗi khi lưu: ' . mysqli_error($ketnoi);
    }
  

}

}



// Xử lý đổi mật khẩu
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])){
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Lấy mật khẩu hiện tại từ database
    $sql_check = "SELECT MatKhau FROM khachhang WHERE MaKH = '$user_id'";
    $result_check = mysqli_query($ketnoi, $sql_check);
    $user_check = mysqli_fetch_assoc($result_check);
    
    // Kiểm tra mật khẩu cũ bằng password_verify
    if(password_verify($old_password, $user_check['MatKhau'])){
        if($new_password == $confirm_password){
            if(strlen($new_password) >= 6){
                // Mã hóa mật khẩu mới bằng password_hash
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update_pass = "UPDATE khachhang SET MatKhau = '$hashed_password' WHERE MaKH = '$user_id'";
                
                if(mysqli_query($ketnoi, $sql_update_pass)){
                    $message = 'Đổi mật khẩu thành công!';
                    $message_type = 'success';
                } else {
                    $message = 'Có lỗi xảy ra. Vui lòng thử lại!';
                    $message_type = 'danger';
                }
            } else {
                $message = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
                $message_type = 'danger';
            }
        } else {
            $message = 'Mật khẩu mới không khớp!';
            $message_type = 'danger';
        }
    } else {
        $message = 'Mật khẩu cũ không đúng!';
        $message_type = 'danger';
    }
}


?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Thông tin tài khoản - Pizza</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/pizza.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/info_user.css">


</head>

<body>
    <header class="bg-icon">
        <?php include 'components/navbar.php'; ?>
    </header>

    <section class="profile-section bg-icon">
        <div class="container">
           <?php if($message): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert" id="autoCloseAlert">
    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

            <script>
                // Tự động đóng sau 3 giây
                setTimeout(function() {
                    var alertElement = document.getElementById('autoCloseAlert');
                    if(alertElement) {
                        // Sử dụng Bootstrap's fade out
                        var bsAlert = new bootstrap.Alert(alertElement);
                        bsAlert.close();
                    }
                }, 3000);
            </script>
            <?php endif; ?>

            <div class=" profile-card ">
               

                <div class=" profile-body">
                     <div>
                    
                    <h3 class="mb-1"><i class="fas fa-user"></i>  <?php echo htmlspecialchars($user['HoTen']); ?></h3>
                    <p class="mb-0"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['Email']); ?></p>
                        </div>
                    
                    <ul class="nav nav-tabs mb-4 justify-content-center" role="tablist">
                       
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info" type="button">
                                <i class="fas fa-info-circle"></i> Thông tin cá nhân
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#edit" type="button">
                                <i class="fas fa-edit"></i> Chỉnh sửa thông tin
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#password" type="button">
                                <i class="fas fa-key"></i> Đổi mật khẩu
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#email" type="button">
                                <i class="fas fa-envelope"></i> Đổi Email
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Tab xem thông tin -->
                        <div class="tab-pane fade show active " id="info" role="tabpanel" style="padding-left: 100px;">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-user"></i> Họ và tên:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['HoTen']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-envelope"></i> Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['Email']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-phone"></i> Số điện thoại:</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['SoDT']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-map-marker-alt"></i> Địa chỉ:</span>
                                <span class="info-value">
                                    <?php 
                                    $address_parts = array_filter([
                                        $user['sonha'],
                                        $user['xaphuong'],
                                        $user['huyenquan'],
                                        $user['tinhthanhpho']
                                    ]);
                                    echo htmlspecialchars(implode(', ', $address_parts)) ?: 'Chưa cập nhật';
                                    ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar"></i> Ngày tạo tài khoản:</span>
                                <span
                                    class="info-value"><?php echo date('d/m/Y', strtotime($user['ngaytao'])); ?></span>
                            </div>
                        </div>

                        <!-- Tab chỉnh sửa thông tin -->
                        <div class="tab-pane fade" id="edit" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thông tin người khách hàng</h5>
                                </div>
                                <div class="card-body">

                                    <form method="POST" action="">
                                        <!-- Tên người nhận -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span style="min-width: 150px;">Tên khách hàng</span>
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
                                            <input oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                type="number" maxlenght='10' name="sodt" id="sodt_input"
                                                class="form-control mx-2"
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
                                                    <select name="province" id="province" class="form-select"
                                                        style="flex: 1;">
                                                        <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                                    </select>
                                                    <select name="district" id="district" class="form-select"
                                                        style="flex: 1;" disabled>
                                                        <option value="">-- Chọn Quận/Huyện --</option>
                                                    </select>
                                                    <select name="ward" id="ward" class="form-select" style="flex: 1;"
                                                        disabled>
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
                                            <button type="submit" name="save_address" id="saveBtn"
                                                class="btn btn-success"
                                                style="width:100%; <?php echo $saved ? 'display:none;' : ''; ?>">
                                                <i class="fa-solid fa-floppy-disk"></i> Lưu thông tin
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>

                        <!-- Tab đổi mật khẩu -->
                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <form method="POST" action="">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">Mật khẩu cũ *</label>
                                            <input type="password" class="form-control" name="old_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mật khẩu mới * (tối thiểu 6 ký tự)</label>
                                            <input type="password" class="form-control" name="new_password"
                                                minlength="6" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Xác nhận mật khẩu mới *</label>
                                            <input type="password" class="form-control" name="confirm_password"
                                                minlength="6" required>
                                        </div>
                                        <div class="text-center mt-4">
                                            <button type="submit" name="change_password" class="btn btn-save">
                                                <i class="fas fa-key"></i> Đổi mật khẩu
                                            </button>
                                        
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="email" role="tabpanel">
                            <form method="POST" id="emailForm">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">

                                         <div class="mb-3">
                                            <label class="form-label">Email cũ *</label>
                                      
                                            <input type="email" class="form-control" style="background-color:#b7b7b7" id="old_email" name="old_email"
                                                value="<?php echo $user["Email"] ?>" readonly
                                                placeholder="example@gmail.com" >
                                         
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Email mới *</label>
                                      
                                            <input type="email" class="form-control" id="new_email" name="email"
                                                placeholder="example@gmail.com" required>
                                            <small id="error-new-email" class="text-danger"
                                                style="font-size: 0.8em;"></small>
                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="button" id="change_email_btn" class="btn btn-save">
                                                <i class="fas fa-envelope"></i> Đổi email
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
 


    <?php include 'components/footer.php'; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

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


<script>
 

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
    
$(document).ready(function() {


    





    // ✅ Đổi selector thành #new_email
    $('#change_email_btn').on('click', function(e) {
        e.preventDefault();

        const email = $('#new_email').val().trim();
        const btn = $(this);
        
        // Clear error
        $('#error-new-email').text('');
        
        // Validation phía client
        if (!email) {
            $('#error-new-email').text('Vui lòng nhập email!');
            return;
        }
        
        // Kiểm tra format email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            $('#error-new-email').text('Email không đúng định dạng!');
            return;
        }

        // Disable button khi đang gửi
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang gửi...');

        $.ajax({
            url: 'handlers/change_email.php',
            method: 'POST',
            data: { email: email },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = 'handlers/verify_change_email.php';
                } else {
                    if (response.error_type === 'email_format' || response.error_type === 'email') {
                        $('#error-new-email').text(response.message);
                    } else {
                        alert(response.message);
                    }
                    btn.prop('disabled', false).html('<i class="fas fa-envelope"></i> Đổi email');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Lỗi hệ thống hoặc mạng. Vui lòng thử lại!');
                btn.prop('disabled', false).html('<i class="fas fa-envelope"></i> Đổi email');
            }
        });
    });

    // Validation khi người dùng nhập
    $('#new_email').on('input', function() {
        $('#error-new-email').text('');
    });


// 3. LOAD ĐỊA CHỈ CŨ (nếu có)

  


});
</script>


  


    <script src="./API/address_selector.js"></script>


</body>

</html>