<?php
session_start();
require_once "../../config.php"; 

// ตรวจสอบสิทธิ์ Owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

$status_msg = "";

// --- ส่วนการอัปเดตสถานะการสั่งซื้อที่ปรับปรุงใหม่ ---
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];

    // ดึงสถานะปัจจุบันมาเทียบก่อนเพื่อไม่ให้ตัดสต็อกซ้ำ
    $check_sql = "SELECT status FROM orders WHERE order_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$order_id]);
    $old_status = $check_stmt->fetchColumn();

    $conn->beginTransaction(); // ใช้ Transaction เพื่อป้องกันข้อมูลผิดพลาด

    try {
        // อัปเดตสถานะหลัก
        $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $update_stmt->execute([$new_status, $order_id]);

        // LOGIC: จัดส่งแล้วสินค้าออกจากคลัง
        if ($new_status === 'shipped' && $old_status !== 'shipped') {
            $items_stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $items_stmt->execute([$order_id]);
            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
                $stock_stmt->execute([$item['quantity'], $item['product_id']]);
                
                $log_stmt = $conn->prepare("INSERT INTO stock_log (product_id, type, quantity, remark) VALUES (?, 'out', ?, ?)");
                $log_stmt->execute([$item['product_id'], $item['quantity'], "ตัดสต็อกอัตโนมัติจากออเดอร์ #$order_id"]);
            }
        }

        // LOGIC: ยกเลิกสินค้าแล้วคืนคลัง
        if ($new_status === 'cancelled' && $old_status === 'shipped') {
            $items_stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $items_stmt->execute([$order_id]);
            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $stock_stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
                $stock_stmt->execute([$item['quantity'], $item['product_id']]);

                $log_stmt = $conn->prepare("INSERT INTO stock_log (product_id, type, quantity, remark) VALUES (?, 'in', ?, ?)");
                $log_stmt->execute([$item['product_id'], $item['quantity'], "คืนสต็อกจากการยกเลิกออเดอร์ #$order_id"]);
            }
        }

        $conn->commit();
        $status_msg = "จัดการออเดอร์และอัปเดตสต็อกเรียบร้อยแล้วค่ะ ✨";
    } catch (Exception $e) {
        $conn->rollBack();
        $status_msg = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// --- ส่วนการดึงข้อมูลเพื่อแสดงผลในตาราง (แก้ปัญหา Undefined variable $orders) ---
$sql = "SELECT o.*, u.fullname, u.email, p.payment_id, p.payment_method, p.payment_proof 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        LEFT JOIN payments p ON o.order_id = p.order_id 
        ORDER BY o.order_date DESC";
$stmt = $conn->query($sql);

$orders = []; // กำหนดเป็น array ว่างไว้ก่อนเพื่อป้องกัน Error
if ($stmt instanceof PDOStatement) {
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | MIRA Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@200;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../photo_ad/golo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            /* ปรับ Palette สีตามรูปที่แนบ */
            --mira-pink-primary: #b3365b;
            --mira-pink-soft: #fff5f7;
            --mira-text-muted: #94a3b8;
            --mira-bg-card: #ffffff;
        }

        body { 
            background-color: var(--mira-pink-soft); 
            font-family: 'Sarabun', sans-serif; 
            color: #4a4a4a;
        }

        /* ปุ่มย้อนกลับตามภาพที่แนบ */
        .nav-back { 
            text-decoration: none;
            color: var(--mira-text-muted); 
            font-size: 0.95rem;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .nav-back:hover { color: var(--mira-pink-primary); }

        /* หัวข้อหน้า */
        .mira-header { 
            color: var(--mira-pink-primary); 
            font-weight: 600;
            letter-spacing: -1px;
        }

        /* การ์ดหลักขาวสะอาด ขอบมนแบบในรูป */
        .admin-card {
            background: var(--mira-bg-card);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(179, 54, 91, 0.05);
            border: none;
        }

        /* ตารางสไตล์ Minimal */
        .table { 
            border-collapse: separate;
            border-spacing: 0;
            vertical-align: middle; 
        }
        .table thead th {
            border: none;
            color: var(--mira-text-muted);
            font-weight: 400;
            font-size: 0.85rem;
            text-transform: none;
            padding-bottom: 20px;
        }
        .table tbody td {
            border-top: 1px solid #f8f9fa;
            padding: 20px 10px;
        }
        .order-row:hover { background-color: #fafafa; }

        /* Status Badge แบบพาสเทล */
        .badge-status {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #e8f5e9; color: #2e7d32; }
        .status-shipped { background: #e3f2fd; color: #1565c0; }
        .status-completed { background: #fff0f3; color: var(--mira-pink-primary); border: 1px solid #f8d7da; }
        .status-cancelled { background: #ffebee; color: #c62828; }

        /* ปุ่ม "ดูสลิป" ตามสไตล์ในรูป */
        .btn-view-slip {
            background-color: #fff0f3;
            color: var(--mira-pink-primary);
            border: 1px solid #f8d7da;
            border-radius: 50px;
            font-size: 0.8rem;
            padding: 5px 15px;
            transition: 0.3s;
        }
        .btn-view-slip:hover {
            background-color: var(--mira-pink-primary);
            color: white;
        }

        /* ปุ่มบันทึก */
        .btn-save {
            background-color: var(--mira-pink-primary);
            border: none;
            border-radius: 50px;
            font-size: 0.85rem;
            padding: 6px 20px;
            transition: 0.3s;
        }
        .btn-save:hover { background-color: #8e2b48; transform: translateY(-1px); }

        .form-select-custom {
            border-radius: 10px;
            font-size: 0.85rem;
            border: 1px solid #eee;
            background-color: #fcfcfc;
        }

    </style>
</head>
<body>

<div class="container py-5">
    <div class="mb-2">
        <a href="../index_ad.php" class="nav-back">
            <i class="bi bi-arrow-left"></i> กลับสู่หน้า Dashboard
        </a>
    </div>

    <div class="text-start mb-5">
        <h2 class="mira-header fw-bold mb-1">จัดการคำสั่งซื้อ</h2>
        <p class="text-muted small">ตรวจสอบและอัปเดตสถานะออเดอร์ Mira ของคุณ</p>
    </div>

    <div class="admin-card">
        <?php if ($status_msg): ?>
            <script>Swal.fire({ icon: 'success', title: 'สำเร็จ', text: '<?= $status_msg ?>', confirmButtonColor: '#b3365b' });</script>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th width="15%">ออเดอร์ ID</th>
                        <th width="15%">วันที่สั่งซื้อ</th>
                        <th width="25%">ข้อมูลลูกค้า</th>
                        <th width="15%">ยอดรวม</th>
                        <th width="15%">สถานะ</th>
                        <th width="15% text-center">การจัดการ</th>
                    </tr>
                </thead>
             <tbody>
    <?php foreach ($orders as $order): ?>
    <tr class="order-row">
        <td><span class="fw-bold" style="color:#b3365b;">#<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></span></td>
        <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
        <td>
            <div class="fw-bold" style="font-size: 0.95rem;"><?= htmlspecialchars($order['fullname']) ?></div>
            <div class="text-muted small"><?= htmlspecialchars($order['email']) ?></div>
        </td>
        <td class="fw-bold">฿<?= number_format($order['total_price'], 2) ?></td>
        <td>
            <span class="badge-status status-<?= $order['status'] ?>">
                <?= strtoupper($order['status']) ?>
            </span>
        </td>
        <td class="text-center">
            <?php 
            // กรณีที่ 1: เป็นการชำระแบบเก็บเงินปลายทาง
            if ($order['payment_method'] === 'Cash on Delivery'): ?>
                <div class="mb-2">
                    <span class="badge bg-info text-dark rounded-pill shadow-sm" style="font-size: 0.75rem; padding: 6px 12px;">
                        <i class="bi bi-truck me-1"></i> เก็บเงินปลายทาง
                    </span>
                </div>

            <?php 
            // กรณีที่ 2: เป็นการโอนเงินและมีสลิป
            elseif ($order['payment_method'] === 'Bank Transfer' && $order['payment_proof']): ?>
                <button type="button" class="btn btn-view-slip mb-2 w-100" 
                    onclick="viewSlip('<?= $order['order_id'] ?>', '<?= $order['payment_proof'] ?>')"> 
                    <i class="bi bi-file-earmark-image me-1"></i> ดูสลิป (โอนเงิน)
                </button>

            <?php 
            // กรณีที่ 3: เลือกโอนเงินแต่ยังไม่แนบสลิป หรือกรณีอื่นๆ
            else: ?>
                <div class="small text-danger mb-2">
                    <i class="bi bi-exclamation-circle me-1"></i> ยังไม่แจ้งชำระ
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="d-flex flex-column gap-2">
                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                <select name="new_status" class="form-select form-select-sm form-select-custom">
                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>รอดำเนินการ</option>
                    <option value="paid" <?= $order['status'] == 'paid' ? 'selected' : '' ?>>จ่ายแล้ว</option>
                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>จัดส่งแล้ว</option>
                    <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>เสร็จสิ้น</option>
                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>ยกเลิก</option>
                </select>
                <button type="submit" name="update_status" class="btn btn-sm btn-dark btn-save">
                    บันทึก
                </button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>
            </table>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
function viewSlip(orderId, imgName) {
    if (!imgName || imgName === '') {
        Swal.fire('ไม่พบไฟล์', 'ออเดอร์นี้ไม่มีไฟล์สลิปแนบมาค่ะ', 'error');
        return;
    }
    Swal.fire({
        title: 'หลักฐานการโอนเงิน #' + orderId,
        // ลองเปลี่ยน ../ เป็น ../../ ถ้าไฟล์นี้อยู่ในโฟลเดอร์ย่อย 2 ชั้น
        imageUrl: '../../uploads/slips/' + imgName, 
        imageWidth: 400,
        imageAlt: 'Slip Payment',
        confirmButtonColor: '#a34a67',
        confirmButtonText: 'ปิดหน้าต่าง'
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</body>
</html>