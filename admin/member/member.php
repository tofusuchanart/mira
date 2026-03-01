<?php
session_start();
require_once "../../config.php";
/** @var PDO $conn */
$stmt = $conn->query("SELECT COUNT(*) FROM users");
$user_count = ($stmt) ? $stmt->fetchColumn() : 0;
$newToday = $conn->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE() AND role = 'customer'")->fetchColumn();

// 2. ลูกค้าใหม่เดือนนี้
$newMonth = $conn->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND role = 'customer'")->fetchColumn();

// 3. ลูกค้าประจำ (ซื้อซ้ำมากกว่า 1 ครั้ง)
$loyalCustomers = $conn->query("SELECT COUNT(*) FROM (SELECT user_id FROM orders GROUP BY user_id HAVING COUNT(order_id) > 1) as loyal")->fetchColumn();

// 4. รายชื่อลูกค้าและยอดซื้อรวม (ดึงข้อมูลจาก users และ orders)
$customers = $conn->query("SELECT u.*, 
                           COALESCE(SUM(o.total_price), 0) as total_spent, 
                           COUNT(o.order_id) as order_count 
                           FROM users u 
                           LEFT JOIN orders o ON u.user_id = o.user_id 
                           WHERE u.role = 'customer'
                           GROUP BY u.user_id 
                           ORDER BY total_spent DESC")->fetchAll(PDO::FETCH_ASSOC);
                           
?>
<?php if(isset($_SESSION['success'])): ?>
    <script>
        // ใช้ window.onload เพื่อให้แน่ใจว่าโหลดหน้าเว็บและ Lib เสร็จก่อน
        window.onload = function() {
            Swal.fire({
                title: 'บันทึกสำเร็จ!',
                text: '<?= $_SESSION['success'] ?>',
                icon: 'success',
                confirmButtonColor: '#b3365b',
                confirmButtonText: 'ตกลง',
                backdrop: `rgba(179, 54, 91, 0.1)`,
                customClass: {
                    popup: 'rounded-4' // ถ้าอยากให้มนๆ แบบ MIRA
                }
            });
        };
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <script>
        window.onload = function() {
            Swal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: '<?= $_SESSION['error'] ?>',
                icon: 'error',
                confirmButtonColor: '#4a4a4a',
                confirmButtonText: 'ลองใหม่'
            });
        };
    </script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>





