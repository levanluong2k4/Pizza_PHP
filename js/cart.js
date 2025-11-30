// File: cart.js - PHIÊN BẢN HOÀN CHỈNH
$(document).ready(function() {

    function updateCartCount(count) {
        $('.cart-count').text(count);
    }
    
    // 1. XỬ LÝ TĂNG/GIẢM SỐ LƯỢNG
    $(document).on('click', '.btn-update-cart', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var masp = button.data('masp');
        var masize = button.data('masize');
        var type = button.data('type');
        
        var cartItem = $('#cart-item-' + masp + '-' + masize);
        var quantityInput = cartItem.find('.quantity-display');
        var currentQuantity = parseInt(quantityInput.val());
        
        // Nếu giảm xuống 0, xử lý xóa
        if (type === 'decrease' && currentQuantity <= 1) {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                return false;
            }
            type = 'delete';
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
                console.log(' Response:', response);
                
                if (response.success) {
                    if (type === 'delete') {
                        // Xóa sản phẩm khỏi DOM
                        cartItem.fadeOut(300, function() {
                            $(this).remove();
                            // Kiểm tra nếu giỏ hàng rỗng
                            if ($('.cart-item').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        // CẬP NHẬT SỐ LƯỢNG VÀ GIÁ TIỀN
                        quantityInput.val(response.quantity);
                        cartItem.find('.subtotal-display strong').text(
                            formatNumber(response.subtotal) + ' VNĐ'
                        );
                    }
                    
                    // Cập nhật tổng tiền (tất cả các vị trí có class .total-amount)
                    $('.total-amount').text(formatNumber(response.total) + ' VNĐ');
                    updateCartCount(response.cartCount);
                   
                } 
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX Error:', error);
                console.error('📄 Response Text:', xhr.responseText);
              
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
        
        return false;
    });
    
    
    // 2. XỬ LÝ XÓA SẢN PHẨM
    $(document).on('click', '.btn-delete-cart', function(e) {
        e.preventDefault();
        
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
                console.log(' Delete Response:', response);
                
                if (response.success) {
                    cartItem.fadeOut(300, function() {
                        $(this).remove();
                        if ($('.cart-item').length === 0) {
                            location.reload();
                        }
                    });
                    $('.total-amount').text(formatNumber(response.total) + ' VNĐ');
                    updateCartCount(response.cartCount);
                  
                }
            },
            error: function(xhr, status, error) {
                console.error(' AJAX Error:', error);
                console.error(' Response Text:', xhr.responseText);
              
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
        
        return false;
    });
    
    
   
    
    
    // 4. HÀM FORMAT SỐ TIỀN
    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
});