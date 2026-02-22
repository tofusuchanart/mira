<?php
session_start();
require_once "../../config.php"; // ปรับ Path ตามจริงของคุณ
/** @var PDO $conn */

// รับค่า Order ID
$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header("Location: order_history.php");
    exit();
}

// 1. ดึงข้อมูลคำสั่งซื้อและข้อมูลลูกค้า
$sql_order = "SELECT o.*, u.fullname, u.email, u.phone, u.address, p.payment_method, p.payment_status 
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.user_id
              LEFT JOIN payments p ON o.order_id = p.order_id
              WHERE o.order_id = :order_id";
$stmt = $conn->prepare($sql_order);
$stmt->execute([':order_id' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("ไม่พบข้อมูลคำสั่งซื้อ");
}

// 2. ดึงรายการสินค้าในคำสั่งซื้อ
$sql_items = "SELECT oi.*, pr.product_name, pr.image 
              FROM order_items oi
              JOIN products pr ON oi.product_id = pr.product_id
              WHERE oi.order_id = :order_id";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->execute([':order_id' => $order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// ฟังก์ชัน Badge สีสถานะ
function getStatusBadge($status) {
    $badges = [
        'pending'   => '<span class="badge badge-pending">รอชำระเงิน</span>',
        'paid'      => '<span class="badge badge-paid">ชำระเงินแล้ว</span>',
        'shipped'   => '<span class="badge badge-shipped">กำลังจัดส่ง</span>',
        'completed' => '<span class="badge badge-success">สำเร็จ</span>',
        'cancelled' => '<span class="badge badge-danger">ยกเลิก</span>'
    ];
    return $badges[$status] ?? $status;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>MIRA | รายละเอียดคำสั่งซื้อ #<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { 
            --mira-pink: #b3365b; 
            --mira-soft-pink: #fdf2f4; 
            --mira-text: #4a4a4a; 
            --mira-light-gray: #f9fafb;
        }
        body { 
            background-color: var(--mira-soft-pink); 
            font-family: 'Sarabun', sans-serif; 
            color: var(--mira-text); 
        }
        .mira-card { 
            background: white; 
            border-radius: 1.5rem; 
            border: none; 
            box-shadow: 0 10px 30px rgba(179, 54, 91, 0.05); 
            padding: 30px; 
            margin-bottom: 25px;
        }
        .section-title {
            color: var(--mira-pink);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .back-link {
            transition: 0.3s;
            color: #8e9aaf !important;
            margin-bottom: 15px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .back-link:hover {
            color: var(--mira-pink) !important;
            transform: translateX(-5px);
        }
        .badge { border-radius: 50px; padding: 8px 16px; font-weight: 500; }
        .badge-pending { background: #fff7ed; color: #c2410c; }
        .badge-paid { background: #eef2ff; color: #4338ca; }
        .badge-shipped { background: #fefce8; color: #a16207; }
        .badge-success { background: #f0fdf4; color: #15803d; }
        .badge-danger { background: #fef2f2; color: #b91c1c; }

        .product-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
        }
        .table thead th {
            background: var(--mira-light-gray);
            border: none;
            color: #94a3b8;
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 15px;
        }
        .info-label { color: #94a3b8; font-size: 0.9rem; margin-bottom: 2px; }
        .info-value { font-weight: 600; color: var(--mira-text); margin-bottom: 15px; }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed #eee;
            color: var(--mira-pink);
            font-weight: 700;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <a href="order_history.php" class="text-decoration-none back-link small">
        <i class="bi bi-arrow-left"></i> กลับสู่หน้าจัดการคำสั่งซื้อ
    </a>

    <div class="row">
        <div class="col-lg-8">
            <div class="mira-card">
                <div class="section-title">
                    <i class="bi bi-box-seam"></i> รายการสินค้า
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>สินค้า</th>
                                <th></th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-end">ราคา/ชิ้น</th>
                                <th class="text-end">รวม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td style="width: 80px;">
                                    <img src="../../photo/<?= $item['image'] ?>" class="product-img" alt="">
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <small class="text-muted">ID: #<?= $item['product_id'] ?></small>
                                </td>
                                <td class="text-center">x<?= $item['quantity'] ?></td>
                                <td class="text-end">฿<?= number_format($item['price'], 2) ?></td>
                                <td class="text-end fw-bold">฿<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mira-card">
                <div class="section-title">
                    <i class="bi bi-receipt"></i> สรุปยอดชำระ
                </div>
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <div class="summary-row">
                            <span>ราคาสินค้ารวม</span>
                            <span>฿<?= number_format($order['total_price'], 2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>ค่าจัดส่ง</span>
                            <span class="text-success">ฟรี</span>
                        </div>
                        <div class="summary-row">
                            <span>ส่วนลด / คูปอง</span>
                            <span>- ฿0.00</span>
                        </div>
                        <div class="total-row">
                            <span>ยอดสุทธิ</span>
                            <span>฿<?= number_format($order['total_price'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="mira-card">
                <div class="section-title">
                    <i class="bi bi-info-circle"></i> ข้อมูลคำสั่งซื้อ
                </div>
                <div class="info-label">เลขคำสั่งซื้อ</div>
                <div class="info-value">#<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></div>
                
                <div class="info-label">วันที่สั่งซื้อ</div>
                <div class="info-value"><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?> น.</div>
                
                <div class="info-label">สถานะปัจจุบัน</div>
                <div class="mb-3"><?= getStatusBadge($order['status']) ?></div>

               <div class="info-label d-flex justify-content-between align-items-center">
    หมายเลขพัสดุ
    <button type="button" class="btn btn-sm text-pink-mira p-0" onclick="editTracking()" style="color: var(--mira-pink);">
        <i class="bi bi-pencil-square"></i> แก้ไข
    </button>
</div>
<div class="info-value text-primary" id="display_tracking">
    <?= !empty($order['tracking_number']) ? $order['tracking_number'] : '<span class="text-muted fw-normal">ยังไม่ได้ระบุ</span>' ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function editTracking() {
    Swal.fire({
        title: 'ระบุหมายเลขพัสดุ',
        input: 'text',
        inputValue: '<?= $order['tracking_number'] ?? '' ?>',
        inputPlaceholder: 'กรอกเลข Tracking ที่นี่...',
        showCancelButton: true,
        confirmButtonText: 'บันทึก',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#b3365b',
        borderRadius: '1.2rem',
        preConfirm: (tracking) => {
            if (!tracking) {
                Swal.showValidationMessage('กรุณากรอกหมายเลขพัสดุ');
            }
            return tracking;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // ส่งค่าไปที่ไฟล์อัปเดต
            const formData = new FormData();
            formData.append('order_id', <?= $order['order_id'] ?>);
            formData.append('tracking_number', result.value);

            fetch('update_tracking.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'อัปเดตสำเร็จ',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload(); // รีโหลดหน้าเพื่อแสดงเลขใหม่
                    });
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', data.message, 'error');
                }
            });
        }
    });
}
</script>


    <?php if (!empty($order['tracking_number'])): ?>
        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="sendTrackingEmail()">
            <i class="bi bi-envelope-heart"></i> ส่งอีเมลแจ้งลูกค้า
        </button>
    <?php endif; ?>
</div>

<script>
function sendTrackingEmail() {
    Swal.fire({
        title: 'ยืนยันการส่งอีเมล?',
        text: "ระบบจะส่งเลขพัสดุไปที่อีเมล <?= $order['email'] ?>",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#b3365b',
        confirmButtonText: 'ใช่, ส่งเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'กำลังส่งอีเมล...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('send_tracking_email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'order_id=<?= $order['order_id'] ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('สำเร็จ!', 'ส่งข้อมูลให้ลูกค้าเรียบร้อยแล้วค่ะ ✨', 'success');
                } else {
                    Swal.fire('ผิดพลาด', data.message, 'error');
                }
            });
        }
    });
}
</script>

            <div class="mira-card">
                <div class="section-title">
                    <i class="bi bi-person"></i> ข้อมูลลูกค้า
                </div>
                <div class="info-label">ชื่อ-นามสกุล</div>
                <div class="info-value"><?= htmlspecialchars($order['fullname']) ?></div>

                <div class="info-label">เบอร์โทรศัพท์</div>
                <div class="info-value"><?= $order['phone'] ?></div>

                <div class="info-label">อีเมล</div>
                <div class="info-value"><?= htmlspecialchars($order['email']) ?></div>

                <div class="info-label">ที่อยู่จัดส่ง</div>
                <div class="info-value small text-muted" style="line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($order['address'])) ?>
                </div>

                <div class="info-label">หมายเหตุจากลูกค้า</div>
                <div class="info-value small"><?= !empty($order['return_remark']) ? htmlspecialchars($order['return_remark']) : '-' ?></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>