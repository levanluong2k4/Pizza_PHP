<?php
session_start();
require __DIR__ . '/../../includes/db_connect.php';

// if (!isset($_SESSION['admin'])) {
//     header('Location: ../login.php');
//     exit;
// }

$madatban = intval($_GET['id'] ?? 0);

if ($madatban <= 0) {
    $_SESSION['error'] = 'Mã đặt bàn không hợp lệ';
    header('Location: quan_ly_datban.php');
    exit;
}

// Lấy thông tin đặt bàn
$sql = "SELECT db.*, 
        ba.SoBan, ba.SoGhe, ba.KhuVuc,
        pt.TenPhong, pt.SucChua,
        c.Tencombo
        FROM datban db
        LEFT JOIN banan ba ON db.MaBan = ba.MaBan
        LEFT JOIN phongtiec pt ON db.MaPhong = pt.MaPhong
        LEFT JOIN combo c ON db.MaCombo = c.MaCombo
        WHERE db.MaDatBan = ?";

$stmt = $ketnoi->prepare($sql);
$stmt->bind_param("i", $madatban);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    $_SESSION['error'] = 'Không tìm thấy đơn đặt bàn';
    header('Location: quan_ly_datban.php');
    exit;
}

// Xác định loại đặt bàn hiện tại
$current_type = $booking['LoaiDatBan'];

