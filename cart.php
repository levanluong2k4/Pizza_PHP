<?php
session_start();
?>
<!doctype html>
<html lang="en">

<head>
    <title>Giỏ hàng - Pizza</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="bai6.css">
    <link rel="stylesheet" href="basic.css">
</head>

<body>
    <header class="bg-icon">
        <?php include 'navbar/navbar.php'; ?>
    </header>

    <main class="container my-5">
        <h2 class="text-center mb-4">Giỏ hàng của bạn</h2>

        <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
        <div class="text-center">
            <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
            <h4>Giỏ hàng của bạn đang trống</h4>
            <p class="text-muted">Hãy thêm một số sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
            <a href="index.php" class="btn btn-success">Tiếp tục mua sắm</a>
        </div>
        <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sản phẩm trong giỏ hàng</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                            $total = 0;
                            foreach ($_SESSION['cart'] as $key => $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                                $total += $subtotal;
                            ?>
                        <div class="row cart-item mb-3 pb-3 border-bottom" data-key="<?php echo $key; ?>">
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
                                    <button class="btn btn-outline-secondary btn-sm decrease-qty"
                                        type="button">-</button>
                                    <input type="number" class="form-control form-control-sm text-center quantity-input"
                                        value="<?php echo $item['quantity']; ?>" min="1" readonly>
                                    <button class="btn btn-outline-secondary btn-sm increase-qty"
                                        type="button">+</button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <p class="mb-1"><strong><?php echo number_format($subtotal); ?> VNĐ</strong></p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-danger btn-sm remove-item" type="button">
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
                        <h5 class="mb-0">Tổng kết đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <span id="subtotal"><?php echo number_format($total); ?> VNĐ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <span>Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng cộng:</strong>
                            <strong id="total" class="text-danger"><?php echo number_format($total); ?> VNĐ</strong>
                        </div>
                        <button class="btn btn-success w-100 mb-2">Thanh toán</button>
                        <a href="index.php" class="btn btn-outline-success w-100">Tiếp tục mua sắm</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <footer></footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function() {
        // Tăng số lượng
        $('.increase-qty').click(function() {
            var cartItem = $(this).closest('.cart-item');
            var key = cartItem.data('key');
            var quantityInput = cartItem.find('.quantity-input');
            var newQuantity = parseInt(quantityInput.val()) + 1;

            updateCartQuantity(key, newQuantity, cartItem);
        });

        // Giảm số lượng
        $('.decrease-qty').click(function() {
            var cartItem = $(this).closest('.cart-item');
            var key = cartItem.data('key');
            var quantityInput = cartItem.find('.quantity-input');
            var currentQuantity = parseInt(quantityInput.val());

            if (currentQuantity > 1) {
                var newQuantity = currentQuantity - 1;
                updateCartQuantity(key, newQuantity, cartItem);
            }
        });

        // Xóa sản phẩm
        $('.remove-item').click(function() {
            var cartItem = $(this).closest('.cart-item');
            var key = cartItem.data('key');

            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                removeCartItem(key, cartItem);
            }
        });

        // Cập nhật số lượng sản phẩm
        function updateCartQuantity(key, quantity, cartItem) {
            $.post('update_cart.php', {
                action: 'update',
                key: key,
                quantity: quantity
            }, function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    cartItem.find('.quantity-input').val(quantity);
                    updateTotals();
                } else {
                    alert('Có lỗi xảy ra: ' + result.message);
                }
            });
        }

        // Xóa sản phẩm khỏi giỏ hàng
        function removeCartItem(key, cartItem) {
            $.post('update_cart.php', {
                action: 'remove',
                key: key
            }, function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    cartItem.fadeOut(function() {
                        $(this).remove();
                        updateTotals();

                        // Nếu giỏ hàng trống, reload trang
                        if ($('.cart-item').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert('Có lỗi xảy ra: ' + result.message);
                }
            });
        }

        // Cập nhật tổng tiền
        function updateTotals() {
            $.get('get_cart_total.php', function(data) {
                var result = JSON.parse(data);
                $('#subtotal').text(result.total.toLocaleString() + ' VNĐ');
                $('#total').text(result.total.toLocaleString() + ' VNĐ');
            });
        }
    });
    </script>
</body>

</html>