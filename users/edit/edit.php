<?php
session_start();
require_once "../../config.php"; // ตรวจสอบ path ไฟล์เชื่อมต่อฐานข้อมูลให้ถูกต้อง

$total_price = 0;
$items = [];

// ดึงข้อมูลสินค้าจากฐานข้อมูลตาม ID ที่อยู่ใน Session
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    try {
        $sql = "SELECT * FROM products WHERE product_id IN ($ids)";
        $stmt = $conn->query($sql);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIRA | Your Shopping Bag</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&family=Itim&display=swap" rel="stylesheet">
    <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --mira-pink-soft: #ffecf2;
            --mira-pink-medium: #f8a5c2;
            --mira-dark-pink: #a34a67; /* ปรับให้เข้มขึ้นนิดนึงเพื่อให้ใกล้เคียงรูป Directory */
            --mira-bg: #fdf5f7; 
        }

        body {
            background-color: var(--mira-bg);
            font-family: 'Sarabun', sans-serif;
            color: #5d5d5d;
        }

        .mira-header {
            font-family: 'Playfair Display', serif;
            color: var(--mira-dark-pink);
            letter-spacing: -0.5px;
        }

        /* ปรับ Panel ให้ดูคลีนและลอยตัวมากขึ้น */
        .glass-panel {
            background: white;
            border: none;
            border-radius: 30px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(163, 74, 103, 0.04);
        }

        /* ซ่อนขอบตารางเพื่อให้ดู Minimal */
        .cart-table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        
        .cart-table thead th {
            color: #b2b2b2;
            border: none;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding-bottom: 20px;
        }

        .product-row {
            background: #fff;
            transition: all 0.3s ease;
        }

        /* เส้นคั่นบางๆ ระหว่างแถว */
        .product-row td {
            border-top: 1px solid #f9f0f2 !important;
            padding: 25px 10px;
        }

        .product-img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 20px; /* ขอบมนมากขึ้น */
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }

        /* ตัวควบคุมจำนวนแบบเม็ดแคปซูล */
        .qty-control {
            display: inline-flex;
            align-items: center;
            background: #fdf5f7;
            border-radius: 100px;
            padding: 2px 10px;
            border: 1px solid #f3e4e8;
        }
        
        .qty-btn {
            color: var(--mira-dark-pink);
            text-decoration: none;
            font-size: 1.1rem;
            padding: 0 8px;
            transition: 0.2s;
        }
        
        .qty-btn:hover { color: var(--mira-pink-medium); }

        .qty-input {
            width: 35px;
            border: none;
            background: transparent;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .btn-remove {
            color: #d1d1d1;
            transition: 0.3s;
            font-size: 1.2rem;
        }
        .btn-remove:hover { color: #ff6b6b; }

        /* Summary Sidebar */
        .summary-card {
            background: white;
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 20px 40px rgba(163, 74, 103, 0.06);
            border: none;
        }

        .btn-checkout {
            background: var(--mira-dark-pink);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 20px;
            width: 100%;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.4s;
        }

        .btn-checkout:hover {
            background: #8e3e58;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(163, 74, 103, 0.2);
            color: white;
        }

        /* ตกแต่งตัวเลขให้น่ารัก */
        .price-text {
            font-weight: 600;
            color: var(--mira-dark-pink);
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="mira-header fw-bold text-uppercase" style="font-size: 2.5rem;">ตะกร้าของฉัน</h2>
        <div class="mx-auto" style="width: 40px; height: 3px; background: var(--mira-dark-pink); border-radius: 10px;"></div>
    </div>

    <div class="row g-4 justify-content-center">
        <div class="col-lg-8">
            <div class="glass-panel">
                <?php if (empty($items)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bag-heart mb-3 d-block" style="font-size: 4rem; color: #f3e4e8;"></i>
                        <h5 class="text-muted fw-light">ตะกร้ายังว่างอยู่เลยค่ะ...</h5>
                        <a href="../index_users.php" class="btn mt-4 px-4" style="color: var(--mira-dark-pink); border: 1px solid var(--mira-dark-pink); border-radius: 50px;">ไปเลือกสินค้ากันเถอะ</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table cart-table align-middle border-0">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">Product</th>
                                    <th>Price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): 
                                    $qty = $_SESSION['cart'][$item['product_id']];
                                    $subtotal = $item['price'] * $qty;
                                    $total_price += $subtotal;
                                ?>
                                <tr class="product-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../photo/<?= htmlspecialchars($item['image']) ?>" class="product-img me-3">
                                            <div>
                                                <h6 class="fw-bold mb-1" style="color: #444;"><?= htmlspecialchars($item['product_name']) ?></h6>
                                                <span class="badge bg-light text-muted fw-normal" style="font-size: 0.65rem;">#<?= $item['product_id'] ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="small text-muted">฿</span><?= number_format($item['price'], 2) ?></td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <div class="qty-control">
                                                <a href="cart_action.php?action=update&id=<?= $item['product_id'] ?>&qty=<?= $qty - 1 ?>" class="qty-btn">-</a>
                                                <input type="text" value="<?= $qty ?>" class="qty-input" readonly>
                                                <a href="cart_action.php?action=update&id=<?= $item['product_id'] ?>&qty=<?= $qty + 1 ?>" class="qty-btn">+</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end price-text">฿<?= number_format($subtotal, 2) ?></td>
                                    <td class="text-center">
                                        <a href="cart_action.php?action=remove&id=<?= $item['product_id'] ?>" class="btn-remove" onclick="return confirm('นำสินค้าออกจากตะกร้าใช่ไหมคะ?')">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-card">
                <h5 class="fw-bold mb-4" style="color: var(--mira-dark-pink); font-family: 'Playfair Display', serif;">สรุปการสั่งซื้อ</h5>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted small">ยอดรวมสินค้า</span>
                    <span class="fw-bold" style="color: #666;">฿<?= number_format($total_price, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted small">ค่าจัดส่ง</span>
                    <span class="text-success small fw-bold">FREE SHIPPING</span>
                </div>
                
                <div class="my-4" style="border-top: 2px dashed #fdf5f7;"></div>
                
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <span class="h6 mb-0 fw-bold">ยอมรวมทั้งหมด</span>
                    <span class="h3 mb-0 fw-bold" style="color: var(--mira-dark-pink);">฿<?= number_format($total_price, 2) ?></span>
                </div>

                <button class="btn btn-checkout mb-4" onclick="window.location='checkout.php'">
                    สั่งซื้อสินค้าเลย
                </button>
                <div class="text-center">
                    <a href="../index_users.php" class="text-decoration-none small text-muted hover-link">
                        <i class="bi bi-arrow-left-short"></i> Continue Shopping
                    </a>
                </div>
                
            </div>

            <div class="mt-4 p-4 text-center" style="background: #fff; border-radius: 25px; border: 2px dashed var(--mira-pink-medium);">
                <p class="mb-0 small" style="color: var(--mira-dark-pink);">✨ รับฟรี! ตัวทดลองทุก order</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>