// Lấy danh sách bàn
$sql_ban = "SELECT * FROM banan WHERE TrangThai != 'bao_tri' ORDER BY SoBan";
$ban_list = $ketnoi->query($sql_ban)->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách phòng
$sql_phong = "SELECT * FROM phongtiec WHERE TrangThai != 'bao_tri' ORDER BY SoPhong";
$phong_list = $ketnoi->query($sql_phong)->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách combo
$sql_combo = "SELECT * FROM combo ORDER BY Tencombo";
$combo_list = $ketnoi->query($sql_combo)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa đặt bàn - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
        }
        
        .edit-container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .edit-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
        }
        
        .edit-body {
            padding: 40px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .type-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .type-option {
            border: 3px solid #ddd;
            border-radius: 15px;
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .type-option:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .type-option.active {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .type-option input[type="radio"] {
            display: none;
        }
        
        .type-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #667eea;
        }
        
        .table-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
        }
        
        .table-option {
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 15px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .table-option:hover {
            border-color: #667eea;
            transform: translateY(-3px);
        }
        
        .table-option.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .table-option.unavailable {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8f9fa;
        }
        
        .table-option input[type="radio"] {
            display: none;
        }
        
        .alert-info {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            border-radius: 8px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>

<div class="edit-container">
    <div class="edit-header">
        <h2>
            <i class="fas fa-edit me-3"></i>
            Chỉnh sửa đặt bàn #<?php echo $madatban; ?>
        </h2>
        <p class="mb-0">Cập nhật thông tin đặt bàn</p>
    </div>
    
    <div class="edit-body">
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <form id="formEditBooking" method="POST" action="../process/process_edit_booking.php">
            <input type="hidden" name="madatban" value="<?php echo $madatban; ?>">
            
            <!-- Thông tin khách hàng -->
            <div class="section-title">
                <i class="fas fa-user me-2"></i>
                Thông tin khách hàng
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="hoten" 
                           value="<?php echo htmlspecialchars($booking['HoTen']); ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="sdt" 
                           value="<?php echo htmlspecialchars($booking['SDT']); ?>" 
                           pattern="[0-9]{10}" required>
                </div>
            </div>
            
            <!-- Chọn loại đặt bàn -->
            <div class="section-title">
                <i class="fas fa-layer-group me-2"></i>
                Loại đặt bàn
            </div>
            
            <div class="type-selector">
                <label class="type-option <?php echo $current_type == 'thuong' ? 'active' : ''; ?>">
                    <input type="radio" name="loaidatban" value="thuong" 
                           <?php echo $current_type == 'thuong' ? 'checked' : ''; ?>>
                    <div class="type-icon"><i class="fas fa-chair"></i></div>
                    <h5>Bàn thường</h5>
                    <p class="text-muted mb-0">Không chọn combo</p>
                </label>
                
                <label class="type-option <?php echo $current_type == 'tiec' ? 'active' : ''; ?>">
                    <input type="radio" name="loaidatban" value="tiec" 
                           <?php echo $current_type == 'tiec' ? 'checked' : ''; ?>>
                    <div class="type-icon"><i class="fas fa-door-open"></i></div>
                    <h5>Bàn tiệc</h5>
                    <p class="text-muted mb-0">Có combo</p>
                </label>
            </div>
            
            <!-- Ngày giờ -->
            <div class="section-title">
                <i class="fas fa-calendar-alt me-2"></i>
                Thời gian
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày đến <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="ngayden" id="ngayden"
                           value="<?php echo date('Y-m-d', strtotime($booking['NgayGio'])); ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giờ đến <span class="text-danger">*</span></label>
                    <select class="form-control" name="gioden" id="gioden" required>
                        <?php
                        $current_time = date('H:i', strtotime($booking['NgayGio']));
                        $time_slots = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30',
                                      '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
                                      '16:00', '16:30', '17:00', '17:30', '18:00', '18:30',
                                      '19:00', '19:30', '20:00'];
                        foreach ($time_slots as $slot) {
                            $selected = ($slot == $current_time) ? 'selected' : '';
                            echo "<option value='$slot' $selected>$slot</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <!-- Chọn bàn (hiện khi chọn bàn thường) -->
            <div id="banSection" style="display: <?php echo $current_type == 'thuong' ? 'block' : 'none'; ?>;">
                <div class="section-title">
                    <i class="fas fa-chair me-2"></i>
                    Chọn bàn <span class="text-danger">*</span>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Bàn hiện tại: <strong>Bàn <?php echo $booking['SoBan']; ?></strong>
                </div>
                
                <div class="table-grid" id="banGrid">
                    <?php foreach ($ban_list as $ban): 
                        $is_current = ($booking['MaBan'] == $ban['MaBan']);
                        $is_selected = $is_current ? 'selected' : '';
                    ?>
                    <label class="table-option <?php echo $is_selected; ?>" data-type="ban" data-id="<?php echo $ban['MaBan']; ?>">
                        <input type="radio" name="table_id" value="ban_<?php echo $ban['MaBan']; ?>" 
                               <?php echo $is_current ? 'checked' : ''; ?>>
                        <div><i class="fas fa-chair fa-2x mb-2"></i></div>
                        <div><strong>Bàn <?php echo $ban['SoBan']; ?></strong></div>
                        <small><?php echo $ban['SoGhe']; ?> ghế</small>
                        <div><small class="text-muted"><?php echo $ban['KhuVuc']; ?></small></div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Chọn phòng (hiện khi chọn bàn tiệc) -->
            <div id="phongSection" style="display: <?php echo $current_type == 'tiec' ? 'block' : 'none'; ?>;">
                <div class="section-title">
                    <i class="fas fa-door-open me-2"></i>
                    Chọn phòng <span class="text-danger">*</span>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Phòng hiện tại: <strong><?php echo $booking['TenPhong'] ?? 'Chưa chọn'; ?></strong>
                </div>
                
                <div class="table-grid" id="phongGrid">
                    <?php foreach ($phong_list as $phong): 
                        $is_current = ($booking['MaPhong'] == $phong['MaPhong']);
                        $is_selected = $is_current ? 'selected' : '';
                    ?>
                    <label class="table-option <?php echo $is_selected; ?>" data-type="phong" data-id="<?php echo $phong['MaPhong']; ?>">
                        <input type="radio" name="table_id" value="phong_<?php echo $phong['MaPhong']; ?>" 
                               <?php echo $is_current ? 'checked' : ''; ?>>
                        <div><i class="fas fa-door-open fa-2x mb-2"></i></div>
                        <div><strong><?php echo $phong['TenPhong']; ?></strong></div>
                        <small><?php echo $phong['SucChua']; ?> người</small>
                    </label>
                    <?php endforeach; ?>
                </div>
                
                <!-- Chọn combo -->
                <div class="mt-4">
                    <div class="section-title">
                        <i class="fas fa-box-open me-2"></i>
                        Chọn combo (tùy chọn)
                    </div>
                    
                    <select class="form-control" name="combo_id" id="comboSelect">
                        <option value="">-- Không chọn combo --</option>
                        <?php foreach ($combo_list as $combo): ?>
                        <option value="<?php echo $combo['MaCombo']; ?>" 
                                <?php echo ($booking['MaCombo'] == $combo['MaCombo']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($combo['Tencombo']); ?> 
                            (Giảm <?php echo $combo['giamgia']; ?>%)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Tiền cọc -->
                <div class="mt-3">
                    <label class="form-label">Tiền cọc (VNĐ)</label>
                    <input type="number" class="form-control" name="tiencoc" min="0" 
                           value="<?php echo $booking['TienCoc'] ?? 0; ?>">
                </div>
            </div>
            
            <!-- Ghi chú -->
            <div class="mt-4">
                <div class="section-title">
                    <i class="fas fa-comment me-2"></i>
                    Ghi chú
                </div>
                
                <textarea class="form-control" name="ghichu" rows="3"><?php echo htmlspecialchars($booking['GhiChu'] ?? ''); ?></textarea>
            </div>
            
            <!-- Nút action -->
            <div class="mt-4 d-flex gap-3">
                <button type="submit" class="btn btn-submit flex-grow-1">
                    <i class="fas fa-save me-2"></i>
                    Lưu thay đổi
                </button>
                <a href="quan_ly_datban.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Xử lý chuyển đổi loại đặt bàn
    $('input[name="loaidatban"]').on('change', function() {
        const type = $(this).val();
        
        $('.type-option').removeClass('active');
        $(this).closest('.type-option').addClass('active');
        
        if (type === 'thuong') {
            $('#banSection').show();
            $('#phongSection').hide();
            // Uncheck phòng
            $('#phongGrid input[type="radio"]').prop('checked', false);
        } else {
            $('#banSection').hide();
            $('#phongSection').show();
            // Uncheck bàn
            $('#banGrid input[type="radio"]').prop('checked', false);
        }
    });
    
    // Xử lý click chọn bàn/phòng
    $('.table-option').on('click', function() {
        const type = $(this).data('type');
        
        // Bỏ chọn tất cả cùng loại
        $(`.table-option[data-type="${type}"]`).removeClass('selected');
        
        // Chọn cái hiện tại
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
    });
    
    // Load trạng thái bàn/phòng theo ngày
    $('#ngayden').on('change', function() {
        const selectedDate = $(this).val();
        const currentType = $('input[name="loaidatban"]:checked').val();
        
        loadAvailability(selectedDate, currentType);
    });
    
    function loadAvailability(date, type) {
        $.ajax({
            url: 'check_availability.php',
            type: 'GET',
            data: {
                date: date,
                type: type
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateAvailability(response.booked, type);
                }
            }
        });
    }
    
    function updateAvailability(bookedIds, type) {
        const container = type === 'thuong' ? '#banGrid' : '#phongGrid';
        
        $(container + ' .table-option').each(function() {
            const id = $(this).data('id');
            
            if (bookedIds.includes(id)) {
                $(this).addClass('unavailable');
                $(this).find('input').prop('disabled', true);
            } else {
                $(this).removeClass('unavailable');
                $(this).find('input').prop('disabled', false);
            }
        });
    }
    
    // Validate form
    $('#formEditBooking').on('submit', function(e) {
        const loaidatban = $('input[name="loaidatban"]:checked').val();
        const tableSelected = $('input[name="table_id"]:checked').length > 0;
        
        if (!tableSelected) {
            e.preventDefault();
            alert('Vui lòng chọn ' + (loaidatban === 'thuong' ? 'bàn' : 'phòng') + '!');
            return false;
        }
        
        return true;
    });
});
</script>

</body>
</html>