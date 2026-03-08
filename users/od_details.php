<?php
session_start();
require_once "../config.php"; 

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: od_htr.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'];

// 1. ดึงข้อมูลสรุปของคำสั่งซื้อนี้ (เช็ค user_id เพื่อความปลอดภัย)
$stmt_order = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt_order->execute([$order_id, $user_id]);
$order = $stmt_order->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: od_htr.php");
    exit();
}

// 2. ดึงรายการสินค้าในคำสั่งซื้อนี้
$sql_items = "SELECT oi.*, p.product_name, p.image 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.product_id 
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
// 2. ดึงรายการสินค้าในคำสั่งซื้อนี้ + เช็คสถานะการรีวิว
// ดึงรายการสินค้า + เช็คว่า "ออเดอร์นี้" สินค้าชิ้นนี้รีวิวไปหรือยัง
$sql_items = "SELECT oi.*, p.product_name, p.image, r.review_id 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.product_id 
              LEFT JOIN reviews r ON (oi.product_id = r.product_id 
                                     AND r.user_id = ? 
                                     AND r.order_id = ?) 
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->execute([$user_id, $order_id, $order_id]); 
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?= $order_id ?> | MIRA</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="photo/golo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --mira-pink-dark: #a34a67;
            --mira-pink-soft: #fdf5f7;
            --mira-pink-accent: #f8a5c2;
            --mira-bg: #fff0f5;
        }

        body {
            background-color: var(--mira-bg);
            font-family: 'Sarabun', sans-serif;
            color: #5d5d5d;
        }

        .mira-header {
            font-family: 'Playfair Display', serif;
            color: var(--mira-pink-dark);
        }

        .details-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            border: none;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(163, 74, 103, 0.05);
        }

        .order-status-bar {
            background: var(--mira-pink-dark);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 15px;
            background: #fdf5f7;
        }

        .item-row {
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }

        .item-row:last-child { border-bottom: none; }

        .price-label {
            color: var(--mira-pink-dark);
            font-weight: 600;
        }

        .summary-box {
            background: var(--mira-pink-soft);
            border-radius: 20px;
            padding: 25px;
        }

        .nav-back {
            color: var(--mira-pink-dark);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .status-dot {
            height: 10px;
            width: 10px;
            background-color: #fff;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            box-shadow: 0 0 8px rgba(255,255,255,0.8);
        }

.btn-review {
    background-color: var(--mira-pink-dark);
    color: white;
    transition: all 0.3s ease;
    border: none;
}

.btn-review:hover {
    background-color: #8a3d56;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(163, 74, 103, 0.2);
}

.text-success-mira {
    color: #28a745;
    font-size: 0.85rem;
    font-weight: 600;
}
</style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <a href="od_htr.php" class="nav-back">
                <i class="bi bi-arrow-left me-2"></i> ย้อนกลับไปประวัติการสั่งซื้อ
            </a>

            <div class="details-card shadow-sm">
                <div class="order-status-bar">
                    <div>
                        <small class="d-block opacity-75">หมายเลขคำสั่งซื้อ</small>
                        <h4 class="mb-0 fw-bold">#<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></h4>
                    </div>
                    <div class="text-end">
                        <span class="status-dot"></span>
                        <span class="text-uppercase fw-bold" style="letter-spacing: 1px;">
                            <?php 
                                $s = $order['status'];
                                if($s == 'pending') echo "รอการตรวจสอบ";
                                elseif($s == 'paid') echo "ชำระเงินแล้ว";
                                elseif($s == 'shipped') echo "กำลังจัดส่ง";
                                elseif($s == 'completed') echo "รายการสำเร็จ";
                                else echo "ยกเลิกแล้ว";
                            ?>
                        </span>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <h5 class="mira-header fw-bold mb-4">สินค้าที่สั่ง</h5>
                    
                  <?php foreach ($items as $item): ?>
<div class="row item-row align-items-center">
    <div class="col-3 col-md-2">
        <img src="../photo/<?= $item['image'] ?>" class="product-img shadow-sm" alt="product">
    </div>
    <div class="col-6 col-md-7">
        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($item['product_name']) ?></h6>
        <small class="text-muted">จำนวน: <?= $item['quantity'] ?> ชิ้น</small>
    </div>
    <div class="col-3 col-md-3 text-end">
        <span class="price-label d-block mb-2">฿<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
        
        <?php if ($order['status'] == 'completed'): ?>
            <?php if (empty($item['review_id'])): ?>
                <button type="button" 
                        class="btn btn-sm rounded-pill px-3" 
                        style="background-color: var(--mira-pink-dark); color: white;"
                        onclick="openReviewModal(<?= $item['product_id'] ?>, '<?= htmlspecialchars($item['product_name']) ?>')">
                    <i class="bi bi-pencil-square"></i> รีวิวสินค้า
                </button>
            <?php else: ?>
                <span class="badge rounded-pill bg-light text-success border">รีวิวแล้ว</span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

                    <div class="row mt-5">
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold mb-3">ข้อมูลการจัดส่ง</h6>
                            <p class="text-muted small">
                                <i class="bi bi-calendar3 me-2"></i> สั่งซื้อเมื่อ: <?= date('d F Y (H:i)', strtotime($order['order_date'])) ?><br>
                                <i class="bi bi-credit-card me-2"></i> ชำระเงินผ่าน: โอนผ่านธนาคาร
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-box">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>ยอดรวมสินค้า</span>
                                    <span>฿<?= number_format($order['total_price'], 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>ค่าจัดส่ง</span>
                                    <span class="text-success">Free</span>
                                </div>
                                <hr style="opacity: 0.1;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">ยอดรวมสุทธิ</span>
                                    <span class="h4 mb-0 fw-bold" style="color: var(--mira-pink-dark);">฿<?= number_format($order['total_price'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-5">
                        
                        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                            <i class="bi bi-printer me-2"></i> พิมพ์ใบเสร็จ
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <div class="modal-content" style="border-radius: 25px; border: none;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" style="color: #b3365b;">
                    <i class="bi bi-stars me-2"></i> รีวิวสินค้า: <span id="modal_product_name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="submit_review.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="modal_product_id">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>"> <div class="mb-4 text-center bg-light p-3" style="border-radius: 15px;">
                        <label class="form-label d-block fw-bold mb-3">คะแนนความพึงพอใจ</label>
                        <select name="rating" class="form-select w-auto mx-auto border-0 shadow-sm" style="border-radius: 10px;" required>
                            <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                            <option value="4">⭐⭐⭐⭐ (4/5)</option>
                            <option value="3">⭐⭐⭐ (3/5)</option>
                            <option value="2">⭐⭐ (2/5)</option>
                            <option value="1">⭐ (1/5)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ข้อความรีวิวของคุณ</label>
                        <textarea name="comment" class="form-control border-0 bg-light" rows="4" 
                                  style="border-radius: 15px;" placeholder="บอกเราหน่อยว่าคุณชอบสินค้านี้อย่างไร..." required></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">แนบรูปภาพสินค้า</label>
                            <input type="file" name="review_image" class="form-control" accept="image/*" style="border-radius: 10px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">แนบวิดีโอสินค้า</label>
                            <input type="file" name="review_video" class="form-control" accept="video/*" style="border-radius: 10px;">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn py-3" style="background-color: #b3365b; color: white; border-radius: 50px; font-weight: bold;">
                            ส่งรีวิวและวิดีโอของคุณ <i class="bi bi-send-fill ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
<script>
function openReviewModal(productId, productName) {
    document.getElementById('modal_product_id').value = productId;
    document.getElementById('modal_product_name').innerText = productName;
    var myModal = new bootstrap.Modal(document.getElementById('reviewModal'));
    myModal.show();
}
</script>

</body>
</html>