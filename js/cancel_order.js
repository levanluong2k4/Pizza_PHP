$(document).ready(function(){

  $(document).on('click', '.btn-cancel_order', function(){
    let order_id = $(this).data("order_id");
    let order_item = $(this).closest('.order-item');
    
    // ✅ Kiểm tra xem đang ở trang nào
    let is_detail_page = $('.detail-order-container').length > 0;

    if (!confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
      return false;
    }
    
    $.ajax({
      url: "cart/cancel_order.php",
      type: "POST",
      data: { 
        order_id: order_id,
        from_detail: is_detail_page ? 1 : 0  // ✅ Gửi thông tin trang hiện tại
      },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          alert(response.message);
          
          // ✅ Nếu ở trang chi tiết → redirect về danh sách
          if (is_detail_page) {
            window.location.href = 'order_user.php';
          } 
          // ✅ Nếu ở trang danh sách → xóa item và reload nếu hết đơn
          else {
            order_item.fadeOut(300, function() {
              $(this).remove();
              if ($('.order-item').length === 0) {
                location.reload();
              }
            });
          }
        } else {
          alert(response.message);
        }
      },
      error: function(xhr, status, error) {
        console.log("error:", error);
        alert("Có lỗi xảy ra, vui lòng thử lại");
      }
    });
  });

});