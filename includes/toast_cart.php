    <?php if(isset($_SESSION['cart_message']) && $_SESSION['cart_message'] == 'success'): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="cartToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <i class="fa fa-shopping-cart me-2"></i>
            <strong class="me-auto">Giỏ hàng</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <i class="fa fa-check-circle text-success"></i> Đã thêm sản phẩm vào giỏ hàng thành công!
        </div>
    </div>
</div>

<script>
    setTimeout(function() {
        var toastEl = document.getElementById('cartToast');
        if(toastEl) {
            var toast = new bootstrap.Toast(toastEl);
            toast.hide();
        }
    }, 3000); // Tự động ẩn sau 3 giây
</script>
<?php 
    unset($_SESSION['cart_message']); 
endif; 
?>
