<?php
session_start();
require_once "../../config.php"; // ปรับ Path ตามจริงของคุณ
/** @var PDO $conn */

// --- ส่วนของการรับค่า Filter ---
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$date_range = $_GET['date_range'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// --- สร้าง SQL Query ---
$query = "SELECT o.*, u.fullname, u.email, u.phone, p.payment_method, p.payment_status,
          (SELECT GROUP_CONCAT(CONCAT(pr.product_name, ' x', oi.quantity) SEPARATOR '<br>') 
           FROM order_items oi 
           JOIN products pr ON oi.product_id = pr.product_id 
           WHERE oi.order_id = o.order_id) as items_detail
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.user_id
          LEFT JOIN payments p ON o.order_id = p.order_id
          WHERE 1=1";

$params = [];

// ค้นหา (เลขคำสั่งซื้อ, ชื่อ, อีเมล, เบอร์)
if ($search) {
    $query .= " AND (o.order_id LIKE :s OR u.fullname LIKE :s OR u.email LIKE :s OR u.phone LIKE :s)";
    $params[':s'] = "%$search%";
}

// กรองสถานะ
if ($status) {
    $query .= " AND o.status = :status";
    $params[':status'] = $status;
}

// กรองวันที่
if ($date_range == 'today') {
    $query .= " AND DATE(o.order_date) = CURDATE()";
} elseif ($date_range == '7days') {
    $query .= " AND o.order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($date_range == 'custom' && $start_date && $end_date) {
    $query .= " AND DATE(o.order_date) BETWEEN :start AND :end";
    $params[':start'] = $start_date;
    $params[':end'] = $end_date;
}

$query .= " ORDER BY o.order_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ฟังก์ชัน Badge สีสถานะ
function getStatusBadge($status)
{
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

<?php if (isset($_SESSION['success'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.onload = function() {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: '<?= $_SESSION['success'] ?>',
                confirmButtonColor: '#b3365b',
                borderRadius: '1.5rem'
            });
        };
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>MIRA | Order Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --mira-pink: #b3365b;
            --mira-soft-pink: #fdf2f4;
            --mira-text: #4a4a4a;
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
            padding: 25px;
        }

        .btn-mira {
            background: var(--mira-pink);
            color: white;
            border-radius: 50px;
            padding: 8px 25px;
            border: none;
            transition: 0.3s;
        }

        .btn-mira:hover {
            background: #8e2a48;
            color: white;
            transform: translateY(-2px);
        }

        /* Status Badges */
        .badge {
            border-radius: 50px;
            padding: 6px 12px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-pending {
            background: #fff7ed;
            color: #c2410c;
        }

        .badge-paid {
            background: #eef2ff;
            color: #4338ca;
        }

        .badge-shipped {
            background: #fefce8;
            color: #a16207;
        }

        .badge-success {
            background: #f0fdf4;
            color: #15803d;
        }

        .badge-danger {
            background: #fef2f2;
            color: #b91c1c;
        }

        .order-table thead th {
            background: #f9fafb;
            color: #94a3b8;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            padding: 15px;
        }

        .order-table tbody td {
            vertical-align: middle;
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }

        .product-info {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.4;
        }

        .search-box {
            border-radius: 50px;
            border: 1px solid #e2e8f0;
            padding-left: 20px;
        }

        .back-link {
            transition: 0.3s;
            color: #8e9aaf !important;
            /* สีเทาอ่อนๆ ตามรูป */
        }

        .back-link:hover {
            color: var(--mira-pink) !important;
            transform: translateX(-5px);
            /* ขยับซ้ายนิดนึงเวลา hover */
        }
    </style>
</head>

<body>

    <div class="container-fluid py-5 px-lg-5">
        <div class="mb-4">
            <a href="member.php" class="text-decoration-none text-muted small d-flex align-items-center gap-2 back-link">
                <i class="bi bi-arrow-left"></i> กลับสู่หน้า Member
            </a>


            <div>
                <h2 style="color: var(--mira-pink); font-weight: 700;">จัดการคำสั่งซื้อ</h2>
                <p class="text-muted small">ตรวจสอบและจัดการรายการสั่งซื้อทั้งหมดของ MIRA</p>
            </div>

        </div>

        <div class="mira-card mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 rounded-start-pill"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 rounded-end-pill" placeholder="เลขคำสั่งซื้อ / ชื่อลูกค้า / เบอร์โทร" value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select rounded-pill">
                        <option value="">ทุกสถานะ</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>รอชำระเงิน</option>
                        <option value="paid" <?= $status == 'paid' ? 'selected' : '' ?>>ชำระเงินแล้ว</option>
                        <option value="shipped" <?= $status == 'shipped' ? 'selected' : '' ?>>กำลังจัดส่ง</option>
                        <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>สำเร็จ</option>
                        <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>ยกเลิก</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="date_range" id="date_range" class="form-select rounded-pill" onchange="toggleCustomDate(this.value)">
                        <option value="">กรองตามวันที่</option>
                        <option value="today" <?= $date_range == 'today' ? 'selected' : '' ?>>วันนี้</option>
                        <option value="7days" <?= $date_range == '7days' ? 'selected' : '' ?>>7 วันล่าสุด</option>
                        <option value="custom" <?= $date_range == 'custom' ? 'selected' : '' ?>>เลือกช่วงวันที่เอง</option>
                    </select>
                </div>
                <div id="custom_date_inputs" class="col-md-3 d-flex gap-2 <?= $date_range != 'custom' ? 'd-none' : '' ?>">
                    <input type="date" name="start_date" class="form-control rounded-pill" value="<?= $start_date ?>">
                    <input type="date" name="end_date" class="form-control rounded-pill" value="<?= $end_date ?>">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-mira w-100"><i class="bi bi-search-heart me-1"></i></button>

                </div>

            </form>
        </div>


        <div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0 p-4">
                        <h5 class="fw-bold"><i class="bi bi-arrow-counterclockwise me-2"></i>ทำรายการคืนสินค้า</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="process_return.php" method="POST">
                        <div class="modal-body p-4 pt-0">
                            <input type="hidden" name="order_id" id="return_order_id">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">เหตุผลการคืนสินค้า</label>
                                <select name="reason" class="form-select rounded-3 border-0 bg-light" required>
                                    <option value="แพ้กลิ่นน้ำหอม">แพ้กลิ่นน้ำหอม</option>
                                    <option value="บรรจุภัณฑ์แตกหัก/รั่วซึม">บรรจุภัณฑ์แตกหัก/รั่วซึม</option>
                                    <option value="ได้รับสินค้าผิดรายการ">ได้รับสินค้าผิดรายการ</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">หมายเหตุเพิ่มเติม</label>
                                <textarea name="remark" class="form-control rounded-3 border-0 bg-light" rows="3"></textarea>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="restore_stock" value="1" id="stockCheck" checked>
                                <label class="form-check-label small" for="stockCheck">คืนสินค้ากลับเข้าสต็อกอัตโนมัติ</label>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="submit" class="btn btn-mira w-100 py-2">ยืนยันการคืนสินค้า</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>










        <div class="mira-card">
            <div class="table-responsive">
                <table class="table order-table">
                    <thead>
                        <tr>
                            <th>เลขคำสั่งซื้อ</th>
                            <th>ข้อมูลลูกค้า</th>
                            <th>รายการสินค้า</th>
                            <th>ยอดรวม</th>
                            <th>การชำระเงิน</th>
                            <th>สถานะ</th>
                            <th>วันที่สั่งซื้อ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td class="fw-bold text-dark">#<?= str_pad($o['order_id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <div class="fw-600 text-dark"><?= htmlspecialchars($o['fullname']) ?></div>
                                    <small class="text-muted"><?= $o['phone'] ?></small>
                                </td>
                                <td>
                                    <div class="product-info"><?= $o['items_detail'] ?></div>
                                </td>
                                <td class="fw-bold text-pink-mira" style="color: var(--mira-pink);">฿<?= number_format($o['total_price'], 2) ?></td>

                                <td>
                                    <?php if ($o['payment_method'] === 'Cash on Delivery'): ?>
                                        <div class="mb-1">
                                            <span class="badge bg-info text-dark shadow-sm" style="font-size: 0.7rem;">
                                                <i class="bi bi-truck me-1"></i> เก็บเงินปลายทาง
                                            </span>
                                        </div>
                                        <span class="text-xs text-primary">● รอชำระเมื่อถึงมือ</span>
                                    <?php else: ?>
                                        <small class="d-block text-muted"><?= $o['payment_method'] ?? 'Bank Transfer' ?></small>
                                        <span class="text-xs <?= $o['payment_status'] == 'success' ? 'text-success' : 'text-danger' ?>">
                                            ● <?= $o['payment_status'] == 'success' ? 'ชำระแล้ว (โอนเงิน)' : 'ยังไม่แนบสลิป' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td><?= getStatusBadge($o['status']) ?></td>
                                <td>
                                    <div class="small"><?= date('d/m/Y', strtotime($o['order_date'])) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= date('H:i', strtotime($o['order_date'])) ?> น.</div>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light rounded-pill dropdown-toggle" data-bs-toggle="dropdown">
                                            จัดการ
                                        </button>
                                        <ul class="dropdown-menu border-0 shadow-sm rounded-4">
                                            <li><a class="dropdown-item small" href="order_detail.php?id=<?= $o['order_id'] ?>"><i class="bi bi-eye me-2"></i>รายละเอียด</a></li>

                                            <?php if ($o['status'] == 'paid' || ($o['payment_method'] === 'Cash on Delivery' && $o['status'] == 'pending')): ?>
                                                <li><a class="dropdown-item small text-primary" href="update_order_status.php?order_id=<?= $o['order_id'] ?>&new_status=shipped"><i class="bi bi-truck me-2"></i>เริ่มจัดส่งสินค้า</a></li>
                                            <?php endif; ?>

                                            <?php if ($o['status'] == 'shipped'): ?>
                                                <li><a class="dropdown-item small text-success" href="update_order_status.php?order_id=<?= $o['order_id'] ?>&new_status=completed"><i class="bi bi-check-circle me-2"></i>จัดส่งสำเร็จ</a></li>
                                            <?php endif; ?>

                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>

                                            ...
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleCustomDate(val) {
            const customDiv = document.getElementById('custom_date_inputs');
            if (val === 'custom') {
                customDiv.classList.remove('d-none');
            } else {
                customDiv.classList.add('d-none');
            }
        }




        function openReturnModal(orderId) {
            // ใส่ค่า ID ลงใน Hidden Input ของ Modal
            document.getElementById('return_order_id').value = orderId;

            // สั่งให้ Modal แสดงผล
            var myModal = new bootstrap.Modal(document.getElementById('returnModal'));
            myModal.show();
        }
        // ดักจับการกดปุ่ม "ยืนยันการคืนสินค้า" ใน Modal
        document.querySelector('#returnModal form').addEventListener('submit', function(e) {
            e.preventDefault(); // หยุดการส่งฟอร์มชั่วคราวเพื่อโชว์ Pop-up
            const form = this;

            Swal.fire({
                title: 'รับเรื่องคืนสินค้าแล้วนะค๊าา 🥺',
                text: 'ระบบกำลังดำเนินการคืนสินค้าและสต็อกให้เรียบร้อยแล้วค่ะ ✨',
                icon: 'success',
                showConfirmButton: false,
                timer: 2500, // แสดง 2.5 วินาที
                timerProgressBar: true,
                borderRadius: '2rem',
                didOpen: () => {
                    // เมื่อแสดง Pop-up เสร็จค่อยส่งข้อมูลจริงไปยัง PHP
                    setTimeout(() => {
                        form.submit();
                    }, 2300);
                }
            });
        });
    </script>
</body>

</html>