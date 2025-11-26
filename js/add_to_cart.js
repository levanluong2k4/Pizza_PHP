// ==================== KIỂM TRA JQUERY ====================
if (typeof $ === 'undefined') {
    console.error('❌ jQuery chưa được load!');
    // Load jQuery động nếu chưa có
    const script = document.createElement('script');
    script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
    script.onload = function() {
        console.log('✅ jQuery đã được load');
        initAddToCart();
    };
    document.head.appendChild(script);
} else {
    initAddToCart();
}

function initAddToCart() {
// ==================== ADD TO CART ====================
$('#addToCartBtn').on('click', function(e) {
    e.preventDefault();
    
    const productId = $(this).data('product-id');
    const sizeId = $(this).data('size-id');
    const quantity = $(this).data('quantity') || 1;
    
    // ✅ KIỂM TRA CHẶT CHẼ - Đảm bảo đã chọn size
    if (!productId || !sizeId) {
        alert('⚠️ Vui lòng chọn size trước khi thêm vào giỏ hàng!');
        return;
    }
    
    // ✅ KIỂM TRA LẦN 2 - Xem radio button có được check không
    const selectedRadio = $('.size-radio:checked');
    if (selectedRadio.length === 0) {
        alert('⚠️ Bạn chưa chọn size!');
        return;
    }
    
    // Disable button để tránh click nhiều lần
    $(this).prop('disabled', true).text('Đang thêm...');
    
 fetch(`./cart/add_to_cart.php?id=${productId}&masize=${sizeId}&soluong=${quantity}`)
    .then(res => res.json())
    .then(data => {
        console.log("✅ Response:", data);
        
        if (data.status === 'success') {
            // ✅ CẬP NHẬT SỐ LƯỢNG GIỎ HÀNG
            $('.cart-count').text(data.totalQuantity);
            
            // ✅ HIỂN THỊ THÔNG BÁO THÀNH CÔNG
            showNotification(' Đã thêm sản phẩm vào giỏ hàng!', 'success');
            
            // ✅ ĐÓNG MODAL VÀ XÓA BACKDROP
            let modal = bootstrap.Modal.getInstance(document.getElementById('sizeModal'));
            if (modal) {
                modal.hide();
            }
            
            // ✅ XÓA BACKDROP VÀ RESET BODY
            setTimeout(() => {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({
                    'overflow': '',
                    'padding-right': ''
                });
            }, 300);
            
            // Reset button
            $('#addToCartBtn').prop('disabled', false).text('Thêm vào giỏ hàng');
            
            // ✅ NẾU ĐANG Ở TRANG CART → RELOAD TRANG
            if (window.location.pathname.includes('cart.php')) {
                console.log("🔄 Đang ở trang cart, reload để hiển thị sản phẩm mới...");
                setTimeout(() => {
                    location.reload();
                }, 800);
            }
        } else {
            showNotification('❌ ' + (data.message || 'Có lỗi xảy ra!'), 'error');
            $('#addToCartBtn').prop('disabled', false).text('Thêm vào giỏ hàng');
        }
    })
    .catch(err => {
        console.error('❌ Lỗi fetch:', err);
        showNotification('❌ Không thể kết nối đến server!', 'error');
        $('#addToCartBtn').prop('disabled', false).text('Thêm vào giỏ hàng');
    });
});

// ==================== HIỂN THỊ THÔNG BÁO ====================
function showNotification(message, type = 'success') {
    // Xóa thông báo cũ nếu có
    $('.custom-notification').remove();
    
    const bgColor = type === 'success' ? '#28a745' : '#dc3545';
    const icon = type === 'success' ? '✅' : '❌';
    
    const notification = $(`
        <div class="custom-notification animate__animated animate__fadeInDown" 
             style="position: fixed; top: 80px; right: 20px; z-index: 9999; 
                    background: ${bgColor}; color: white; padding: 15px 25px; 
                    border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                    font-weight: 500; max-width: 350px;">
            ${icon} ${message}
        </div>
    `);
    
    $('body').append(notification);
    
    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        notification.addClass('animate__fadeOutUp');
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

$(document).ready(function() {
    console.log("🚀 Script initialized");
    
    // ==================== UPDATE MODAL ====================
    function updateModal(data) {
        console.log("📦 Updating modal with:", data);
        
        if (!data || !data.product) {
            console.error("❌ Invalid data:", data);
            alert("Không thể tải thông tin sản phẩm!");
            return;
        }
        
        // Cập nhật tiêu đề
        $('#sizeModalLabel').text('Chọn size cho ' + data.product.TenSP);
        
        // Cập nhật hình ảnh
        let imagePath = data.product.Anh;
        if (!imagePath.startsWith('./') && !imagePath.startsWith('http')) {
            imagePath = './' + imagePath;
        }
        $('.product-image').attr('src', imagePath).attr('alt', data.product.TenSP);
        
        // Cập nhật tên và mô tả
        $('.product-name').text(data.product.TenSP);
        $('.product-description').text(data.product.MoTa || '');
        
        // Cập nhật sizes
        let sizeHTML = '';
        if (data.sizes && data.sizes.length > 0) {
            data.sizes.forEach(function(size, index) {
                let sizeImagePath = size.Anh;
                if (!sizeImagePath.startsWith('./') && !sizeImagePath.startsWith('http')) {
                    sizeImagePath = './' + sizeImagePath;
                }
                
                sizeHTML += `
                    <div class="form-check">
                        <input class="form-check-input size-radio" type="radio" 
                               name="size" id="size-${size.MaSize}"
                               value="${size.MaSize}" 
                               data-name="${size.TenSize}" 
                               data-price="${size.Gia}"
                               ${index === 0 ? 'checked' : ''}>
                        <label class="form-check-label" for="size-${size.MaSize}">
                            <img src="${sizeImagePath}" alt="" height="30px" class="me-2">
                            ${size.TenSize} - ${parseInt(size.Gia).toLocaleString('vi-VN')} VNĐ
                        </label>
                    </div>
                `;
            });
        } else {
            sizeHTML = '<p class="text-danger">Sản phẩm này hiện chưa có size.</p>';
        }
        
        $('.size-container').html(sizeHTML);
        
        // Reset
        $('#quantity').val(1);
        $('#totalPrice').text('0 VNĐ');
        $('.selected-info').hide();
        
        // Lưu product ID
        $('#sizeModal').data('product-id', data.product.MaSP);
        
        // Bind events cho size radio mới
        bindSizeEvents();
        
        // ✅ Kích hoạt updateTotal() cho size đầu tiên
        updateTotal();
    }
    
    // ==================== UPDATE TOTAL ====================
    function updateTotal() {
        const selected = $('.size-radio:checked');
        const quantity = parseInt($('#quantity').val()) || 1;
        const addToCartBtn = $('#addToCartBtn');
        
        // ✅ CHƯA CHỌN SIZE → DISABLE BUTTON
        if (selected.length === 0) {
            $('#totalPrice').text('0 VNĐ');
            $('.selected-info').hide();
            addToCartBtn.prop('disabled', true);
            addToCartBtn.addClass('disabled');
            return;
        }
        
        // ✅ ĐÃ CHỌN SIZE → ENABLE BUTTON
        const name = selected.data('name');
        const price = parseFloat(selected.data('price'));
        const total = price * quantity;
        
        $('#selectedSize').text(name);
        $('#selectedPrice').text(price.toLocaleString('vi-VN'));
        $('#totalPrice').text(total.toLocaleString('vi-VN') + ' VNĐ');
        $('.selected-info').show();
        
        const sizeId = selected.val();
        const productId = $('#sizeModal').data('product-id');
        
        // ✅ ENABLE BUTTON VÀ GẮN DATA
        addToCartBtn.prop('disabled', false);
        addToCartBtn.removeClass('disabled');
        addToCartBtn.data({
            'product-id': productId,
            'size-id': sizeId,
            'quantity': quantity
        });
    }
    
    // ==================== BIND SIZE EVENTS ====================
    function bindSizeEvents() {
        $('.size-radio').off('change').on('change', updateTotal);
    }
    
    // ==================== MUA NGAY ====================
    $(document).on('click', '.btn-buy', function(e) {
        e.preventDefault();
        let id = $(this).data("masp");
        console.log("🛒 Buy button clicked, product ID:", id);
        
        $.ajax({
            url: "/unitop/backend/lesson/school/project_pizza/includes/query_products.php",
            method: "GET",
            data: { id: id },
            dataType: 'json',
            beforeSend: function() {
                console.log("⏳ Loading product data...");
            }
        })
        .done(function(response) {
            console.log("✅ Product data loaded:", response);
            updateModal(response);
            
            let modal = new bootstrap.Modal(document.getElementById('sizeModal'));
            modal.show();
        })
        .fail(function(xhr, status, error) {
            console.error("❌ AJAX failed:", {
                status: status,
                error: error,
                response: xhr.responseText
            });
            alert("Có lỗi xảy ra khi tải sản phẩm. Vui lòng thử lại!");
        });
    });
    
    // ==================== QUANTITY CONTROLS ====================
    $('#quantity').on('input', updateTotal);
    
    $(document).on('click', '#decreaseBtn', function() {
        let input = $('#quantity');
        let current = parseInt(input.val());
        if (current > 1) {
            input.val(current - 1);
            updateTotal();
        }
    });
    
    $(document).on('click', '#increaseBtn', function() {
        let input = $('#quantity');
        input.val(parseInt(input.val()) + 1);
        updateTotal();
    });

        $('#sizeModal').on('hidden.bs.modal', function () {
        console.log("🔒 Modal đã đóng, dọn dẹp backdrop...");
        
        // Xóa tất cả backdrop
        $('.modal-backdrop').remove();
        
        // Reset body
        $('body').removeClass('modal-open').css({
            'overflow': '',
            'padding-right': ''
        });
    });
});

} // ✅ Đóng function initAddToCart()