<!-- Modal luôn tồn tại, nhưng để trống, sẽ được điền bằng AJAX -->
<div class="modal fade" id="sizeModal" tabindex="-1" aria-labelledby="sizeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="margin-top: 117px;">
            <div class="modal-header">
                <h5 class="modal-title" id="sizeModalLabel">Chọn size</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <!-- Hình ảnh và thông tin sản phẩm -->
                    <div class="col-md-5 d-flex flex-column align-items-center">
                        <img src="" class="product-image rounded" style="width: 200px; height: auto;" alt="">
                        <h5 class="mt-3 text-success text-center product-name"></h5>
                        <p class="mt-3 fw-bolder opacity-50 text-center product-description"></p>
                    </div>

                    <!-- Size options -->
                    <div class="col-md-7">
                        <h6>Chọn size:</h6>
                        <div class="size-container">
                            <!-- Sizes sẽ được load vào đây qua AJAX -->
                        </div>

                        <div class="mt-3">
                            <div class="selected-info" style="display:none;">
                                <h6>Size đã chọn: <span id="selectedSize"></span></h6>
                                <h5 class="text-danger">Giá: <span id="selectedPrice"></span> VNĐ</h5>
                            </div>
                        </div>

                        <!-- Số lượng -->
                        <div class="mt-3">
                            <label for="quantity" class="form-label">Số lượng:</label>
                            <div class="input-group" style="width: 150px;">
                                <button class="btn btn-outline-secondary" type="button" id="decreaseBtn">-</button>
                                <input type="number" class="form-control text-center" id="quantity" value="1" min="1">
                                <button class="btn btn-outline-secondary" type="button" id="increaseBtn">+</button>
                            </div>
                        </div>

                        <!-- Tổng tiền -->
                        <div class="mt-3">
                            <h5>Tổng tiền: <span id="totalPrice" class="text-danger">0 VNĐ</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <a href="javascript:void(0)" class="btn btn-success" id="addToCartBtn" disabled >Thêm vào giỏ hàng</a>
            </div>
        </div>
    </div>
</div>