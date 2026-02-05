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
                                <button class="copy-btn" onclick="copyAccount()">คัดลอกเลขบัญชี</button>
                            </div>
                        </div>

                        <form action="process_payment.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label">อัปโหลดหลักฐานการโอน (Slip)</label>
                                <div class="upload-area" onclick="document.getElementById('slip_img').click()">
                                    <i class="bi bi-cloud-arrow-up" style="font-size: 2rem; color: var(--mira-pink-accent);"></i>
                                    <p class="mb-0 small text-muted">คลิกเพื่อเลือกไฟล์รูปภาพสลิป</p>
                                    <input type="file" id="slip_img" name="slip_img" hidden required onchange="previewImage(this)">
                                    <div id="preview-container" class="mt-2 hidden"></div>
                                </div>
                            </div>

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
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted small">ค่าจัดส่ง</span>
                            <span class="text-success small fw-bold">FREE</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">จำนวนเงินทั้งสิ้น</span>
                            <span class="h3 mb-0 fw-bold" style="color: var(--mira-dark-pink);">฿<?= number_format($total_price, 2) ?></span>
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
                        <a href="edit.php" class="btn-back-pill">
    <i class="bi bi-chevron-left"></i> กลับไปที่ตะกร้าสินค้า
</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyAccount() {
        navigator.clipboard.writeText('1234567890');
        alert('คัดลอกเลขบัญชีแล้วค่ะ!');
    }

    function previewImage(input) {
        const container = document.getElementById('preview-container');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                container.innerHTML = `<img src="${e.target.result}" style="max-height: 150px; border-radius: 10px;" class="mt-2 shadow-sm">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>