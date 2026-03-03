<?php
session_start();
require_once "../../config.php";

$total_price = 0;
$items = [];
$user_id = $_SESSION['user_id'] ?? 0;
$my_vouchers = [];

if ($user_id > 0) {
    // ดึงคูปองที่เก็บไว้ (unused) และยังไม่หมดอายุ
   // ลองเอา AND p.end_date >= NOW() ออกเพื่อเช็คว่าข้อมูลมาไหม
$v_sql = "SELECT uv.*, p.promo_name, p.discount_type, p.discount_value, p.min_spent 
          FROM user_vouchers uv
          JOIN promotions p ON uv.promo_id = p.promo_id
          WHERE uv.user_id = ? AND uv.used_status = 'unused'";
    $v_stmt = $conn->prepare($v_sql);
    $v_stmt->execute([$user_id]);
    $my_vouchers = $v_stmt->fetchAll(PDO::FETCH_ASSOC);
}
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    try {
        $sql = "SELECT * FROM products WHERE product_id IN ($ids)";
        $stmt = $conn->query($sql);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- เพิ่มส่วนคำนวณยอดเงินตรงนี้ ---
        foreach ($items as $item) {
            $qty = $_SESSION['cart'][$item['product_id']];
            $total_price += $item['price'] * $qty;
        }
        // ------------------------------
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
} else {
    // ถ้าตะกร้าว่าง ให้ดีดกลับไปหน้าตะกร้า
    header("Location: mycart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIRA | Payment Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --mira-pink-bg: #fdf5f7;
            --mira-pink-accent: #f8a5c2;
            --mira-dark-pink: #a34a67;
            --mira-white: #ffffff;
        }

        body {
            background-color: var(--mira-pink-bg);
            font-family: 'Sarabun', sans-serif;
            color: #555;
        }

        .checkout-header {
            font-family: 'Playfair Display', serif;
            color: var(--mira-dark-pink);
            font-size: 3rem;
            margin-bottom: 30px;
            font-weight: 800;
        }

        .payment-card {
            background: var(--mira-white);
            border-radius: 30px;
            border: none;
            box-shadow: 0 15px 40px rgba(163, 74, 103, 0.05);
            padding: 40px;
        }

        /* ส่วนข้อมูลบัญชีธนาคาร */
        .bank-info-box {
            background: linear-gradient(135deg, #fff 0%, #fff0f5 100%);
            border: 2px dashed var(--mira-pink-accent);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            position: relative;
        }

        .bank-logo {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .copy-btn {
            background: var(--mira-pink-bg);
            color: var(--mira-dark-pink);
            border: none;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .copy-btn:hover { background: var(--mira-pink-accent); color: white; }

        /* การอัปโหลดสลิป */
        .upload-area {
            border: 2px dashed #eee;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            background: #fafafa;
        }
        .upload-area:hover { border-color: var(--mira-pink-accent); background: #fff; }

        .form-label {
            font-weight: 600;
            color: var(--mira-dark-pink);
            font-size: 0.9rem;
        }

        .form-control {
            border-radius: 15px;
            border: 1px solid #f3e4e8;
            padding: 12px 15px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(248, 165, 194, 0.2);
            border-color: var(--mira-pink-accent);
        }

        .btn-confirm {
            background: var(--mira-dark-pink);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 20px;
            width: 100%;
            font-weight: 600;
            margin-top: 20px;
            transition: 0.4s;
        }
        .btn-confirm:hover {
            background: #8e3e58;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(163, 74, 103, 0.2);
            color: white;
        }

        .order-summary-mini {
            background: #fff;
            border-radius: 25px;
            padding: 25px;
            border: 1px solid #f3e4e8;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h2 class="checkout-header text-center">ชำระเงิน</h2>
            
            <div class="row g-4">
                <div class="col-md-7">
                  <div class="payment-card">
    <div class="d-flex p-1 bg-light rounded-pill mb-4" style="border: 1px solid #f3e4e8;">
        <button type="button" id="tab_transfer" onclick="selectMethod('Bank Transfer')" 
            class="btn btn-sm w-50 rounded-pill py-2 fw-bold transition-all" 
            style="background: var(--mira-dark-pink); color: white; border: none;">
            <i class="bi bi-bank me-2"></i>โอนเงิน
        </button>
        <button type="button" id="tab_cod" onclick="selectMethod('Cash on Delivery')" 
            class="btn btn-sm w-50 rounded-pill py-2 fw-bold text-muted transition-all" 
            style="background: transparent; border: none;">
            <i class="bi bi-truck me-2"></i>เก็บเงินปลายทาง
        </button>
    </div>

    <form action="process_payment.php" method="POST" enctype="multipart/form-data" id="paymentForm">
        <input type="hidden" name="payment_method" id="payment_method" value="Bank Transfer">

        <div id="transfer_group">
            <h5 class="fw-bold mb-4">โอนเงินผ่านบัญชีธนาคาร</h5>
            
            <div class="bank-info-box">
                <div class="d-flex align-items-center mb-3">
                    <img src="../../users/photo/Bank.webp" class="bank-logo me-3" alt="Bank Logo">
                    <div>
                        <p class="mb-0 fw-bold text-dark">ธนาคารกรุงไทย (KTB)</p>
                        <p class="mb-0 small text-muted">ชื่อบัญชี: บจก. มิรา</p>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-4 border">
                    <span class="h5 mb-0 fw-bold text-mira">679-5-69372-4</span>
                    <button type="button" class="copy-btn" onclick="copyAccount()">คัดลอกเลขบัญชี</button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">อัปโหลดหลักฐานการโอน (Slip)</label>
                <div class="upload-area" onclick="document.getElementById('slip_img').click()">
                    <i class="bi bi-cloud-arrow-up" style="font-size: 2rem; color: var(--mira-pink-accent);"></i>
                    <p class="mb-0 small text-muted">คลิกเพื่อเลือกไฟล์รูปภาพสลิป</p>
                    <input type="file" id="slip_img" name="slip_img" hidden required onchange="previewImage(this)">
                    <div id="preview-container" class="mt-2 hidden"></div>
                </div>
            </div>
        </div>

        <div id="cod_group" style="display: none;">
            <h5 class="fw-bold mb-4">เก็บเงินปลายทาง (COD)</h5>
            <div class="text-center py-4 border rounded-4 mb-4" style="background: #fafafa; border: 2px dashed #eee;">
                <i class="bi bi-box-seam d-block mb-3" style="font-size: 3rem; color: var(--mira-pink-accent);"></i>
                <p class="mb-1 fw-bold text-dark">ชำระเงินเมื่อได้รับสินค้า</p>
                <p class="small text-muted mb-0">เตรียมยอดเงินให้พอดีเพื่อความสะดวกในการรับสินค้านะคะ</p>
            </div>
        </div>
                            <div class="order-summary-mini shadow-sm mb-3">
    <h6 class="fw-bold mb-3" style="color: var(--mira-dark-pink);">
        <i class="bi bi-ticket-perforated me-2"></i>ส่วนลดของฉัน
    </h6>
    
    <?php if (empty($my_vouchers)): ?>
        <p class="text-muted small mb-0 text-center py-2">คุณยังไม่มีคูปองส่วนลดเก็บไว้</p>
    <?php else: ?>
        <select class="form-select form-select-sm border-pink" id="couponSelect" onchange="applyCoupon()">
            <option value="0" data-discount="0">เลือกคูปองส่วนลด...</option>
            <?php foreach($my_vouchers as $v): ?>
                <option value="<?= $v['promo_id'] ?>" 
                        data-type="<?= $v['discount_type'] ?>" 
                        data-value="<?= $v['discount_value'] ?>"
                        data-min="<?= $v['min_spent'] ?>">
                    <?= htmlspecialchars($v['promo_name']) ?> 
                    (ลด <?= $v['discount_value'] ?><?= $v['discount_type']=='percentage' ? '%' : '฿' ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <div id="couponMessage" class="small mt-2"></div>
    <?php endif; ?>
</div>

<input type="hidden" name="applied_promo_id" id="applied_promo_id" value="0">
<input type="hidden" name="final_total_price" id="final_total_price" value="<?= $total_price ?>">

                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label">วันที่โอน</label>
                                    <input type="date" class="form-control" name="pay_date" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">เวลาที่โอน (โดยประมาณ)</label>
                                    <input type="time" class="form-control" name="pay_time" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-confirm">
                                แจ้งชำระเงินเรียบร้อย
                            </button>
                        </form>
                    </div>
                </div>

           <div class="col-md-5">
    <div class="order-summary-mini shadow-sm">
        <h6 class="fw-bold mb-4" style="color: var(--mira-dark-pink);">สรุปยอดที่ต้องชำระ</h6>
        
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted small">ยอดรวมสินค้า</span>
            <span class="fw-bold">฿<?= number_format($total_price, 2) ?></span>
        </div>
        
        <div class="d-flex justify-content-between mb-2 text-danger" id="discountRow" style="display: none !important;">
            <span class="small">ส่วนลดคูปอง</span>
            <span class="fw-bold">-฿<span id="discountAmount">0.00</span></span>
        </div>

        <div class="d-flex justify-content-between mb-3">
            <span class="text-muted small">ค่าจัดส่ง</span>
            <span class="text-success small fw-bold">FREE</span>
        </div>
        
        <hr>

        <div class="d-flex justify-content-between align-items-center">
            <span class="fw-bold">จำนวนเงินทั้งสิ้น</span>
            <span class="h3 mb-0 fw-bold" style="color: var(--mira-dark-pink);">
                ฿<span id="finalPrice"><?= number_format($total_price, 2) ?></span>
            </span>
        </div>
    </div>

    <div class="text-center mt-4">
        <p class="small text-muted">
            <i class="bi bi-shield-check text-success"></i> 
            ตรวจสอบข้อมูลอย่างปลอดภัยตามมาตรฐาน SSL
        </p>
        
                        
                        <style>
    .btn-back-pill {
        display: inline-flex;
        align-items: center;
        background: white;
        color: var(--mira-dark-pink);
        border: 1px solid #eee;
        padding: 10px 25px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }

    .btn-back-pill i {
        margin-right: 10px;
    }

    .btn-back-pill:hover {
        background: var(--mira-bg); /* ชมพูอ่อนมากที่ตั้งค่าไว้ */
        border-color: var(--mira-pink);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(179, 54, 91, 0.1);
        color: var(--mira-dark-pink);
    }
</style>
                        <a href="mycart.php" class="btn-back-pill">
    <i class="bi bi-chevron-left"></i> กลับไปที่ตะกร้าสินค้า
</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function togglePaymentFields() {
    const isCod = document.getElementById('pay_cod').checked;
    const slipSection = document.getElementById('slip_section');
    const slipInput = document.getElementById('slip_img');

    if (isCod) {
        slipSection.style.display = 'none';
        slipInput.required = false; // ไม่ต้องบังคับสลิปถ้าเลือก COD
    } else {
        slipSection.style.display = 'block';
        slipInput.required = true;
    }
}
function copyAccount() {
    navigator.clipboard.writeText('6795693724');
    Swal.fire({
        icon: 'success',
        title: 'คัดลอกสำเร็จ',
        text: 'คัดลอกเลขบัญชีแล้วค่ะ',
        showConfirmButton: false,
        timer: 1500
    });
}

function previewImage(input) {
    const container = document.getElementById('preview-container');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            container.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded-3 mt-3 shadow-sm" style="max-height: 200px;">`;
            container.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>


<script>
    function selectMethod(method) {
    const methodInput = document.getElementById('payment_method');
    const transferGroup = document.getElementById('transfer_group');
    const codGroup = document.getElementById('cod_group');
    const slipInput = document.getElementById('slip_img');
    
    // ปุ่ม Tab
    const tabTransfer = document.getElementById('tab_transfer');
    const tabCod = document.getElementById('tab_cod');

    methodInput.value = method;

    if (method === 'Cash on Delivery') {
        transferGroup.style.display = 'none';
        codGroup.style.display = 'block';
        slipInput.required = false;

        // สลับ Style ปุ่ม
        tabCod.style.background = 'var(--mira-dark-pink)';
        tabCod.style.color = 'white';
        tabCod.classList.remove('text-muted');
        
        tabTransfer.style.background = 'transparent';
        tabTransfer.style.color = '#6c757d';
        tabTransfer.classList.add('text-muted');
    } else {
        transferGroup.style.display = 'block';
        codGroup.style.display = 'none';
        slipInput.required = true;

        // สลับ Style ปุ่มกลับ
        tabTransfer.style.background = 'var(--mira-dark-pink)';
        tabTransfer.style.color = 'white';
        tabTransfer.classList.remove('text-muted');
        
        tabCod.style.background = 'transparent';
        tabCod.style.color = '#6c757d';
        tabCod.classList.add('text-muted');
    }
}
document.querySelector('form[action="process_payment.php"]').addEventListener('submit', function(e) {
    // 1. ป้องกันฟอร์มส่งข้อมูลทันที
    e.preventDefault();
    const form = this;

    // 2. แสดง Popup ยืนยันการแจ้งชำระเงิน
    Swal.fire({
        title: 'ยืนยันการแจ้งชำระเงิน?',
        text: "ตรวจสอบข้อมูลและหลักฐานการโอนเงินให้ถูกต้องนะคะ",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#a34a67', // สี mira-dark-pink จากสไตล์ของคุณ
        cancelButtonColor: '#aaa',
        confirmButtonText: 'ยืนยันและส่งข้อมูล',
        cancelButtonText: 'ตรวจสอบอีกครั้ง',
        borderRadius: '20px'
    }).then((result) => {
        if (result.isConfirmed) {
            // 3. แสดง Popup โหลดระหว่างกำลังประมวลผล (ถ้าต้องการ)
            Swal.fire({
                title: 'กำลังบันทึกข้อมูล...',
                text: 'กรุณารอสักครู่ค่ะ',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // 4. ส่งฟอร์มไปยัง PHP ตาม Logic เดิม
            form.submit();
        }
    });
});
function applyCoupon() {
    const select = document.getElementById('couponSelect');
    const option = select.options[select.selectedIndex];
    
    const basePrice = parseFloat("<?= $total_price ?>");
    const minSpent = parseFloat(option.getAttribute('data-min') || 0);
    const discType = option.getAttribute('data-type');
    const discValue = parseFloat(option.getAttribute('data-value') || 0);
    
    const msg = document.getElementById('couponMessage');
    const discRow = document.getElementById('discountRow');
    const discDisplay = document.getElementById('discountAmount');
    const finalDisplay = document.getElementById('finalPrice');
    
    // Hidden inputs ในฟอร์ม
    const inputPromo = document.getElementById('applied_promo_id');
    const inputFinal = document.getElementById('final_total_price');

    // ตรวจสอบยอดขั้นต่ำ
    if (basePrice < minSpent) {
        msg.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-circle"></i> ยอดขั้นต่ำต้องถึง ฿${minSpent}</span>`;
        resetDiscount();
        return;
    }

    let discount = 0;
    if (discType === 'percentage') {
        discount = (basePrice * discValue) / 100;
    } else {
        discount = discValue;
    }

    if (discount > 0) {
        msg.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> ใช้ส่วนลดได้สำเร็จ</span>`;
        discRow.setAttribute('style', 'display: flex !important');
        discDisplay.innerText = discount.toLocaleString(undefined, {minimumFractionDigits: 2});
        
        const final = basePrice - discount;
        finalDisplay.innerText = final.toLocaleString(undefined, {minimumFractionDigits: 2});
        
        // เก็บค่าลง Hidden Input เพื่อส่งไป process_payment.php
        inputPromo.value = select.value;
        inputFinal.value = final;
    } else {
        resetDiscount();
    }

    function resetDiscount() {
        msg.innerHTML = "";
        discRow.setAttribute('style', 'display: none !important');
        finalDisplay.innerText = basePrice.toLocaleString(undefined, {minimumFractionDigits: 2});
        inputPromo.value = "0";
        inputFinal.value = basePrice;
    }
}
</script>



</body>
</html>