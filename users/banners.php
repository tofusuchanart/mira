

      <!-- เริ่มต้นแบนเนอร์ -->
       
        <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleFade" data-bs-slide-to="0" class="active" aria-current="true"></button>
        <button type="button" data-bs-target="#carouselExampleFade" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#carouselExampleFade" data-bs-slide-to="2"></button>

    </div>

    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="../photo/banner4.png" class="d-block w-100" alt="Mira Banner 1">
        </div>
        <div class="carousel-item">
            <img src="../photo/banner2.png" class="d-block w-100" alt="Mira Banner 2">
        </div>
        <div class="carousel-item">
            <img src="../photo/banner3.png" class="d-block w-100" alt="Mira Banner 3">
        </div>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
<section class="container my-5">
    <div class="d-flex align-items-center mb-4">
        <h4 class="fw-bold mb-0" style="color: #b3365b;"><i class="bi bi-clock-history me-2"></i> สิทธิพิเศษจำกัดเวลา</h4>
        <div class="ms-3 flex-grow-1 border-bottom opacity-25"></div>
    </div>

    <div class="row g-3">
        <?php foreach($active_promos as $promo): ?>
            <div class="col-md-4" id="promo-card-<?= $promo['promo_id'] ?>">
                <div class="mira-voucher shadow-sm d-flex position-relative">
                    
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                            หมดเขตใน: <span class="countdown" data-time="<?= $promo['end_ts'] ?>">--:--:--</span>
                        </span>
                    </div>

                    <div class="voucher-left d-flex align-items-center justify-content-center p-3">
                        <img src="../photo/golo.png" width="45" alt="Mira Logo">
                    </div>
                    
                    <div class="voucher-right p-3 flex-grow-1 bg-white">
                        <div class="fw-bold text-dark mb-1 mt-2 small text-truncate" style="max-width: 150px;">
                            <?= htmlspecialchars($promo['promo_name']) ?>
                        </div>
                        <div class="fw-bold" style="color: #b3365b; font-size: 1.2rem;">
                            ลด <?= number_format($promo['discount_value']) ?><?= ($promo['discount_type'] == 'percentage' ? '%' : '฿') ?>
                        </div>
                        <p class="text-muted mb-2" style="font-size: 0.7rem;">ขั้นต่ำ <?= number_format($promo['min_spent']) ?> บาท</p>
                        
                        <button class="btn btn-collect-mira w-100 py-1" onclick="collectVoucher(<?= $promo['promo_id'] ?>)">
                            เก็บส่วนลด
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>