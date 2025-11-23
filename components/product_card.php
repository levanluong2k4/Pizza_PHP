       <div class="col-lg-4 col-6 wow animate__bounceInLeft">
                    <div class="inner-items text-center">
                        <div class="card border-0 bg-transparent">
                            <img src="./<?php echo $sp["Anh"] ?>" class="card-img-top mx-auto"
                                alt="<?php echo $sp["TenSP"] ?>">
                            <div class="card-body">
                                <p class="card-text text-success mb-3" style="font-weight: 600;">
                                    <?php echo $sp["TenSP"] ?></p>
                                
                                 <div class="d-flex align-items-center justify-content-center flex-column">

                                    <span class="combo-total-price fs-4 text-danger" style="font-weight: 600;">

                                        <?php echo number_format($sp['GiaThapNhat'] , 0, ',', '.'); ?> VNĐ
                                    </span>
                                   <button  class="inner-btn mt-2 btn-buy"
                                    data-masp="<?php echo $sp["MaSP"]; ?>">
                                    Mua ngay
                                </button>
                                </div>
                            </div>
                        </div>
                    </div>
        </div>