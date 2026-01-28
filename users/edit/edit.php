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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --mira-pink: #f8a5c2;
            --mira-dark-pink: #b3365b; /* สีหัวข้อตามรูปที่ส่งมา */
            --mira-bg: #fff0f5; /* โทนชมพูอ่อนมากสำหรับพื้นหลัง */
        }

        body {
            background-color: var(--mira-bg);
            font-family: 'Sarabun', sans-serif;
            color: #5d5d5d;
        }

        .mira-header {
            font-family: 'Playfair Display', serif;
            color: var(--mira-dark-pink);
            letter-spacing: 1px;
        }

        /* Glassmorphism Panel เหมือนในรูป Customer Directory */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(179, 54, 91, 0.05);
        }

        /* Table Style */
        .cart-table {
            border-collapse: separate;
            border-spacing: 0 15px;
        }
        .cart-table thead th {
            color: var(--mira-dark-pink);
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 0 15px;
        }
        .product-row {
            background: white;
            border-radius: 15px;
            transition: 0.3s;
        }
        .product-row td {
            padding: 20px 15px;
            border: none;
        }
        .product-row td:first-child { border-radius: 15px 0 0 15px; }
        .product-row td:last-child { border-radius: 0 15px 15px 0; }

        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            background: #f9f9f9;
        }

        /* Quantity Control */
        .qty-control {
            display: inline-flex;
            align-items: center;
            background: var(--mira-bg);
            border-radius: 50px;
            padding: 5px 15px;
        }
        .qty-btn {
            color: var(--mira-dark-pink);
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .qty-input {
            width: 40px;
            border: none;
            background: transparent;
            text-align: center;
            font-weight: 600;
        }

        .btn-remove {
            color: #ff4757;
            background: rgba(255, 71, 87, 0.1);
            padding: 8px;
            border-radius: 10px;
            transition: 0.3s;
        }
        .btn-remove:hover { background: #ff4757; color: white; }

        /* Summary Sidebar */
        .summary-card {
            background: white;
            border-radius: 25px;
            padding: 30px;
            border: 1px solid rgba(179, 54, 91, 0.1);
        }

        .btn-checkout {
            background: var(--mira-dark-pink);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 50px;
            width: 100%;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-checkout:hover {
            background: #8e2a48;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(179, 54, 91, 0.2);
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="mb-5">
        <h2 class="mira-header fw-bold">Shopping Bag</h2>
        <p class="text-muted small">บริหารจัดการรายการสินค้าที่คุณเลือกไว้</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="glass-panel">
                <?php if (empty($items)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bag-x mb-3 d-block" style="font-size: 3rem; color: #ddd;"></i>
                        <h5 class="text-muted">ไม่มีสินค้าในรถเข็น</h5>
                        <a href="../../perfume_forwomen/index_w.php" class="btn mt-3" style="color: var(--mira-dark-pink); border: 1px solid var(--mira-dark-pink); border-radius: 50px;">ไปที่หน้าสินค้า</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table cart-table align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th class="text-center">Quantity</th>
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
                                <tr class="product-row shadow-sm">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../photo/<?= htmlspecialchars($item['image']) ?>" class="product-img me-3">
                                            <div>
                                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($item['product_name']) ?></h6>
                                                <small class="text-muted">Item ID: #<?= $item['product_id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>฿<?= number_format($item['price'], 2) ?></td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <div class="qty-control">
                                                <a href="cart_action.php?action=update&id=<?= $item['product_id'] ?>&qty=<?= $qty - 1 ?>" class="qty-btn">-</a>
                                                <input type="text" value="<?= $qty ?>" class="qty-input" readonly>
                                                <a href="cart_action.php?action=update&id=<?= $item['product_id'] ?>&qty=<?= $qty + 1 ?>" class="qty-btn">+</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold" style="color: var(--mira-dark-pink);">฿<?= number_format($subtotal, 2) ?></td>
                                    <td class="text-center">
                                        <a href="cart_action.php?action=remove&id=<?= $item['product_id'] ?>" class="btn-remove" onclick="return confirm('ยืนยันการลบ?')">
                                            <i class="bi bi-trash"></i>
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
            <div class="summary-card shadow-sm">
                <h5 class="fw-bold mb-4" style="color: var(--mira-dark-pink);">Order Summary</h5>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">ยอดรวมสินค้า</span>
                    <span class="fw-600">฿<?= number_format($total_price, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">ค่าจัดส่ง</span>
                    <span class="text-success fw-bold">FREE</span>
                </div>
                <hr style="border-style: dashed;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="h6 mb-0">ยอดชำระสุทธิ</span>
                    <span class="h4 mb-0 fw-bold" style="color: var(--mira-dark-pink);">฿<?= number_format($total_price, 2) ?></span>
                </div>

                <button class="btn btn-checkout mb-3" onclick="window.location='checkout.php'">
                    ชำระเงินตอนนี้
                </button>
                
                <div class="text-center">
                    <a href="../index_users.php" class="text-decoration-none small text-muted">
                        <i class="bi bi-arrow-left me-1"></i> เลือกสินค้าเพิ่มเติม
                    </a>
                </div>
            </div>

            <div class="mt-4 p-3 border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, #f8a5c2 0%, #f78fb3 100%); color: white;">
                <div class="d-flex align-items-center">
                   
                    
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>