<?php 
session_start();
require '../includes/db_connect.php';

$combo_id = $_GET['combo_id'] ?? $_SESSION['combo_order']['combo_id'] ?? null;
$loaidatban = $_GET['loaidatban'] ?? 'thuong'; // Mặc định là đặt bàn thường

$combo_info = null;
$combo_details = [];
$total_price = 0;
$final_price = 0;
$discount_amount = 0;

// Nếu là đặt bàn tiệc, lấy thông tin combo
if ($loaidatban == 'tiec' && $combo_id != '') {
    // ✅ Lấy thông tin combo
    $sql_combo = "SELECT * FROM combo WHERE MaCombo = ?";
    $stmt_combo = $ketnoi->prepare($sql_combo);
    $stmt_combo->bind_param("i", $combo_id);
    $stmt_combo->execute();
    $combo_info = $stmt_combo->get_result()->fetch_assoc();
    $stmt_combo->close();
    
    if (!$combo_info) {
        die("Combo không tồn tại!");
    }
    
    // ✅ TÍNH GIÁ TỪ DATABASE (KHÔNG DÙNG GIÁ TỪ URL)
    
    // Nếu người dùng đã thay đổi sản phẩm (có trong session)
    if (isset($_SESSION['combo_order']['items'])) {
        foreach ($_SESSION['combo_order']['items'] as $item) {
            // Lấy giá thực từ database
            $sql_price = "SELECT sp.TenSP, sp.Anh, s.TenSize, sps.Gia 
                         FROM sanpham_size sps
                         INNER JOIN sanpham sp ON sps.MaSP = sp.MaSP
                         INNER JOIN size s ON sps.MaSize = s.MaSize
                         WHERE sps.MaSP = ? AND sps.MaSize = ?";
            $stmt_price = $ketnoi->prepare($sql_price);
            $stmt_price->bind_param("ii", $item['masp'], $item['masize']);
            $stmt_price->execute();
            $result = $stmt_price->get_result()->fetch_assoc();
            
            if ($result) {
                $item_total = $result['Gia'] * $item['soluong'];
                $total_price += $item_total;
                
                // Lưu vào combo_details để hiển thị
                $combo_details[] = [
                    'MaSP' => $item['masp'],
                    'MaSize' => $item['masize'],
                    'TenSP' => $result['TenSP'],
                    'TenSize' => $result['TenSize'],
                    'Anh' => $result['Anh'],
                    'Gia' => $result['Gia'],
                    'SoLuong' => $item['soluong'],
                    'ThanhTien' => $item_total
                ];
            }
            $stmt_price->close();
        }
    } else {
        // Nếu KHÔNG thay đổi, lấy từ chi tiết combo gốc
        $sql_detail = "SELECT ct.*, sp.TenSP, sp.Anh, s.TenSize, sps.Gia
                       FROM chitietcombo ct
                       INNER JOIN sanpham sp ON ct.MaSP = sp.MaSP
                       INNER JOIN size s ON ct.MaSize = s.MaSize
                       INNER JOIN sanpham_size sps ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize
                       WHERE ct.MaCombo = ?";
        $stmt_detail = $ketnoi->prepare($sql_detail);
        $stmt_detail->bind_param("i", $combo_id);
        $stmt_detail->execute();
        $result_detail = $stmt_detail->get_result();
        
        while ($row = $result_detail->fetch_assoc()) {
            $item_total = $row['Gia'] * $row['SoLuong'];
            $total_price += $item_total;
            
            $row['ThanhTien'] = $item_total;
            $combo_details[] = $row;
        }
        $stmt_detail->close();
    }
    
    // ✅ ÁP DỤNG GIẢM GIÁ
    $discount_percent = $combo_info['giamgia'] ?? 0;
    $discount_amount = $total_price * ($discount_percent / 100);
    $final_price = $total_price - $discount_amount;
    
    // ✅ LƯU VÀO SESSION ĐỂ DÙNG CHO CÁC BƯỚC TIẾP THEO
    $_SESSION['order_summary'] = [
        'combo_id' => $combo_id,
        'total_price' => $total_price,
        'discount_percent' => $discount_percent,
        'discount_amount' => $discount_amount,
        'final_price' => $final_price,
        'combo_details' => $combo_details
    ];
}

