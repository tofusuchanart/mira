<?php
session_start();
require_once "../../config.php"; 

// ตรวจสอบสิทธิ์ Owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

$status_msg = "";

// ส่วนการอัปเดตสถานะการสั่งซื้อ
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    if ($update_stmt->execute([$new_status, $order_id])) {
        $status_msg = "อัปเดตสถานะออเดอร์ #$order_id เรียบร้อยแล้วค่ะ ✨";
    }
}

// ดึงรายการสั่งซื้อทั้งหมด JOIN กับผู้ใช้ และดึงรูปหลักฐานการโอน (ถ้ามี)
$sql = "SELECT o.*, u.fullname, u.email, p.payment_id, p.payment_method 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        LEFT JOIN payments p ON o.order_id = p.order_id 
        ORDER BY o.order_date DESC";
$stmt = $conn->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | MIRA Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --mira-pink-dark: #a34a67;
            --mira-pink-soft: #fdf5f7;
            --mira-bg: #fff0f5;
        }

        body { background-color: var(--mira-bg); font-family: 'Sarabun', sans-serif; }
        .mira-header { font-family: 'Playfair Display', serif; color: var(--mira-pink-dark); }
        
        .admin-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(163, 74, 103, 0.05);
        }

        /* Status Badge Styling */
        .badge-status {
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d1e7dd; color: #0f5132; }
        .status-shipped { background: #cfe2ff; color: #084298; }
        .status-completed { background: #fdf5f7; color: var(--mira-pink-dark); border: 1px solid var(--mira-pink-dark); }
        .status-cancelled { background: #f8d7da; color: #842029; }

        .table { vertical-align: middle; }
        .order-row:hover { background: var(--mira-pink-soft); transition: 0.3s; }
        
        .btn-edit-status {
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 5px 10px;
            transition: 0.3s;
        }
        .btn-edit-status:hover { border-color: var(--mira-pink-dark); color: var(--mira-pink-dark); }
        
        .nav-back { color: var(--mira-pink-dark); text-decoration: none; font-weight: 600; }



        .btn-light {
    background-color: #fdf5f7;
    border: 1px solid rgba(163, 74, 103, 0.1);
    color: var(--mira-pink-dark);
}
.btn-light:hover {
    background-color: var(--mira-pink-dark);
    color: white;
}
    </style>
</head>
<body>

<div class="container py-5">
    <a href="../index_ad.php" class="nav-back mb-4 d-inline-block">
        <i class="bi bi-arrow-left me-2"></i> กลับหน้าDashboard
    </a>

    <div class="admin-card">
        <h2 class="mira-header fw-bold mb-4">จัดการคำสั่งซื้อ</h2>

        <?php if ($status_msg): ?>
            <script>Swal.fire({ icon: 'success', title: 'สำเร็จ', text: '<?= $status_msg ?>', confirmButtonColor: '#a34a67' });</script>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr class="text-muted small uppercase">
                        <th>ออเดอร์ ID</th>
                        <th>วันที่สั่งซื้อ</th>
                        <th>ลูกค้า</th>
                        <th>ยอดรวม</th>
                        <th>สถานะปัจจุบัน</th>
                        <th>หลักฐาน</th><th class="text-center">จัดการ</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr class="order-row">
                        <td><strong>#<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                        <td class="small"><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($order['fullname']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($order['email']) ?></div>
                        </td>
                        <td class="fw-bold text-dark">฿<?= number_format($order['total_price'], 2) ?></td>
                        <td>
                            <span class="badge-status status-<?= $order['status'] ?>">
                                <?= strtoupper($order['status']) ?>
                            </span>
                        </td>
                           
                        <td>
    <?php if ($order['payment_id']): ?>
        <button type="button" class="btn btn-sm btn-light rounded-pill px-3" 
        onclick="viewSlip('<?= $order['order_id'] ?>', '<?= $order['payment_proof'] ?>')"> 
    <i class="bi bi-file-earmark-image text-primary"></i> ดูสลิป
</button>
    <?php else: ?>
        <span class="text-muted small">ยังไม่แจ้งชำระ</span>
    <?php endif; ?>
</td>



                        <td class="text-center">
                            <form action="" method="POST" class="d-flex gap-2 justify-content-center">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                <select name="new_status" class="form-select form-select-sm border-0 bg-light" style="width: 130px; border-radius: 10px;">
                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="paid" <?= $order['status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-dark rounded-pill px-3">
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

<script>
function viewSlip(orderId, imgName) {
    Swal.fire({
        title: 'หลักฐานการโอนเงิน #' + orderId,
        imageUrl: '../uploads/payments/' + imgName, // ปรับ Path โฟลเดอร์ที่เก็บสลิปของคุณ
        imageAlt: 'Slip Payment',
        confirmButtonColor: '#a34a67',
        confirmButtonText: 'ปิดหน้าต่าง',
        borderRadius: '20px'
    });
}
</script>
</body>
</html>