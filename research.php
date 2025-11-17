<?php 
$search = $_GET['search'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$combo_id = $_GET['combo_id'] ?? '';
$loaidatban = $_GET['loaidatban'] ?? '';



require 'includes/db_connect.php';

$products_result = null;
$categories = null;
$combos_id = null;
$combo_details = null;
$loaidatban_result=null;

// Kiểm tra xem có tìm kiếm theo combo không
$is_combo_search = ($combo_id != '');
$is_loaidatban_search=($loaidatban != '');
if ($is_combo_search) {
    // Lấy thông tin combo
    $sql_combo = "SELECT * FROM combo WHERE MaCombo = ?";
    $stmt_combo = $ketnoi->prepare($sql_combo);
    $stmt_combo->bind_param("i", $combo_id);
    $stmt_combo->execute();
    $combos_id = $stmt_combo->get_result()->fetch_assoc();
    $stmt_combo->close();
    
    // Lấy chi tiết combo với thông tin sản phẩm và giá theo size
    $sql_combo_detail = "SELECT ct.*, 
                                sps.Gia as ThanhTien, 
                                sp.TenSP, 
                                sp.Anh, 
                                sp.MaLoai,
                                sps.MaSize,
                                s.TenSize
                         FROM chitietcombo ct 
                         INNER JOIN sanpham sp ON ct.MaSP = sp.MaSP
                         INNER JOIN size s on ct.MaSize = s.MaSize
                         INNER JOIN sanpham_size sps ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize 
                         WHERE ct.MaCombo = ?";
    $stmt_detail = $ketnoi->prepare($sql_combo_detail);
    $stmt_detail->bind_param("i", $combo_id);
    $stmt_detail->execute();
    $combo_details = $stmt_detail->get_result();
    $stmt_detail->close();
}

else if($is_loaidatban_search){
    $sqlcombo = "SELECT * FROM combo";
    $loaidatban_result = mysqli_query($ketnoi, $sqlcombo);

}
else {
    // Code tìm kiếm sản phẩm bình thường
    $sql = "SELECT * FROM sanpham WHERE 1=1";
    $params = [];
    $types = "";

    if ($search != '') {
        $sql .= " AND TenSP LIKE ?";
        $params[] = "%$search%";
        $types .= "s";
    }

    if ($category_id != '') {
        $sql .= " AND MaLoai = ?";
        $params[] = $category_id;
        $types .= "s";
        
        $sql_categories = "SELECT * FROM loaisanpham WHERE MaLoai = ?";
        $stmt_cat = $ketnoi->prepare($sql_categories);
        $stmt_cat->bind_param("s", $category_id);
        $stmt_cat->execute();
        $categories = $stmt_cat->get_result()->fetch_assoc();
        $stmt_cat->close();
    }

    $stmt = $ketnoi->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $products_result = $stmt->get_result();
    $stmt->close();
}

require 'includes/query_products.php';


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Tìm kiếm</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="./css/animate.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
    <link rel="stylesheet" href="css/pizza.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="css/search.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


</head>

<body>

    <header class="bg-icon pt-2">
        <?php include 'components/navbar.php'; ?>
    </header>

    <main class="container my-5">
        <h2 class="mb-4">
            <?php 
            if ($search != "") echo "Kết quả tìm kiếm cho: " . htmlspecialchars($search);
            if ($category_id != "" && $categories) echo " - " . htmlspecialchars($categories['TenLoai']);
            if ($combo_id != "" && $combos_id) echo "Combo: " . htmlspecialchars($combos_id['Tencombo']);
        ?>
        </h2>
        <hr>

        <?php if ($is_combo_search && $combos_id): ?>
        <!-- Hiển thị thông tin combo -->
        <div class="combo-card">
            <div class="combo-header">
                <h3>
                    <i class="fas fa-box-open me-2"></i>
                    <?php echo htmlspecialchars($combos_id['Tencombo']); ?>
                </h3>
                <div class="combo-price">
                    <i class="fas fa-tags me-2"></i>
                    <?php echo number_format($combos_id['giamgia'], 0, ',', '.'); ?> VNĐ
                </div>
            </div>

            <div class="combo-body">
                <?php if (!empty($combos_id['Anh'])): ?>
                <img src="./<?php echo $combos_id['Anh']; ?>"
                    alt="<?php echo htmlspecialchars($combos_id['Tencombo']); ?>" class="combo-image">
                <?php endif; ?>

                <button class="btn btn-view-details" onclick="toggleComboDetails()">
                    <i class="fas fa-list me-2"></i>
                    <span id="detailsBtnText">Xem chi tiết sản phẩm trong combo</span>
                    <i class="fas fa-chevron-down ms-2" id="detailsIcon"></i>
                </button>

                <!-- Chi tiết combo -->
                <div id="comboDetails" class="combo-details">
                    <h5>
                        <i class="fas fa-pizza-slice me-2"></i>
                        Sản phẩm trong combo:
                    </h5>

                    <?php 
                    $total_combo = 0;
                    if ($combo_details && mysqli_num_rows($combo_details) > 0): 
                        mysqli_data_seek($combo_details, 0);
                        while ($item = mysqli_fetch_assoc($combo_details)): 
                            $item_total = $item['ThanhTien'] * $item['SoLuong'];
                            $total_combo += $item_total;
                    ?>
                    <div class="combo-item" data-masp="<?php echo $item['MaSP']; ?>"
                        data-maloai="<?php echo $item['MaLoai']; ?>" data-masize="<?php echo $item['MaSize']; ?>"
                        data-soluong="<?php echo $item['SoLuong']; ?>">
                        <img src="./<?php echo $item['Anh']; ?>" alt="<?php echo htmlspecialchars($item['TenSP']); ?>"
                            class="combo-item-image">

                        <div class="combo-item-info">
                            <div class="combo-item-name">
                                <?php echo htmlspecialchars($item['TenSP']); ?>
                                <?php if (isset($item['TenSize'])): ?>
                                <span class="badge bg-info ms-2"><?php echo $item['TenSize']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="combo-item-qty">
                                <i class="fas fa-shopping-cart me-1"></i>
                                Số lượng: <strong><?php echo $item['SoLuong']; ?></strong>
                                <span class="ms-2 text-muted">
                                    <?php echo number_format($item['ThanhTien'], 0, ',', '.'); ?> VNĐ/món
                                </span>
                            </div>
                        </div>

                        <div class="text-end">
                            <div class="combo-item-price">
                                <?php echo number_format($item_total, 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    endif; 
                    ?>

                    <div class="combo-total">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="combo-total-label">
                                <i class="fas fa-receipt me-2"></i>
                                Tổng giá trị sản phẩm:
                            </span>
                            <span class="combo-total-price">
                                <?php echo number_format($total_combo, 0, ',', '.'); ?> VNĐ
                            </span>
                        </div>

                        <?php if ($total_combo > $combos_id['giamgia']): ?>
                        <div class="combo-savings">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="combo-savings-text">
                                    <i class="fas fa-gift me-2"></i>
                                    Tiết kiệm được:
                                </span>
                                <span class="combo-savings-text" style="font-size: 22px;">
                                    <?php echo number_format($total_combo - $combos_id['giamgia'], 0, ',', '.'); ?> VNĐ
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="./datban/info_datban.php?combo_id=<?php echo $combo_id ?>&loaidatban=tiec"
                    class="btn btn-order-combo mt-3">
                    <i class="fas fa-calendar-check me-2"></i>
                    Đặt bàn ngay - Combo này
                </a>
            </div>
        </div>

        <?php elseif ($products_result && mysqli_num_rows($products_result) > 0): ?>
        <!-- Hiển thị danh sách sản phẩm bình thường -->
        <div id="productContainer">
            <?php 
            $products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);
            $total_products = count($products);
            $products_per_row = 3;
            $rows = array_chunk($products, $products_per_row);
            $initial_rows = 1;
            
            foreach ($rows as $index => $row_products): 
                $show_class = $index < $initial_rows ? 'show' : '';
            ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4 product-row <?= $show_class ?>">
                <?php foreach ($row_products as $sp): ?>
                <div class="col-lg-4 col-6 wow animate__bounceInLeft">
                    <div class="inner-items text-center">
                        <div class="card border-0 bg-transparent">
                            <img src="./<?php echo $sp["Anh"] ?>" class="card-img-top mx-auto"
                                alt="<?php echo $sp["TenSP"] ?>">
                            <div class="card-body">
                                <p class="card-text text-success mb-3" style="font-weight: 600;">
                                    <?php echo $sp["TenSP"] ?>
                                </p>
                                <button class="inner-btn mt-2 btn-buy" data-masp="<?php echo $sp["MaSP"]; ?>">
                                    Mua ngay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($rows) > $initial_rows): ?>
        <div class="text-center">
            <button id="loadMoreBtn" class="btn btn-outline-success btn-load-more">
                <i class="fas fa-chevron-down me-2"></i>Xem thêm
            </button>
        </div>
        <?php endif; ?>

        <?php elseif ($loaidatban_result): ?>
        <!-- Hiển thị danh sách sản phẩm bình thường -->
        <div id="productContainer">
            <?php 
            $product_combo = mysqli_fetch_all($loaidatban_result, MYSQLI_ASSOC);
            $total_products = count($product_combo);
            $combo_per_row = 3;
            $rows = array_chunk($product_combo, $combo_per_row);
            $initial_rows = 1;
            
            foreach ($rows as $index => $row_products): 
                $show_class = $index < $initial_rows ? 'show' : '';
            ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4 product-row <?= $show_class ?>">
                <?php foreach ($row_products as $sp): ?>

                <div class="col-lg-4 col-6 wow animate__bounceInLeft">
                    <div class="inner-items card-combo text-center">
                        <a href="/unitop/backend/lesson/school/project_pizza/research.php?combo_id=<?php echo $sp["MaCombo"]; ?>&loaidatban=tiec">
                        <div class="card border-0 bg-transparent">
                            <img src="./<?php echo $sp["Anh"] ?>" class="card-img-top mx-auto"
                                alt="<?php echo $sp["Tencombo"] ?>">
                            <div class="card-body align-items-center" style="height:300px">
                                <h3 class="card-text text-success mb-3" style="font-weight: 600;">
                                    <?php echo $sp["Tencombo"] ?>
                                </h3>
                                <div class="combo-description" style=" height: 150px;     
                                                                                overflow-y: auto;">
                                            <?php 
                                    $macombo=$sp['MaCombo'];
                                        $sql_detail_combo="SELECT ct.*, 
                                            sps.Gia as ThanhTien, 
                                            sp.TenSP, 
                                            sp.Anh, 
                                            sp.MaLoai,
                                            sps.MaSize,
                                            s.TenSize
                                    FROM chitietcombo ct 
                                    INNER JOIN sanpham sp ON ct.MaSP = sp.MaSP
                                    INNER JOIN size s on ct.MaSize = s.MaSize
                                    INNER JOIN sanpham_size sps ON ct.MaSP = sps.MaSP AND ct.MaSize = sps.MaSize 
                                    WHERE ct.MaCombo=$macombo";
                                        $result_detail=mysqli_query($ketnoi,$sql_detail_combo);
                                    $tongtien=0;
                                    foreach ($result_detail as $value) :
                                    $tien= $value['ThanhTien'] * $value['SoLuong'];
                                        $tongtien += $tien;
                                    echo '<p class="m-0 text-start card-text" style="font-weight: 600; color:#0000004a">'
                                                . '+' . $value['SoLuong'] . ' ' . $value["TenSP"] .
                                            '</p>';

                                    endforeach
                                                    ?>

                            </div>
                                <div class="d-flex align-items-center justify-content-between">

                                    <span class="combo-total-price">

                                        <?php echo number_format($tongtien, 0, ',', '.'); ?> VNĐ
                                    </span>
                                    <a class="inner-btn mt-2  py-2 px-5"
                                        href="./datban/info_datban.php?combo_id=<?php echo $sp['MaCombo'] ?>&loaidatban=tiec">
                                        Đặt ngay
                                    </a>
                                </div>

                            </div>
                        </div>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($rows) > $initial_rows): ?>
        <div class="text-center">
            <button id="loadMoreBtn" class="btn btn-outline-success btn-load-more">
                <i class="fas fa-chevron-down me-2"></i>Xem thêm
            </button>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            Không tìm thấy sản phẩm nào.
        </div>
        <?php endif; ?>
    </main>

    <!-- Modal chọn size -->
    <?php require "includes/modal_size.php" ?>

    <!-- Modal thay đổi sản phẩm trong combo -->
    <div class="modal fade" id="modalChangeProduct" tabindex="-1" aria-labelledby="modalChangeProductLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalChangeProductLabel">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Thay đổi sản phẩm
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="current-product-info mb-4 p-3 bg-light rounded">
                        <h6 class="mb-3">
                            <i class="fas fa-pizza-slice me-2"></i>
                            Sản phẩm hiện tại:
                        </h6>
                        <div class="d-flex align-items-center">
                            <img id="currentProductImage" src="" alt="" class="rounded me-3"
                                style="width: 80px; height: 80px; object-fit: cover;">
                            <div>
                                <div class="fw-bold" id="currentProductName"></div>
                                <div class="text-muted" id="currentProductSize"></div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-3">
                        <i class="fas fa-list me-2"></i>
                        Chọn sản phẩm thay thế (cùng loại):
                    </h6>

                    <div id="productList" class="row g-3"></div>

                    <div id="loadingProducts" class="text-center py-5 d-none">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Đang tải sản phẩm...</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Hủy
                    </button>
                    <button type="button" class="btn btn-success" id="btnConfirmChange" disabled>
                        <i class="fas fa-check me-2"></i>
                        Xác nhận thay đổi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php require "includes/toast_cart.php"?>

    <?php include './components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="js/search.js"></script>
    <script src="js/add_to_cart.js"></script>



    <script>
    // Toggle combo details
    function toggleComboDetails() {
        const details = document.getElementById('comboDetails');
        const btnText = document.getElementById('detailsBtnText');
        const icon = document.getElementById('detailsIcon');

        details.classList.toggle('show');

        if (details.classList.contains('show')) {
            btnText.textContent = 'Ẩn chi tiết sản phẩm';
            icon.className = 'fas fa-chevron-up ms-2';
        } else {
            btnText.textContent = 'Xem chi tiết sản phẩm trong combo';
            icon.className = 'fas fa-chevron-down ms-2';
        }
    }



    // Load more products
    $('#loadMoreBtn').on('click', function() {
        const productRows = $('.product-row');
        let currentShowingRows = $('.product-row.show').length;
        const rowsToLoadPerClick = 2;

        let rowsShown = 0;
        for (let i = currentShowingRows; i < productRows.length && rowsShown < rowsToLoadPerClick; i++) {
            $(productRows[i]).addClass('show');
            rowsShown++;
            currentShowingRows++;
        }

        if (currentShowingRows >= productRows.length) {
            $(this).hide();
        }

        setTimeout(() => {
            const newlyShownRow = productRows[currentShowingRows - rowsShown];
            if (newlyShownRow) {
                newlyShownRow.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }
        }, 100);
    });

    $(document).ready(function() {
        let selectedProduct = null;
        let originalComboItem = null;

        $('.combo-item').on('click', function() {
            const masp = $(this).data('masp');
            const maloai = $(this).data('maloai');
            const masize = $(this).data('masize');

            // Lưu lại combo item gốc
            originalComboItem = $(this);

            // Mở modal
            $('#modalChangeProduct').modal('show');

            // Hiển thị thông tin sản phẩm hiện tại
            const imgSrc = $(this).find('img').attr('src');
            const productName = $(this).find('.combo-item-name').clone().children().remove().end()
                .text().trim();
            const productSize = $(this).find('.badge').text() || '';

            $('#currentProductImage').attr('src', imgSrc);
            $('#currentProductName').text(productName);
            $('#currentProductSize').text(productSize);

            // Load sản phẩm thay thế
            $('#productList').empty();
            $('#loadingProducts').removeClass('d-none');
            $('#btnConfirmChange').prop('disabled', true);
            selectedProduct = null;

            $.ajax({
                url: 'get_products_by_category.php',
                type: 'GET',
                data: {
                    category_id: maloai,
                    exclude_id: masp
                },
                dataType: 'json',
                success: function(res) {
                    $('#loadingProducts').addClass('d-none');

                    if (res.success && res.products.length > 0) {
                        res.products.forEach(p => {
                            // Hiển thị tất cả sizes của sản phẩm
                            p.sizes.forEach(size => {
                                $('#productList').append(`
                                <div class="col-md-4 col-6 mb-3">
                                    <div class="product-option-card" 
                                         data-masp="${p.MaSP}" 
                                         data-masize="${size.MaSize}"
                                         data-tensp="${p.TenSP}"
                                         data-tensize="${size.TenSize}"
                                         data-gia="${size.Gia}"
                                         data-anh="${p.Anh}">
                                        <img src="${p.Anh}" class="product-option-image" alt="${p.TenSP}">
                                        <div class="product-option-name">${p.TenSP}</div>
                                        <div class="badge bg-info mb-2">${size.TenSize}</div>
                                        <div class="product-option-price">${parseInt(size.Gia).toLocaleString()} VNĐ</div>
                                    </div>
                                </div>
                            `);
                            });
                        });

                        // Xử lý click chọn sản phẩm
                        $('.product-option-card').on('click', function() {
                            $('.product-option-card').removeClass('selected');
                            $(this).addClass('selected');

                            selectedProduct = {
                                masp: $(this).data('masp'),
                                masize: $(this).data('masize'),
                                tensp: $(this).data('tensp'),
                                tensize: $(this).data('tensize'),
                                gia: $(this).data('gia'),
                                anh: $(this).data('anh')
                            };

                            $('#btnConfirmChange').prop('disabled', false);
                        });
                    } else {
                        $('#productList').html(
                            '<div class="col-12"><p class="text-muted text-center">Không có sản phẩm thay thế nào.</p></div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    $('#loadingProducts').addClass('d-none');
                    $('#productList').html(
                        '<div class="col-12"><p class="text-danger text-center">Lỗi khi tải sản phẩm: ' +
                        error + '</p></div>');
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                }
            });
        });

        // Xử lý xác nhận thay đổi
        $('#btnConfirmChange').on('click', function() {
            if (selectedProduct && originalComboItem) {
                // Cập nhật giao diện combo item
                originalComboItem.find('img').attr('src', selectedProduct.anh);
                originalComboItem.find('.combo-item-name').html(
                    selectedProduct.tensp +
                    ' <span class="badge bg-info ms-2">' + selectedProduct.tensize + '</span>'
                );
                originalComboItem.find('.combo-item-price').text(
                    parseInt(selectedProduct.gia).toLocaleString() + ' VNĐ'
                );

                // Cập nhật data attributes
                originalComboItem.data('masp', selectedProduct.masp);
                originalComboItem.data('masize', selectedProduct.masize);

                // Đóng modal
                $('#modalChangeProduct').modal('hide');

                // Hiển thị thông báo
                alert('Đã thay đổi sản phẩm thành công!');
            }
        });
    });
    </script>

</body>

</html>