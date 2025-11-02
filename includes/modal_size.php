    <?php if (isset($sp_info)): ?>
      
        <div class="modal fade" id="sizeModal" tabindex="-1" aria-labelledby="sizeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sizeModalLabel">
                            Chọn size cho <?php echo htmlspecialchars($sp_info['TenSP']); ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-5 d-flex flex-column align-items-center">
                                <img src="./<?php echo htmlspecialchars($sp_info['Anh']); ?>" class="rounded" style="width: 200px; height: auto;"
                                    alt="<?php echo htmlspecialchars($sp_info['TenSP']); ?>">
                                <h5 class="mt-3 text-success text-center">
                                    <?php echo htmlspecialchars($sp_info['TenSP']); ?>
                                </h5>
                                <p class="mt-3 fw-bolder opacity-50 text-center"><?php echo htmlspecialchars($sp_info['MoTa']); ?> </p>
                            </div>

                            <div class="col-md-7">
                                <h6>Chọn size:</h6>
                                <div class="size-options">
                                    <?php foreach ($sizes as $size): ?>
                                    <div class="form-check">
                                        <input class="form-check-input size-radio" type="radio" name="size"
                                            id="size-<?php echo $size['MaSize']; ?>"
                                            value="<?php echo $size['MaSize']; ?>"
                                            data-name="<?php echo htmlspecialchars($size['TenSize']); ?>"
                                            data-price="<?php echo floatval($size['Gia']); ?>">
                                        <label class="form-check-label" for="size-<?php echo $size['MaSize']; ?>">
                                            <img src="./<?php echo $size['Anh']; ?>" alt="" height="30px" class="me-2">
                                            <?php echo $size['TenSize']; ?> - <?php echo number_format($size['Gia']); ?>
                                            VNĐ
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="mt-3">
                                    <div class="selected-info" style="display:none;">
                                        <h6>Size đã chọn: <span id="selectedSize"></span></h6>
                                        <h5 class="text-danger">Giá: <span id="selectedPrice"></span> VNĐ</h5>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label for="quantity" class="form-label">Số lượng:</label>
                                    <div class="input-group" style="width: 150px;">
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="decreaseBtn">-</button>
                                        <input type="number" class="form-control text-center" id="quantity" value="1"
                                            min="1">
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="increaseBtn">+</button>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <h5>Tổng tiền: <span id="totalPrice" class="text-danger">0 VNĐ</span></h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <a href="#" class="btn btn-success" id="addToCartBtn"
                             disabled >Thêm vào giỏ hàng</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>