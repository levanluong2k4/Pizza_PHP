// File: cart.js
$(document).ready(function() {
    
    // 1. XỬ LÝ TĂNG/GIẢM SỐ LƯỢNG
    $('.btn-update-cart').click(function(e) {
        e.preventDefault(); // THÊM DÒNG NÀY để ngăn chuyển trang
        
        var button = $(this);
        var masp = button.data('masp');
        var masize = button.data('masize');
        var type = button.data('type');
        
        var cartItem = $('#cart-item-' + masp + '-' + masize);
        var quantityInput = cartItem.find('.quantity-display');
        var currentQuantity = parseInt(quantityInput.val());
        
        if (type === 'decrease' && currentQuantity <= 1) {
        
        $.ajax({
            url: './cart/update_cart.php',
            type: 'POST',
            data: {
                masp: masp,
                masize: masize,
                type: 'delete'
            },
            dataType: 'json',
            beforeSend: function() {
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    cartItem.fadeOut(300, function() {
                        $(this).remove();
                        if ($('.cart-item').length === 0) {
                            location.reload();
                        }
                    });
                    $('#total-amount').text(formatNumber(response.total));
                    
                    showNotification(response.message, 'success');
                    
                 
                } else {
                    showNotification(response.message, 'danger');
                }
            },
            error: function() {
                showNotification('Có lỗi xảy ra. Vui lòng thử lại!', 'danger');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
            
          
        }
        
        $.ajax({
            url: './cart/update_cart.php',
            type: 'POST',
            data: {
                masp: masp,
                masize: masize,
                type: type
            },
            dataType: 'json',
            beforeSend: function() {
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    quantityInput.val(response.quantity);
                    cartItem.find('.subtotal-display strong').text(
                        formatNumber(response.subtotal) + ' VNĐ'
                    );
                    $('#total-amount').text(formatNumber(response.total));
                    showNotification(response.message, 'success');
                } else {
                    showNotification(response.message, 'danger');
                }
            },
            error: function() {
                showNotification('Có lỗi xảy ra. Vui lòng thử lại!', 'danger');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
        
        return false; // THÊM DÒNG NÀY
    });
    
    
    // 2. XỬ LÝ XÓA SẢN PHẨM
    $('.btn-delete-cart').click(function(e) {
        e.preventDefault(); // THÊM DÒNG NÀY
        
        if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
            return false;
        }
        
        var button = $(this);
        var masp = button.data('masp');
        var masize = button.data('masize');
        var cartItem = $('#cart-item-' + masp + '-' + masize);
        
        $.ajax({
            url: './cart/update_cart.php',
            type: 'POST',
            data: {
                masp: masp,
                masize: masize,
                type: 'delete'
            },
            dataType: 'json',
            beforeSend: function() {
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    cartItem.fadeOut(300, function() {
                        $(this).remove();
                        if ($('.cart-item').length === 0) {
                            location.reload();
                        }
                    });
                    $('#total-amount').text(formatNumber(response.total));
                    showNotification(response.message, 'success');
                } else {
                    showNotification(response.message, 'danger');
                }
            },
            error: function() {
                showNotification('Có lỗi xảy ra. Vui lòng thử lại!', 'danger');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
        
        return false; // THÊM DÒNG NÀY
    });
    
    
    // 3. HÀM HIỂN THỊ THÔNG BÁO
    function showNotification(message, type) {
        var notification = $('#cart-notification');
        notification.removeClass('alert-success alert-danger alert-warning');
        notification.addClass('alert-' + type);
        notification.text(message);
        notification.fadeIn();
        
        setTimeout(function() {
            notification.fadeOut();
        }, 3000);
    }
    
    
    // 4. HÀM FORMAT SỐ TIỀN
    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
});