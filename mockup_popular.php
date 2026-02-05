<?php
try {
    // สมมติว่าดึงสินค้า 4 ชิ้นที่ราคาแพงที่สุดหรือมีสต็อกมากที่สุดมาโชว์เป็นตัวยอดนิยม
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY price DESC LIMIT 4");
    $stmt->execute();
    $popular_products = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>


<style>
    body {
        font-family: 'Sarabun', sans-serif;
        background: #0f0f0f;
        /* พื้นหลังสีเข้มเพื่อให้สินค้าเด่น */
        color: white;
    }

    .popular-header {
        padding: 80px 0 40px;
        text-align: center;
    }

    .highlight-text {
        color: #f8a5c2;
        /* สีชมพูพาสเทลเอกลักษณ์ของร้าน */
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: bold;
    }

    /* Glass Card สำหรับสินค้า */
    .glass-item {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 25px;
        padding: 30px;
        transition: all 0.4s ease;
        text-align: center;
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .glass-item:hover {
        transform: translateY(-15px);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid #f8a5c2;
    }

    .item-img {
        width: 100%;
        height: 250px;
        object-fit: contain;
        filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.5));
        transition: 0.4s;
    }

    .glass-item:hover .item-img {
        transform: scale(1.1);
    }

    .badge-popular {
        position: absolute;
        top: 20px;
        left: 20px;
        background: linear-gradient(45deg, #f8a5c2, #f78fb3);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
    }

    .price-tag {
        font-size: 1.5rem;
        font-weight: bold;
        color: #f8a5c2;
        margin-top: 15px;
    }

    .btn-mira {
        background: transparent;
        border: 2px solid #f8a5c2;
        color: white;
        border-radius: 30px;
        padding: 8px 25px;
        margin-top: 20px;
        transition: 0.3s;
    }

    .btn-mira:hover {
        background: #f8a5c2;
        color: black;
    }

    /* ตกแต่งพื้นหลังด้วยแสงฟุ้ง */
    .glow {
        position: fixed;
        width: 300px;
        height: 300px;
        background: #f8a5c2;
        filter: blur(150px);
        opacity: 0.1;
        z-index: -1;
    }
</style>
</head>

<body>

    <div class="glow" style="top: 10%; left: 10%;"></div>
    <div class="glow" style="bottom: 10%; right: 10%;"></div>

    <div class="container mb-5">
        <div class="popular-header">
            <span class="highlight-text">Top Sellers</span>
            <h1 class="display-4 fw-bold">สิ้นค้ายอดนิยม</h1>
            <p class="text-white-50">สินค้าที่ได้รับความไว้วางใจและมียอดจำหน่ายสูงสุดในเดือนนี้</p>
        </div>

        <div class="row g-4">
            <?php foreach ($popular_products as $index => $row): ?>
                <div class="col-md-3">
                    <div class="glass-item shadow-lg">
                        <img src="photo/<?php echo $row['image']; ?>" class="item-img" alt="product">

                        <h4 class="mt-4 fw-bold text-truncate"><?php echo $row['product_name']; ?></h4>
                        <p class="text-white-50 small text-truncate"><?php echo $row['description']; ?></p>

                        <div class="price-tag"><?php echo number_format($row['price']); ?> ฿</div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="product_detail.php?id=<?php echo $row['product_id']; ?>" class="btn btn-mira">
                        สั่งซื้อตอนนี้
                    </a> 
                <?php else: ?>
                    <a href="javascript:void(0)" class="btn btn-mira" data-bs-toggle="modal" data-bs-target="#loginAlertModal">
                        สั่งซื้อตอนนี้
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; // <--- จุดที่มักจะลืมปิดตรงนี้ครับ ?>
</div>
    </div>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>

    <div class="modal fade" id="loginAlertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-person-circle" style="font-size: 4rem; color: #b3365b;"></i>
                    </div>

                    <h4 class="fw-bold mb-3" style="color: #b3365b;">กรุณาเข้าสู่ระบบ</h4>
                   <p class="text-muted mb-4">คุณต้องเข้าสู่ระบบก่อนจึงจะสามารถเลือกซื้อสินค้าและทำรายการสั่งซื้อได้</p>

                    <div class="d-grid gap-2">
                        <a href="login/login.php" class="btn py-2 fw-bold shadow-sm"
                            style="background-color: #b3365b; color: white; border-radius: 50px;">
                            ไปที่หน้าเข้าสู่ระบบ
                        </a>
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">
                            ไว้ทีหลัง
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>