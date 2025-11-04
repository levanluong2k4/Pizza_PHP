<?php 
$search = $_GET['search'] ?? '';
$category_id = $_GET['category_id'] ?? '';

require 'includes/db_connect.php';
$products_result = null;
$categories = null;

// Chuẩn bị câu SQL cơ bản
$sql = "SELECT * FROM sanpham WHERE 1=1";
$params = [];
$types = "";

// Nếu có từ khóa tìm kiếm
if ($search != '') {
    $sql .= " AND TenSP LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// Nếu có chọn loại sản phẩm
if ($category_id != '') {
    $sql .= " AND MaLoai = ?";
    $params[] = $category_id;
    $types .= "s";

    // Lấy thông tin loại để hiển thị tiêu đề
    $sql_categories = "SELECT * FROM loaisanpham WHERE MaLoai = ?";
    $stmt_cat = $ketnoi->prepare($sql_categories);
    $stmt_cat->bind_param("s", $category_id);
    $stmt_cat->execute();
    $categories = $stmt_cat->get_result()->fetch_assoc();
    $stmt_cat->close();
}

// Chuẩn bị truy vấn chính
$stmt = $ketnoi->prepare($sql);

// Gắn tham số nếu có
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$products_result = $stmt->get_result();
$stmt->close();

require 'includes/query_products.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tìm kiếm</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- animate -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="./css/animate.css">
    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/pizza.css">
    <link rel="stylesheet" href="css/basic.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        body{
            padding: 0;
        }
        header{
            margin-top: 67px;
        }
        
        .product-row {
            display: none;
        }
        
        .product-row.show {
            display: flex;
        }
        
        .btn-load-more {
            margin: 30px auto;
            padding: 12px 40px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-load-more:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .product-card {
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body>

    <header class="bg-icon pt-2">
        <?php include 'components/navbar.php'; ?>
<main class="container my-5">
    <h2 class="mb-4">
        <?php 
            if ($search != "") echo "Kết quả tìm kiếm cho: " . htmlspecialchars($search);
            if ($category_id!= "" && $categories) echo " - " . htmlspecialchars($categories['TenLoai']);
        ?>
    </h2>
    <hr>

    <?php if ($products_result && mysqli_num_rows($products_result) > 0): ?>
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
                                    <a href="?id=<?php echo $sp["MaSP"]; ?>" class="inner-btn mt-2"
                                        data-masp="<?php echo $sp["MaSP"]; ?>">
                                        Mua ngay
                                    </a>
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

    <?php else: ?>
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            Không tìm thấy sản phẩm nào.
        </div>
    <?php endif; ?>
</main>


           <!-- Modal chọn size -->
        <?php require "includes/modal_size.php" ?>
    </header>

    <?php require "includes/toast_cart.php"?>

    <?php include './components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            if(!loadMoreBtn) return;
            
            const productRows = document.querySelectorAll('.product-row');
            let currentShowingRows = <?= $initial_rows ?>;
            const rowsToLoadPerClick = 2; // Load thêm 2 dòng (8 sản phẩm) mỗi lần click
            
            loadMoreBtn.addEventListener('click', function() {
                let rowsShown = 0;
                
                // Hiển thị thêm 2 dòng tiếp theo
                for(let i = currentShowingRows; i < productRows.length && rowsShown < rowsToLoadPerClick; i++) {
                    productRows[i].classList.add('show');
                    rowsShown++;
                    currentShowingRows++;
                }
                
                // Ẩn nút nếu đã hiển thị hết tất cả sản phẩm
                if(currentShowingRows >= productRows.length) {
                    loadMoreBtn.style.display = 'none';
                }
                
                // Smooth scroll đến sản phẩm mới được hiển thị
                setTimeout(() => {
                    const newlyShownRow = productRows[currentShowingRows - rowsShown];
                    if(newlyShownRow) {
                        newlyShownRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }, 100);
            });
        });
    </script>


 <script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('id')) {

            const modal = new bootstrap.Modal(document.getElementById('sizeModal'));
            modal.show();
        }
    });
    document.addEventListener("DOMContentLoaded", function() {
        const radios = document.querySelectorAll('.size-radio');
        const quantityInput = document.getElementById('quantity');
        const totalPriceSpan = document.getElementById('totalPrice');
        const selectedInfo = document.querySelector('.selected-info');
        const selectedSizeSpan = document.getElementById('selectedSize');
        const selectedPriceSpan = document.getElementById('selectedPrice');
        const decreaseBtn = document.getElementById('decreaseBtn');
        const increaseBtn = document.getElementById('increaseBtn');

        function updateTotal() {
            const selected = document.querySelector('.size-radio:checked');
            const quantity = parseInt(quantityInput.value) || 1;

            // Nếu chưa chọn size, reset lại hiển thị
            if (!selected) {
                totalPriceSpan.textContent = "0 VNĐ";
                selectedInfo.style.display = 'none';
                addToCartBtn.disabled = true;
                return;
            }

            const name = selected.dataset.name;
            const price = parseFloat(selected.dataset.price);

            // Nếu giá không hợp lệ => ngăn lỗi NaN
            if (isNaN(price)) {
                totalPriceSpan.textContent = "0 VNĐ";
                return;
            }

            const total = price * quantity;

            selectedSizeSpan.textContent = name;
            selectedPriceSpan.textContent = price.toLocaleString('vi-VN');
            totalPriceSpan.textContent = total.toLocaleString('vi-VN') + " VNĐ";
            selectedInfo.style.display = 'block';
            addToCartBtn.disabled = false;
            const sizeId = selected.value;
            const productId = "<?php echo $sp_info['MaSP']; ?>";
            addToCartBtn.href = `./cart/add_to_cart.php?id=${productId}&masize=${sizeId}&soluong=${quantity}`;
        }

        radios.forEach(radio => radio.addEventListener('change', updateTotal));
        quantityInput.addEventListener('input', updateTotal);
        decreaseBtn.addEventListener('click', () => {
            let current = parseInt(quantityInput.value);
            if (current > 1) {
                quantityInput.value = current - 1;
                updateTotal();
            }
        });
        increaseBtn.addEventListener('click', () => {
            quantityInput.value = parseInt(quantityInput.value) + 1;
            updateTotal();
        });
        fetch(`./cart/add_to_cart.php?id=${maSP}&masize=${maSize}&soluong=${quantity}`)
            .then(response => response.text())
            .then(total => {
                document.querySelector(".cart-count").textContent = total;
                alert("Đã thêm sản phẩm vào giỏ hàng!");
            });
    });
    </script>
 <script src="js/search.js"></script>
</body>
</html>