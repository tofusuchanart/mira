<?php 
// ไม่ต้อง include config.php ซ้ำหาก index.php เรียกไว้แล้ว
// แต่ถ้าเปิดหน้านี้แยกต่างหาก ต้องเช็คเผื่อไว้
if (!isset($conn)) {
    require_once "../config.php"; 
}

try {
    // ดึงสินค้าผู้หญิง และ Unisex
    $stmt = $conn->prepare("SELECT * FROM products WHERE sex IN ('female', 'unisex') ORDER BY product_id DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();
    $total_products = count($products);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<style>
    .product-banner {
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('perfume_forwomen/photo/bn.png'); 
        background-size: cover;
        background-position: center;
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-align: center;
        border-radius: 0 0 30px 30px; /* เพิ่มความโค้งมนให้เข้ากับธีม */
    }
    .product-card {
        border: none;
        background: white;
        transition: 0.3s;
        border-radius: 20px; /* ปรับให้โค้งมนเหมือนหน้า Cart View */
        padding: 20px;
        text-decoration: none !important;
        display: block;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .product-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(179, 54, 91, 0.1); }
    .product-img {
        width: 100%;
        height: 200px;
        object-fit: contain;
        margin-bottom: 15px;
    }
    .product-name { 
        font-size: 1rem; 
        color: #333; 
        font-weight: 600;
        height: 45px; 
        overflow: hidden; 
    }
    .product-price { color: #b3365b; font-weight: bold; font-size: 1.2rem; }
    .badge-unisex {
        background: #f8a5c2;
        color: white;
        font-size: 0.65rem;
        padding: 2px 10px;
        border-radius: 50px;
        position: absolute;
        top: 15px;
        right: 15px;
    }
</style>

<div class="product-banner mb-5">
    <div>
        <h1 class="display-4 fw-bold text-uppercase">🌼For WOMEN🌼</h1>
        <p class="mb-0">ค้นพบกลิ่นหอมที่สะท้อนตัวตนในแบบคุณ</p>
    </div>
</div>

<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0" style="color: #b3365b;">Women's Collection</h4>
        <div class="badge bg-light text-dark rounded-pill border shadow-sm px-3">
            พบ <?= $total_products ?> รายการ
        </div>
    </div>

    <div class="row g-4">
        <?php foreach($products as $row): ?>
<div class="col-6 col-md-4 col-lg-3 position-relative">
    <div class="product-card h-100" style="cursor: pointer;" 
         onclick="checkLoginRedirect('<?= $row['product_id'] ?>')">
        
        <?php if($row['sex'] == 'unisex'): ?>
            <span class="badge-unisex shadow-sm">Unisex</span>
        <?php endif; ?>
        
        <img src="/mira/photo/<?= htmlspecialchars($row['image']) ?>" class="product-img" alt="<?= $row['product_name'] ?>">
        
        <div class="product-name mb-2">
            <?= htmlspecialchars($row['product_name']) ?>
        </div>
        
        <div class="product-price">
            ฿<?= number_format($row['price'], 0) ?>
        </div>
    </div>
</div>
<?php endforeach; ?> 
</div> </div> 
    <div class="modal fade" id="loginAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 25px; border: none;">
            <div class="modal-body text-center p-5">
                <i class="bi bi-person-circle" style="font-size: 4rem; color: #b3365b;"></i>
                <h4 class="fw-bold mb-3">กรุณาเข้าสู่ระบบ</h4>
                <p class="text-muted mb-4">คุณต้องเข้าสู่ระบบสมาชิกก่อนจึงจะดูรายละเอียดได้</p>
                <div class="d-grid gap-2">
                    <a href="/mira/login/login.php" class="btn btn-primary" style="background:#b3365b; border-radius:50px;">เข้าสู่ระบบ</a>
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">ไว้ทีหลัง</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkLoginRedirect(productId) {
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    if (isLoggedIn) {
        window.location.href = "/mira/perfume_formen/product_detail.php?id=" + productId;
    } else {
        var myModal = new bootstrap.Modal(document.getElementById('loginAlertModal'));
        myModal.show();
    }
}
</script>
<div class="modal fade" id="loginAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 25px; border: none;">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <i class="bi bi-person-circle" style="font-size: 4rem; color: #b3365b;"></i>
                </div>
                <h4 class="fw-bold mb-3" style="color: #b3365b;">กรุณาเข้าสู่ระบบ</h4>
                <p class="text-muted mb-4">คุณต้องเข้าสู่ระบบสมาชิกก่อน <br>จึงจะสามารถดูรายละเอียดสินค้าและสั่งซื้อได้</p>
                
                <div class="d-grid gap-2">
                    <a href="/mira/login/login.php" class="btn py-3 fw-bold shadow-sm" 
                       style="background-color: #b3365b; color: white; border-radius: 50px;">
                        เข้าสู่ระบบตอนนี้
                    </a>
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">
                        ไว้ทีหลัง
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>