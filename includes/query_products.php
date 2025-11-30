<?php 
require_once "db_connect.php";

// ✅ BẮT BUỘC: Chỉ xử lý AJAX requests
if (!isset($_GET['id']) && !isset($_GET['maloai'])) {
    exit;
}

// ✅ Xử lý lấy thông tin sản phẩm khi click "Mua ngay"
if (isset($_GET['id']) && !isset($_GET['maloai'])) {
    $id = intval($_GET['id']);
    
    // Lấy thông tin sản phẩm
    $stmt_product = mysqli_prepare($ketnoi, "SELECT * FROM sanpham WHERE MaSP = ?");
    
    if (!$stmt_product) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . mysqli_error($ketnoi)
        ]);
        exit;
    }
    
    mysqli_stmt_bind_param($stmt_product, "i", $id);
    mysqli_stmt_execute($stmt_product);
    $result_product = mysqli_stmt_get_result($stmt_product);
    $sp_info = mysqli_fetch_assoc($result_product);
    
    if (!$sp_info) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Không tìm thấy sản phẩm với ID: ' . $id
        ]);
        mysqli_stmt_close($stmt_product);
        exit;
    }
    
    // ✅ SỬA: Lấy Anh từ bảng sanpham_size (ss.Anh), không phải từ size (s.Anh)
    $stmt_sizes = mysqli_prepare($ketnoi, "SELECT ss.MaSize, s.TenSize, ss.Gia, ss.Anh
                FROM sanpham_size ss
                INNER JOIN size s ON ss.MaSize = s.MaSize
                WHERE ss.MaSP = ?
                ORDER BY ss.Gia ASC");
    
    if (!$stmt_sizes) {
        mysqli_stmt_close($stmt_product);
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . mysqli_error($ketnoi)
        ]);
        exit;
    }
    
    mysqli_stmt_bind_param($stmt_sizes, "i", $id);
    mysqli_stmt_execute($stmt_sizes);
    $result_sizes = mysqli_stmt_get_result($stmt_sizes);
    
    $sizes = array();
    while ($row = mysqli_fetch_assoc($result_sizes)) {
        $sizes[] = $row;
    }
    
    mysqli_stmt_close($stmt_product);
    mysqli_stmt_close($stmt_sizes);
    
    // ✅ Trả về JSON
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'product' => $sp_info,
        'sizes' => $sizes
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ✅ Lấy sản phẩm theo loại
if (isset($_GET['maloai'])) {
    $maloai = intval($_GET['maloai']);
    
    $stmt = mysqli_prepare($ketnoi, "SELECT sanpham.*, MIN(sps.Gia) as GiaThapNhat
                                     FROM sanpham
                                     INNER JOIN sanpham_size as sps ON sps.MaSP = sanpham.MaSP
                                     WHERE sanpham.MaLoai = ?
                                     GROUP BY sanpham.MaSP");
    mysqli_stmt_bind_param($stmt, "i", $maloai);
    mysqli_stmt_execute($stmt);
    $sanpham_rs = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($sanpham_rs) > 0) {
        foreach ($sanpham_rs as $sp): 
            include __DIR__ . "/../components/product_card.php"; 
        endforeach; 
    } else {
        echo '<p>Không có sản phẩm nào trong danh mục này.</p>';
    }
    
    mysqli_stmt_close($stmt);
    exit;
}
?>