<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>MIRA | Membership Insights</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <style>
    /* นำเข้าฟอนต์ Sarabun เพื่อความสม่ำเสมอ */
    @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');

    body {
        background-color: #fdf2f4; /* สีพื้นหลังชมพูอ่อนมากแบบในรูปตัวอย่าง */
        font-family: 'Sarabun', sans-serif;
        color: #4a4a4a;
    }

    /* สไตล์หัวข้อตามรูปตัวอย่าง */
    .mira-title { 
        color: #b3365b; 
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    /* ปุ่มกลับ Dashboard สไตล์ Minimal */
    .back-link {
        text-decoration: none;
        color: #94a3b8;
        font-size: 0.9rem;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .back-link:hover { color: #b3365b; }

    /* Stat Card ปรับให้เรียบและขอบมนขึ้น */
    .stat-card {
        background: white;
        border-radius: 2.5rem; /* ขอบมนมากแบบปุ่มในรูป */
        padding: 25px;
        border: none;
        transition: transform 0.3s ease;
        box-shadow: 0 10px 25px -5px rgba(240, 98, 146, 0.1);
    }
    .stat-card:hover { transform: translateY(-5px); }
    
    .stat-icon { 
        width: 48px; height: 48px; 
        background: #fff0f3; 
        color: #f06292; 
        border-radius: 1rem; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 1.4rem; margin-bottom: 12px; 
    }

    /* แผงตารางสไตล์ MIRA-CARD */
    .glass-panel {
        background: white;
        border-radius: 2.5rem;
        padding: 40px;
        box-shadow: 0 10px 25px -5px rgba(240, 98, 146, 0.1);
        border: none;
    }

    /* ตาราง */
    .table thead th {
        background: transparent;
        border-bottom: 1px solid #f8f9fa;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding-bottom: 20px;
    }
    .table tbody td {
        padding: 20px 10px;
        border-bottom: 1px solid #fdf2f4;
        font-size: 0.95rem;
    }
    
    .user-avatar { 
        width: 45px; height: 45px; 
        border-radius: 50%; 
        object-fit: cover; 
        border: 2px solid #fff; 
        box-shadow: 0 4px 10px rgba(240, 98, 146, 0.2); 
    }

    /* Badge สไตล์ Minimal */
    .badge-premium { 
        background: #f06292; 
        color: white; 
        border-radius: 50px; 
        padding: 5px 15px; 
        font-size: 0.65rem; 
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .badge-general {
        background: #f8f9fa;
        color: #94a3b8;
        border-radius: 50px;
        padding: 5px 15px;
        font-size: 0.65rem;
        font-weight: 700;
    }
    
    .text-pink-mira { color: #f06292; }
</style>
</head>
<body>

<div class="container py-5">
    <div class="mb-4">
        <a href="../index_ad.php" class="back-link">
            <i class="bi bi-arrow-left"></i> กลับสู่หน้า Dashboard
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-5">
        <div>
            <h2 class="mira-title mb-1">ข้อมูลสมาชิก</h2>
            <p class="text-muted small">บริหารจัดการข้อมูลสมาชิกและสถิติการซื้อของ Mira</p>
        </div>
       
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-person-plus"></i></div>
                <h6 class="text-muted small fw-600">ลูกค้าใหม่วันนี้</h6>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($newToday) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: #eef2ff; color: #6366f1;"><i class="bi bi-calendar-check"></i></div>
                <h6 class="text-muted small fw-600">ลูกค้าใหม่เดือนนี้</h6>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($newMonth) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff7ed; color: #f97316;"><i class="bi bi-arrow-repeat"></i></div>
                <h6 class="text-muted small fw-600">ลูกค้าประจำ</h6>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($loyalCustomers) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: #fefce8; color: #ca8a04;"><i class="bi bi-gem"></i></div>
                <h6 class="text-muted small fw-600">ระดับ VIP</h6>
                <h3 class="fw-bold mb-0 text-dark">Premium</h3>
            </div>
        </div>
    </div>

    <div class="glass-panel">
        <div class="mb-4">
            <h5 class="fw-bold text-dark">รายชื่อสมาชิกทั้งหมด (<?= count($customers) ?>)</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>สมาชิกระดับ</th>
                        <th>ข้อมูลลูกค้า</th>
                        <th>วันที่สมัคร</th>
                        <th class="text-center">ออเดอร์</th>
                        <th class="text-end">ยอดซื้อรวม</th>
                    </tr>
                </thead>

<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 2rem; overflow: hidden;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-0 text-center">
                <img id="modal_img" src="" class="user-avatar mb-3" style="width: 100px; height: 100px; border: 4px solid #fff0f3;">
                <h4 id="modal_name" class="fw-bold text-dark mb-1"></h4>
                <p id="modal_email" class="text-muted small mb-3"></p>
                
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-4">
                            <small class="text-muted d-block">เบอร์โทรศัพท์</small>
                            <span id="modal_phone" class="fw-600"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-4">
                            <small class="text-muted d-block">จำนวนออเดอร์</small>
                            <span id="modal_orders" class="fw-600"></span> รายการ
                        </div>
                    </div>
                </div>

                <div class="text-start p-3 rounded-4 mb-4" style="background: #fff0f3;">
                    <h6 class="small fw-bold text-pink-mira mb-2"><i class="bi bi-geo-alt-fill"></i> ที่อยู่จัดส่ง</h6>
                    <p id="modal_address" class="small text-dark mb-0"></p>
                </div>

                <a id="modal_history_btn" href="#" class="btn btn-mira w-100 py-3 rounded-4 fw-bold" style="background: #b3365b; color: white; text-decoration: none;">
                    <i class="bi bi-cart-check-fill me-2"></i> ดูประวัติการสั่งซื้อทั้งหมด
                </a>
            </div>
        </div>
    </div>
</div>


                <tbody>
    <?php foreach ($customers as $cus): 
        // --- Logic ดึงรูปโปรไฟล์เดิม ---
        $profile_file = $cus['profile_img'];
        $file_path = "../../register/photo/" . $profile_file;
        if (!empty($profile_file) && file_exists($file_path)) {
            $pic = $file_path;
        } else {
            $pic = "https://ui-avatars.com/api/?name=".urlencode($cus['fullname'])."&background=f06292&color=fff";
        }
    ?>
    <tr>
        <td>
            <?php if($cus['total_spent'] > 5000): ?>
                <span class="badge-premium">TOP SPENDER</span>
            <?php else: ?>
                <span class="badge-general">GENERAL</span>
            <?php endif; ?>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <img src="<?= $pic ?>" class="user-avatar me-3">
                <div>
                    <h6 class="mb-0 fw-600 text-dark"><?= htmlspecialchars($cus['fullname']) ?></h6>
                    <small class="text-muted"><?= $cus['email'] ?></small>
                </div>
            </div>
        </td>
        <td class="small text-muted"><?= date('d/m/Y', strtotime($cus['created_at'])) ?></td>
        <td class="text-center fw-bold text-dark"><?= $cus['order_count'] ?></td>
        <td class="text-end fw-bold text-pink-mira">฿<?= number_format($cus['total_spent'], 2) ?></td>
        

        <td class="text-end">
    <div class="d-flex gap-2 justify-content-end">
        <button class="btn btn-sm btn-light rounded-pill px-3" 
                onclick="viewCustomer(<?= htmlspecialchars(json_encode($cus)) ?>)"
                style="font-size: 0.8rem; border: 1px solid #eee;">
            <i class="bi bi-eye text-pink-mira"></i>รายละเอียด
        </button>
        <button class="btn btn-sm btn-light rounded-pill px-3" 
                onclick="editCustomer(<?= htmlspecialchars(json_encode($cus)) ?>)"
                style="font-size: 0.8rem; border: 1px solid #eee;">
            <i class="bi bi-pencil-square text-primary"></i> แก้ไข
        </button>
    </div>
</td>


    </tr>
    <?php endforeach; ?>
</tbody>

<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 2rem;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-dark"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลสมาชิก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="update_member.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="user_id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ชื่อ-นามสกุล</label>
                        <input type="text" name="fullname" id="edit_name" class="form-control rounded-3 border-0 bg-light" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">อีเมล (ใช้สำหรับ Login)</label>
                        <input type="email" name="email" id="edit_email" class="form-control rounded-3 border-0 bg-light" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">เบอร์โทรศัพท์</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control rounded-3 border-0 bg-light">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ที่อยู่จัดส่ง</label>
                        <textarea name="address" id="edit_address" rows="3" class="form-control rounded-3 border-0 bg-light"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ระดับสมาชิก (Role)</label>
                        <select name="role" id="edit_role" class="form-select rounded-3 border-0 bg-light">
                            <option value="customer">Customer (ลูกค้าทั่วไป)</option>
                            <option value="owner">Owner (ผู้ดูแลระบบ)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-mira rounded-pill px-4" style="background: #b3365b; color: white;">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </form>
        </div>
    </div>
</div>



            </table>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function viewCustomer(data) {
    // กำหนดรูปภาพ
    let pic = data.profile_img ? "../../register/photo/" + data.profile_img : "https://ui-avatars.com/api/?name=" + encodeURIComponent(data.fullname) + "&background=f06292&color=fff";
    
    // ใส่ข้อมูลลงใน Modal
    document.getElementById('modal_img').src = pic;
    document.getElementById('modal_name').innerText = data.fullname;
    document.getElementById('modal_email').innerText = data.email;
    document.getElementById('modal_phone').innerText = data.phone || 'ไม่ได้ระบุ';
    document.getElementById('modal_address').innerText = data.address || 'ไม่มีข้อมูลที่อยู่';
    document.getElementById('modal_orders').innerText = data.order_count;
    
    // ลิงก์ไปหน้าประวัติการสั่งซื้อ (อ้างอิงตามโครงสร้างโฟลเดอร์คุณ)
    // ส่ง user_id ไปเพื่อกรองเฉพาะออเดอร์ของคนนี้
    document.getElementById('modal_history_btn').href = "order_history.php?user_id=" + data.user_id;

    // สั่งเปิด Modal
    new bootstrap.Modal(document.getElementById('customerModal')).show();
}

// ฟังก์ชันแก้ไขข้อมูล (เพิ่มส่วนนี้เข้าไป)
function editCustomer(data) {
    document.getElementById('edit_id').value = data.user_id;
    document.getElementById('edit_name').value = data.fullname;
    document.getElementById('edit_email').value = data.email;
    document.getElementById('edit_phone').value = data.phone || '';
    document.getElementById('edit_address').value = data.address || '';
    document.getElementById('edit_role').value = data.role;

    let myEditModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
    myEditModal.show();
}
</script>


</body>
</html>