// Lấy danh sách bàn/phòng - Ban đầu hiển thị tất cả
$ban_list = [];
$phong_list = [];

if ($loaidatban == 'thuong') {
    // Hiển thị tất cả bàn không bảo trì
    $sql_ban = "SELECT * FROM banan WHERE TrangThai != 'bao_tri' ORDER BY SoBan";
    $result_ban = $ketnoi->query($sql_ban);
    while ($row = $result_ban->fetch_assoc()) {
        $ban_list[] = $row;
    }
} else {
    // Hiển thị tất cả phòng không bảo trì
    $sql_phong = "SELECT * FROM phongtiec WHERE TrangThai != 'bao_tri' ORDER BY SoPhong";
    $result_phong = $ketnoi->query($sql_phong);
    while ($row = $result_phong->fetch_assoc()) {
        $phong_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Pizza</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- animate -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="./css/animate.css">
    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />

    <!-- slick -->
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
    <link rel="stylesheet" type="text/css" href="slick/slick-theme.css" />

    <!-- CSS -->
    <link rel="stylesheet" href="../css/pizza.css">
    <link rel="stylesheet" href="../css/basic.css">
    <link rel="stylesheet" href="../css/sign_up.css">
    <link rel="stylesheet" href="../css/datban.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <header class="bg-icon pt-2">
        <?php include '../components/navbar.php'; ?>
    </header>

    <div class="booking-container">
        <div class="booking-header">
            <h2>
                <i class="fas fa-calendar-check me-2"></i>
                <?php echo $loaidatban == 'tiec' ? 'Đặt Bàn Tiệc' : 'Đặt Bàn Thường'; ?>
            </h2>
        </div>

        <div class="booking-body">
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Có lỗi xảy ra!
                </h5>
                <hr>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php echo $_SESSION['error']; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form id="formDatBan" method="POST" action="../handlers/process_datban.php">
                 <input type="hidden" name="loaidatban" value="<?php echo $loaidatban; ?>">
    <input type="hidden" name="combo_id" value="<?php echo $combo_id; ?>">
    <input type="hidden" id="selectedTable" name="table_id" value="">
    
    <!-- ✅ THÊM HIDDEN INPUT ĐỂ GỬI GIÁ AN TOÀN -->
    <?php if ($loaidatban == 'tiec' && isset($final_price)): ?>
    <input type="hidden" name="final_price" value="<?php echo $final_price; ?>">
    <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
    <input type="hidden" name="discount_amount" value="<?php echo $discount_amount; ?>">
    <?php endif; ?>

    <?php if ($loaidatban == 'tiec' && $combo_info): ?>
    <!-- Hiển thị thông tin combo -->
    <div class="combo-summary">
        <h5 class="mb-3">
            <i class="fas fa-box-open me-2"></i>
            Combo đã chọn
        </h5>
        <?php if (!empty($combo_info['Anh'])): ?>
        <img src="../<?php echo $combo_info['Anh']; ?>" alt="<?php echo $combo_info['Tencombo']; ?>">
        <?php endif; ?>
        <h4><?php echo htmlspecialchars($combo_info['Tencombo']); ?></h4>
    
        <h5>Giá gốc: 
            <span class="text-decoration-line-through text-muted">
                <?php echo number_format($total_price, 0, ',', '.'); ?> VNĐ
            </span>
        </h5>
        <div class="combo-price">
            <i class="fas fa-tags me-2"></i>
            Chỉ còn: <strong class="text-danger">
                <?php echo number_format($final_price, 0, ',', '.'); ?> VNĐ
            </strong>
        </div>
        <?php if ($discount_amount > 0): ?>
        <div class="text-success mt-2">
            <i class="fas fa-gift me-2"></i>
            Tiết kiệm: <?php echo number_format($discount_amount, 0, ',', '.'); ?> VNĐ 
            (<?php echo $combo_info['giamgia']; ?>%)
        </div>
        <?php endif; ?>
        <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal"
            data-bs-target="#comboDetailsModal">
            <i class="fas fa-list me-2"></i>Xem chi tiết combo
        </button>
    </div>
    <?php endif; ?>

                <!-- Thông tin khách hàng -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-user me-2"></i>
                        Thông tin khách hàng
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Họ và tên <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" name="hoten" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Số điện thoại <span class="required">*</span>
                            </label>
                            <input type="tel" class="form-control" name="sdt" pattern="[0-9]{10}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Ngày đến <span class="required">*</span>
                            </label>
                            <input type="date" class="form-control" name="ngayden" id="ngayden" required>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Chọn ngày để xem bàn/phòng khả dụng
                            </small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Giờ đến <span class="required">*</span>
                            </label>
                            <select class="form-control" name="gioden" id="gioden" required>
                                <option value="">-- Chọn ngày trước --</option>
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Chỉ hiển thị giờ cách hiện tại ít nhất 10 tiếng
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Chọn bàn/phòng -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-chair me-2"></i>
                        <?php echo $loaidatban == 'tiec' ? 'Chọn phòng tiệc' : 'Chọn bàn'; ?>
                        <span class="required">*</span>
                    </div>

                    <div id="tableListContainer">
                        <?php if ($loaidatban == 'thuong'): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Vui lòng chọn ngày để xem danh sách bàn trống
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Vui lòng chọn ngày để xem danh sách phòng trống
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

             

                <!-- Ghi chú -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-comment me-2"></i>
                        Ghi chú:(miễn phí decor nếu đặt phòng tiệc vui lòng ghi rõ trong phần ghi chú) 
                    </div>

                    <textarea class="form-control" name="ghichu" rows="3"
                        placeholder="Yêu cầu đặc biệt (nếu có)..."></textarea>
                </div>
                <!-- Ghi chú -->
                        <?php if($loaidatban=="tiec"):
                            
                    ?>  
                       <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-comment me-2"></i>
                        Thanh toán trực tuyến
                    </div>

                    <div class=" d-flex justify-content-around align-items-center mb-2">
                       <div class="d-flex align-items-center">
                         <input class="form-check-input me-2" type="radio" name="transfer_method" id="momo" value="momo" checked>
                        <label class="form-check-label" for="momo">
                            <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo"
                                style="height: 50px; vertical-align: middle;">Thanh toán qua MoMo
                        </label>
                       </div>
                        <div class="d-flex align-items-center">
                            <input class="form-check-input me-2" type="radio" name="transfer_method" id="vnpay" value="vnpay">
                        <label class="form-check-label" for="vnpay">
                            <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png"
                                alt="VNPay" style="height: 50px; vertical-align: middle;">Thanh toán qua VNPay
                        </label>
                        </div>
                       
                    </div>
                   
                </div>
                    <?php endif; ?>     

                <!-- Nút submit -->
                <button type="submit" class="btn-submit">
                   
                   <?php if($loaidatban=="tiec"):
                       
                    ?>

                    Yêu cầu thanh toán trước <?php echo number_format($final_price, 0, ',', '.'); ?>VNĐ
                     <?php else:
                       
                        ?>
                         Đặt bàn ngay
                            <?php endif; ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Modal chi tiết combo -->
   <!-- Modal chi tiết combo -->
<?php if ($loaidatban == 'tiec' && $combo_info): ?>
<div class="modal fade" id="comboDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="top: 50px;">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-list me-2"></i>
                    Chi tiết combo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php foreach ($combo_details as $item): ?>
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <img src="../<?php echo $item['Anh']; ?>"
                        style="width: 80px; height: 80px; object-fit: contain; border-radius: 8px;" class="me-3">
                    <div class="flex-grow-1">
                        <h6 class="mb-1"><?php echo $item['TenSP']; ?></h6>
                        <small class="text-muted">Size: <?php echo $item['TenSize']; ?></small>
                        <div class="mt-1">
                            Số lượng: <strong><?php echo $item['SoLuong']; ?></strong>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted"><?php echo number_format($item['Gia'], 0, ',', '.'); ?> VNĐ/món</div>
                        <strong class="text-success">
                            <?php echo number_format($item['ThanhTien'], 0, ',', '.'); ?> VNĐ
                        </strong>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="text-end mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Tổng giá trị sản phẩm:</h5>
                        <h5 class="mb-0">
                            <span class="text-decoration-line-through text-muted">
                                <?php echo number_format($total_price, 0, ',', '.'); ?> VNĐ
                            </span>
                        </h5>
                    </div>
                    
                    <?php if ($discount_amount > 0): ?>
                    <div class="d-flex justify-content-between align-items-center text-success mb-2">
                        <span>Giảm giá (<?php echo $combo_info['giamgia']; ?>%):</span>
                        <span>-<?php echo number_format($discount_amount, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Tổng thanh toán:</h4>
                        <h4 class="mb-0 text-danger">
                            <?php echo number_format($final_price, 0, ',', '.'); ?> VNĐ
                        </h4>
                    </div>
                    
                    <?php if ($discount_amount > 0): ?>
                    <div class="alert alert-success mt-3 mb-0">
                        <i class="fas fa-gift me-2"></i>
                        Bạn tiết kiệm được: <strong><?php echo number_format($discount_amount, 0, ',', '.'); ?> VNĐ</strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

    <?php include '../components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        const loaidatban = '<?php echo $loaidatban; ?>';

        // Set ngày tối thiểu là hôm nay
        const today = new Date().toISOString().split('T')[0];
        $('#ngayden').attr('min', today);

        // Tạo danh sách tất cả các khung giờ
        const allTimeSlots = [
            '10:00', '10:30', '11:00', '11:30', '12:00', '12:30',
            '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
            '16:00', '16:30', '17:00', '17:30', '18:00', '18:30',
            '19:00', '19:30', '20:00'
        ];

        // Hàm tính giờ tối thiểu có thể đặt
        function getMinBookingDateTime() {
            const now = new Date();
            return new Date(now.getTime() + (10 * 60 * 60 * 1000)); // Thêm 10 tiếng
        }

        // Hàm cập nhật danh sách giờ theo ngày được chọn
        function updateTimeSlots() {
            const selectedDate = $('#ngayden').val();
            if (!selectedDate) {
                $('#gioden').html('<option value="">-- Chọn ngày trước --</option>');
                return;
            }

            const minDateTime = getMinBookingDateTime();

            $('#gioden').html('<option value="">-- Chọn giờ --</option>');

            allTimeSlots.forEach(function(timeSlot) {
                const [hours, minutes] = timeSlot.split(':');
                const slotDateTime = new Date(selectedDate);
                slotDateTime.setHours(parseInt(hours), parseInt(minutes), 0, 0);

                // Chỉ thêm option nếu thời gian >= minDateTime
                if (slotDateTime >= minDateTime) {
                    $('#gioden').append(`<option value="${timeSlot}">${timeSlot}</option>`);
                }
            });

            // Kiểm tra nếu không có slot nào available
            if ($('#gioden option').length === 1) {
                $('#gioden').html('<option value="">-- Không có giờ khả dụng cho ngày này --</option>');
                alert('Ngày này không còn khung giờ khả dụng. Vui lòng chọn ngày khác!');
            }
        }

        // Khi thay đổi ngày, cập nhật danh sách giờ VÀ danh sách bàn/phòng
        $('#ngayden').on('change', function() {
            updateTimeSlots();
            $('#gioden').val(''); // Reset giờ đã chọn
            $('#selectedTable').val(''); // Reset bàn đã chọn

            // Reload danh sách bàn/phòng theo ngày
            const selectedDate = $(this).val();
            if (selectedDate) {
                loadAvailableTables(selectedDate);
            }
        });

        // Hàm load danh sách bàn/phòng trống qua AJAX
        function loadAvailableTables(date) {
            $('#tableListContainer').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Đang tải danh sách...</p>
                </div>
            `);

            $.ajax({
                url: '../handlers/get_available_tables.php',
                type: 'GET',
                data: {
                    ngayden: date,
                    loaidatban: loaidatban
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderTableList(response.data);
                    } else {
                        $('#tableListContainer').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                ${response.message}
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    $('#tableListContainer').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Lỗi khi tải dữ liệu: ${error}
                        </div>
                    `);
                }
            });
        }

        // Hàm render danh sách bàn/phòng
        function renderTableList(data) {
            if (data.length === 0) {
                const message = loaidatban === 'thuong' ?
                    'Không có bàn trống cho ngày này. Vui lòng chọn ngày khác.' :
                    'Không có phòng trống cho ngày này. Vui lòng chọn ngày khác.';

                $('#tableListContainer').html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${message}
                    </div>
                `);
                return;
            }

            let html = '<div class="table-selection">';

            if (loaidatban === 'thuong') {
                // Render bàn thường
                data.forEach(function(ban) {
                    html += `
                        <div class="table-item" data-id="${ban.MaBan}">
                            <i class="fas fa-chair fa-2x mb-2"></i>
                            <div>Bàn ${ban.SoBan}</div>
                            <small>${ban.SoGhe} ghế</small>
                            <div class="mt-1"><small class="text-muted">${ban.KhuVuc}</small></div>
                        </div>
                    `;
                });
            } else {
                // Render phòng tiệc
                data.forEach(function(phong) {
                    html += `
                        <div class="room-item" data-id="${phong.MaPhong}" data-succhua="${phong.SucChua}">
                            <i class="fas fa-door-open fa-2x mb-2"></i>
                            <div>${phong.TenPhong}</div>
                            <small>Sức chứa: ${phong.SucChua} người</small>
                        </div>
                    `;
                });
            }

            html += '</div>';
            $('#tableListContainer').html(html);

            // Bind sự kiện click cho các item
            $('.table-item, .room-item').on('click', function() {
                $('.table-item, .room-item').removeClass('selected');
                $(this).addClass('selected');
                $('#selectedTable').val($(this).data('id'));
            });
        }

        // Validate khi chọn giờ
        $('#gioden').on('change', function() {
            validateDateTime();
        });

        function validateDateTime() {
            const selectedDate = $('#ngayden').val();
            const selectedTime = $('#gioden').val();

            if (!selectedDate || !selectedTime) return true;

            const selectedDateTime = new Date(selectedDate + ' ' + selectedTime);
            const minDateTime = getMinBookingDateTime();

            if (selectedDateTime < minDateTime) {
                alert('Vui lòng chọn thời gian cách hiện tại ít nhất 10 tiếng!');
                $('#gioden').val('');
                return false;
            }

            const hour = selectedDateTime.getHours();
            if (hour < 10 || hour > 20) {
                alert('Giờ đến phải trong khung 10:00 - 20:00!');
                $('#gioden').val('');
                return false;
            }

            return true;
        }

        // Validate form trước khi submit
        $('#formDatBan').on('submit', function(e) {
            if (!$('#ngayden').val()) {
                e.preventDefault();
                alert('Vui lòng chọn ngày đến!');
                return false;
            }

            if (!$('#gioden').val()) {
                e.preventDefault();
                alert('Vui lòng chọn giờ đến!');
                return false;
            }

            if ($('#selectedTable').val() === '') {
                e.preventDefault();
                alert('Vui lòng chọn <?php echo $loaidatban == "tiec" ? "phòng" : "bàn"; ?>!');
                return false;
            }

            if (!validateDateTime()) {
                e.preventDefault();
                return false;
            }

            return true;
        });

        // Khởi tạo: disable dropdown giờ cho đến khi chọn ngày
        $('#gioden').html('<option value="">-- Chọn ngày trước --</option>');
    });
    </script>

</body>

</html>