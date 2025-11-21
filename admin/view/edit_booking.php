<?php
session_start();
require __DIR__ . '/../../includes/db_connect.php';

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
        c.Tencombo, c.giamgia
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

// Lấy danh sách tất cả sản phẩm với size
$sql_sanpham = "SELECT ss.MaSize, sp.MaSP, sp.TenSP, ss.Anh, s.TenSize, ss.Gia, lsp.TenLoai, lsp.MaLoai
                FROM sanpham_size ss
                INNER JOIN size s ON ss.MaSize = s.MaSize
                INNER JOIN sanpham sp ON sp.MaSP = ss.MaSP
                LEFT JOIN loaisanpham lsp ON sp.MaLoai = lsp.MaLoai
                ORDER BY sp.TenSP, s.MaSize";
$sanpham_list = $ketnoi->query($sql_sanpham)->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách loại sản phẩm
$sql_loai = "SELECT DISTINCT lsp.MaLoai, lsp.TenLoai 
             FROM loaisanpham lsp
             INNER JOIN sanpham sp ON sp.MaLoai = lsp.MaLoai
             ORDER BY lsp.TenLoai";
$loai_list = $ketnoi->query($sql_loai)->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách sản phẩm đã đặt (nếu là tiệc)
$chitiet_list = [];
if ($current_type == 'tiec' && $booking['MaPhong']) {
    $sql_chitiet = "SELECT ct.*, sp.TenSP, sps.Gia, s.TenSize, ct.MaSize
                    FROM chitietdatban ct
                    INNER JOIN size s ON ct.MaSize = s.MaSize
                    INNER JOIN sanpham sp ON sp.MaSP = ct.MaSP
                    INNER JOIN sanpham_size sps ON sps.MaSize = s.MaSize AND sps.MaSP = sp.MaSP
                    WHERE ct.MaDatBan = ?";
    $stmt = $ketnoi->prepare($sql_chitiet);
    $stmt->bind_param("i", $madatban);
    $stmt->execute();
    $chitiet_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
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
            max-width: 1200px;
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
        
        /* Combo Products Section */
        .combo-products-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
        }
        
        .product-list {
            max-height: 400px;
            overflow-y: auto;
            background: white;
            border-radius: 10px;
            padding: 15px;
        }
        
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .product-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-control input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }
        
        .btn-quantity {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .btn-quantity:hover {
            background: #5568d3;
        }
        
        .add-product-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .product-select-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .product-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .product-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        .product-card.selected {
            border-color: #667eea;
            background: #f0f4ff;
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
        
        .total-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
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
                    <p class="text-muted mb-0">Có combo và quản lý sản phẩm</p>
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
                                data-discount="<?php echo $combo['giamgia']; ?>"
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
                    <input type="number" class="form-control" name="tiencoc" id="tiencoc" min="0" 
                           value="<?php echo $booking['TienCoc'] ?? 0; ?>">
                </div>
                
                <!-- Quản lý sản phẩm combo -->
                <div class="combo-products-section" id="comboProductsSection">
                    <div class="section-title">
                        <i class="fas fa-utensils me-2"></i>
                        Quản lý sản phẩm combo
                        <button type="button" class="btn btn-sm btn-primary float-end" id="btnShowAddProduct">
                            <i class="fas fa-plus me-2"></i>Thêm sản phẩm
                        </button>
                    </div>
                    
                    <!-- Danh sách sản phẩm đã chọn -->
                    <div class="product-list" id="selectedProductsList">
                        <?php if (!empty($chitiet_list)): ?>
                            <?php foreach ($chitiet_list as $item): ?>
                            <div class="product-item" data-product-id="<?php echo $item['MaSP']; ?>-<?php echo $item['MaSize']; ?>">
                                <div class="product-info">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['TenSP']); ?> - <?php echo htmlspecialchars($item['TenSize']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo number_format($item['Gia']); ?> VNĐ
                                    </small>
                                </div>
                                <div class="product-actions">
                                    <div class="quantity-control">
                                        <button type="button" class="btn-quantity btn-decrease">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="form-control product-quantity" 
                                               name="products[<?php echo $item['MaSP']; ?>_<?php echo $item['MaSize']; ?>][quantity]" 
                                               value="<?php echo $item['SoLuong']; ?>" min="1">
                                        <button type="button" class="btn-quantity btn-increase">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-product">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="products[<?php echo $item['MaSP']; ?>_<?php echo $item['MaSize']; ?>][masp]" value="<?php echo $item['MaSP']; ?>">
                                <input type="hidden" name="products[<?php echo $item['MaSP']; ?>_<?php echo $item['MaSize']; ?>][masize]" value="<?php echo $item['MaSize']; ?>">
                                <input type="hidden" name="products[<?php echo $item['MaSP']; ?>_<?php echo $item['MaSize']; ?>][price]" value="<?php echo $item['Gia']; ?>">
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4" id="emptyMessage">
                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                <p>Chưa có sản phẩm nào được thêm</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Form thêm sản phẩm -->
                    <div class="add-product-box" id="addProductBox" style="display: none;">
                        <h6 class="mb-3">Chọn sản phẩm để thêm:</h6>
                        
                        <!-- Bộ lọc tìm kiếm -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="searchProduct" placeholder="Tìm kiếm sản phẩm...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-filter"></i></span>
                                    <select class="form-control" id="filterCategory">
                                        <option value="">-- Tất cả loại sản phẩm --</option>
                                        <?php foreach ($loai_list as $loai): ?>
                                        <option value="<?php echo $loai['MaLoai']; ?>">
                                            <?php echo htmlspecialchars($loai['TenLoai']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-select-grid" id="productGrid">
                            <?php 
                            if (!empty($sanpham_list)) {
                                foreach ($sanpham_list as $sp): 
                            ?>
                            <div class="product-card" 
                                 data-product-id="<?php echo isset($sp['MaSP']) ? $sp['MaSP'] : '0'; ?>-<?php echo isset($sp['MaSize']) ? $sp['MaSize'] : '0'; ?>"
                                 data-masp="<?php echo isset($sp['MaSP']) ? $sp['MaSP'] : '0'; ?>"
                                 data-masize="<?php echo isset($sp['MaSize']) ? $sp['MaSize'] : '0'; ?>"
                                 data-maloai="<?php echo isset($sp['MaLoai']) ? $sp['MaLoai'] : '0'; ?>"
                                 data-product-name="<?php echo isset($sp['TenSP']) ? htmlspecialchars($sp['TenSP']) : 'N/A'; ?>"
                                 data-product-price="<?php echo isset($sp['Gia']) ? $sp['Gia'] : '0'; ?>"
                                 data-product-unit="<?php echo isset($sp['TenSize']) ? htmlspecialchars($sp['TenSize']) : 'N/A'; ?>"
                                 data-search-text="<?php echo isset($sp['TenSP']) ? strtolower(htmlspecialchars($sp['TenSP'])) : ''; ?> <?php echo isset($sp['TenSize']) ? strtolower(htmlspecialchars($sp['TenSize'])) : ''; ?>">
                                <?php if (isset($sp['Anh']) && !empty($sp['Anh'])): ?>
                                <img src="../../<?php echo $sp['Anh']; ?>" 
                                     alt="<?php echo isset($sp['TenSP']) ? htmlspecialchars($sp['TenSP']) : 'Product'; ?>" 
                                     style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                                <?php else: ?>
                                <div style="width: 100%; height: 120px; background: #f0f0f0; border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                                <?php endif; ?>
                                <h6><?php echo isset($sp['TenSP']) ? htmlspecialchars($sp['TenSP']) : 'N/A'; ?></h6>
                                <p class="text-muted mb-0">
                                    <small>
                                        <?php echo isset($sp['TenSize']) ? htmlspecialchars($sp['TenSize']) : 'N/A'; ?> - 
                                        <?php echo isset($sp['Gia']) ? number_format($sp['Gia']) : '0'; ?> VNĐ
                                    </small>
                                </p>
                                <?php if (isset($sp['TenLoai']) && !empty($sp['TenLoai'])): ?>
                                <p class="mb-0 mt-1">
                                    <small class="badge bg-secondary"><?php echo htmlspecialchars($sp['TenLoai']); ?></small>
                                </p>
                                <?php endif; ?>
                            </div>
                            <?php 
                                endforeach;
                            } else {
                                echo '<div class="col-12"><p class="text-center text-muted">Không có sản phẩm nào</p></div>';
                            }
                            ?>
                        </div>
                        
                        <div class="text-center mt-3" id="noResultMessage" style="display: none;">
                            <i class="fas fa-search fa-3x text-muted mb-2"></i>
                            <p class="text-muted">Không tìm thấy sản phẩm phù hợp</p>
                        </div>
                        
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-secondary" id="btnCancelAdd">Đóng</button>
                        </div>
                    </div>
                    
                    <!-- Tổng kết -->
                    <div class="total-summary">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tổng tiền sản phẩm:</span>
                            <strong id="totalProducts">0 VNĐ</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Giảm giá combo:</span>
                            <strong id="discountAmount">0 VNĐ</strong>
                        </div>
                        <hr style="border-color: rgba(255,255,255,0.3)">
                        <div class="d-flex justify-content-between">
                            <h5>Tổng thanh toán:</h5>
                            <h5 id="finalTotal">0 VNĐ</h5>
                        </div>
                    </div>
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
            $('#phongGrid input[type="radio"]').prop('checked', false);
        } else {
            $('#banSection').hide();
            $('#phongSection').show();
            $('#banGrid input[type="radio"]').prop('checked', false);
        }
        
        updateTotalPrice();
    });
    
    // Xử lý click chọn bàn/phòng
    $('.table-option').on('click', function() {
        const type = $(this).data('type');
        $(`.table-option[data-type="${type}"]`).removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
    });
    
    // Hiện/ẩn form thêm sản phẩm
    $('#btnShowAddProduct').on('click', function() {
        $('#addProductBox').slideDown();
        $('#searchProduct').val('');
        $('#filterCategory').val('');
        filterProducts();
    });
    
    $('#btnCancelAdd').on('click', function() {
        $('#addProductBox').slideUp();
        $('.product-card').removeClass('selected');
    });
    
    // Tìm kiếm sản phẩm
    $('#searchProduct').on('keyup', function() {
        filterProducts();
    });
    
    // Lọc theo loại sản phẩm
    $('#filterCategory').on('change', function() {
        filterProducts();
    });
    
    // Hàm lọc sản phẩm
    function filterProducts() {
        const searchText = $('#searchProduct').val().toLowerCase();
        const selectedCategory = $('#filterCategory').val();
        let visibleCount = 0;
        
        $('.product-card').each(function() {
            const productText = $(this).data('search-text') || '';
            const productCategory = $(this).data('maloai') || '';
            
            // Kiểm tra tìm kiếm
            const matchSearch = searchText === '' || productText.includes(searchText);
            
            // Kiểm tra loại sản phẩm
            const matchCategory = selectedCategory === '' || productCategory == selectedCategory;
            
            // Hiển thị nếu khớp cả 2 điều kiện
            if (matchSearch && matchCategory) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        // Hiển thị thông báo nếu không có kết quả
        if (visibleCount === 0) {
            $('#noResultMessage').show();
        } else {
            $('#noResultMessage').hide();
        }
    }
    
    // Thêm sản phẩm
    $('.product-card').on('click', function() {
        const productId = $(this).data('product-id');
        const maSP = $(this).data('masp');
        const maSize = $(this).data('masize');
        const productName = $(this).data('product-name');
        const productPrice = $(this).data('product-price');
        const productUnit = $(this).data('product-unit');
        
        // Kiểm tra sản phẩm đã tồn tại chưa
        if ($(`.product-item[data-product-id="${productId}"]`).length > 0) {
            alert('Sản phẩm này đã có trong danh sách!');
            return;
        }
        
        // Xóa thông báo trống nếu có
        $('#emptyMessage').remove();
        
        // Tạo key unique cho product
        const productKey = `${maSP}_${maSize}`;
        
        // Thêm sản phẩm vào danh sách
        const productHTML = `
            <div class="product-item" data-product-id="${productId}">
                <div class="product-info">
                    <h6 class="mb-1">${productName} - ${productUnit}</h6>
                    <small class="text-muted">
                        ${formatNumber(productPrice)} VNĐ
                    </small>
                </div>
                <div class="product-actions">
                    <div class="quantity-control">
                        <button type="button" class="btn-quantity btn-decrease">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="form-control product-quantity" 
                               name="products[${productKey}][quantity]" 
                               value="1" min="1">
                        <button type="button" class="btn-quantity btn-increase">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-product">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <input type="hidden" name="products[${productKey}][masp]" value="${maSP}">
                <input type="hidden" name="products[${productKey}][masize]" value="${maSize}">
                <input type="hidden" name="products[${productKey}][price]" value="${productPrice}">
            </div>
        `;
        
        $('#selectedProductsList').append(productHTML);
        
        // Đánh dấu đã chọn
        $(this).addClass('selected');
        
        // Ẩn form thêm
        setTimeout(() => {
            $('#addProductBox').slideUp();
            $('.product-card').removeClass('selected');
        }, 300);
        
        updateTotalPrice();
    });
    
    // Xóa sản phẩm
    $(document).on('click', '.btn-remove-product', function() {
        if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
            $(this).closest('.product-item').fadeOut(300, function() {
                $(this).remove();
                
                // Hiện thông báo trống nếu không còn sản phẩm
                if ($('.product-item').length === 0) {
                    $('#selectedProductsList').html(`
                        <div class="text-center text-muted py-4" id="emptyMessage">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <p>Chưa có sản phẩm nào được thêm</p>
                        </div>
                    `);
                }
                
                updateTotalPrice();
            });
        }
    });
    
    // Tăng/giảm số lượng
    $(document).on('click', '.btn-increase', function() {
        const input = $(this).siblings('.product-quantity');
        const currentVal = parseInt(input.val()) || 1;
        input.val(currentVal + 1);
        updateTotalPrice();
    });
    
    $(document).on('click', '.btn-decrease', function() {
        const input = $(this).siblings('.product-quantity');
        const currentVal = parseInt(input.val()) || 1;
        if (currentVal > 1) {
            input.val(currentVal - 1);
            updateTotalPrice();
        }
    });
    
    // Thay đổi số lượng trực tiếp
    $(document).on('change', '.product-quantity', function() {
        const val = parseInt($(this).val()) || 1;
        if (val < 1) {
            $(this).val(1);
        }
        updateTotalPrice();
    });
    
    // Thay đổi combo
    $('#comboSelect').on('change', function() {
        updateTotalPrice();
    });
    
    // Hàm tính tổng tiền
    function updateTotalPrice() {
        let totalProducts = 0;
        
        // Tính tổng tiền sản phẩm
        $('.product-item').each(function() {
            const price = parseFloat($(this).find('input[name$="[price]"]').val()) || 0;
            const quantity = parseInt($(this).find('.product-quantity').val()) || 0;
            totalProducts += price * quantity;
        });
        
        // Lấy giảm giá từ combo
        const discountPercent = parseFloat($('#comboSelect option:selected').data('discount')) || 0;
        const discountAmount = (totalProducts * discountPercent) / 100;
        const finalTotal = totalProducts - discountAmount;
        
        // Cập nhật hiển thị
        $('#totalProducts').text(formatNumber(totalProducts) + ' VNĐ');
        $('#discountAmount').text(formatNumber(discountAmount) + ' VNĐ (' + discountPercent + '%)');
        $('#finalTotal').text(formatNumber(finalTotal) + ' VNĐ');
    }
    
    // Hàm format số
    function formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
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
        
        // Kiểm tra nếu là tiệc nhưng không có sản phẩm
        if (loaidatban === 'tiec' && $('.product-item').length === 0) {
            if (!confirm('Bạn chưa thêm sản phẩm nào. Bạn có chắc muốn tiếp tục?')) {
                e.preventDefault();
                return false;
            }
        }
        
        return true;
    });
    
    // Khởi tạo tính tổng tiền
    updateTotalPrice();
    
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
                type: type,
                exclude_booking: <?php echo $madatban; ?>
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
});
</script>

</body>
</html>