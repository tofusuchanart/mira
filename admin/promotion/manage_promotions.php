<?php
require_once "../../config.php";

// --- ส่วนของการบันทึกข้อมูล (Logic) ---
if (isset($_POST['add_promotion'])) {
    $promo_name = $_POST['promo_name'];
    $discount_type = $_POST['discount_type'];
    $discount_value = $_POST['discount_value'];
    $min_spent = $_POST['min_spent'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $is_flash_sale = isset($_POST['is_flash_sale']) ? 1 : 0;

    try {
        $sql = "INSERT INTO promotions (promo_name, discount_type, discount_value, min_spent, start_date, end_date, is_flash_sale) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$promo_name, $discount_type, $discount_value, $min_spent, $start_date, $end_date, $is_flash_sale]);
        $success = "เพิ่มโปรโมชั่นสำเร็จแล้ว!";
    } catch (PDOException $e) {
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// ลบโปรโมชั่น
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->prepare("DELETE FROM promotions WHERE promo_id = ?")->execute([$id]);
    header("Location: manage_promotions.php");
}

// ดึงข้อมูลโปรโมชั่นทั้งหมด
$promos = $conn->query("SELECT * FROM promotions ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการโปรโมชั่น - MIRA Admin</title>
    <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --mira-pink: #b3365b; --mira-bg: #fff5f7; }
        body { 
            font-family: 'Sarabun', sans-serif; 
            background: var(--mira-bg);
            background-image: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('../photo_ad/ro.jpg');
            background-size: cover; background-attachment: fixed;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            border: none; border-radius: 20px;
            padding: 30px; box-shadow: 0 10px 30px rgba(179, 54, 91, 0.05);
            margin-bottom: 30px;
        }
        .btn-mira { background: var(--mira-pink); color: white; border-radius: 10px; border: none; padding: 10px 20px; }
        .btn-mira:hover { background: #8e2a48; color: white; }
        .badge-flash { background: #ff4d7d; color: white; }
    </style>
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-pink" style="color: var(--mira-pink);"><i class="bi bi-lightning-charge me-2"></i>จัดการโปรโมชั่น & Flash Sale</h2>
        <a href="../index_ad.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">กลับหน้าหลัก</a>
    </div>

    <?php if(isset($success)): ?>
        <div class="alert alert-success border-0 rounded-4 shadow-sm"><?= $success ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4">
            <div class="glass-card">
                <h5 class="fw-bold mb-4">สร้างส่วนลดใหม่</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small">ชื่อโปรโมชั่น</label>
                        <input type="text" name="promo_name" class="form-control rounded-3" placeholder="เช่น Flash Sale 12.12" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small">ประเภท</label>
                            <select name="discount_type" class="form-select rounded-3">
                                <option value="percentage">ลดเป็น %</option>
                                <option value="fixed">ลดจำนวนบาท</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small">ค่าส่วนลด</label>
                            <input type="number" name="discount_value" class="form-control rounded-3" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">ซื้อขั้นต่ำ (บาท)</label>
                        <input type="number" name="min_spent" class="form-control rounded-3" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">วันเวลาเริ่ม</label>
                        <input type="datetime-local" name="start_date" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">วันเวลาสิ้นสุด</label>
                        <input type="datetime-local" name="end_date" class="form-control rounded-3" required>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_flash_sale" id="flashCheck">
                        <label class="form-check-label" for="flashCheck">เปิดเป็น Flash Sale</label>
                    </div>
                    <button type="submit" name="add_promotion" class="btn btn-mira w-100">บันทึกโปรโมชั่น</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="glass-card">
                <h5 class="fw-bold mb-4">รายการโปรโมชั่นทั้งหมด</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr class="text-muted small">
                                <th>ชื่อโปรโมชั่น</th>
                                <th>ส่วนลด</th>
                                <th>ระยะเวลา</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($promos as $row): 
                                $now = new DateTime();
                                $start = new DateTime($row['start_date']);
                                $end = new DateTime($row['end_date']);
                                $status_badge = '';

                                if ($now < $start) {
                                    $status_badge = '<span class="badge bg-warning text-dark">รอเริ่ม</span>';
                                } elseif ($now >= $start && $now <= $end) {
                                    $status_badge = '<span class="badge bg-success">กำลังรัน</span>';
                                } else {
                                    $status_badge = '<span class="badge bg-secondary">หมดอายุ</span>';
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= $row['promo_name'] ?></div>
                                    <?php if($row['is_flash_sale']): ?>
                                        <span class="badge badge-flash" style="font-size: 0.6rem;"><i class="bi bi-lightning-fill"></i> FLASH SALE</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= number_format($row['discount_value']) ?> <?= ($row['discount_type'] == 'percentage' ? '%' : '฿') ?>
                                    <div class="text-muted" style="font-size: 0.7rem;">ขั้นต่ำ <?= $row['min_spent'] ?>฿</div>
                                </td>
                                <td class="small">
                                    <?= date('d/m H:i', strtotime($row['start_date'])) ?> - <br>
                                    <?= date('d/m H:i', strtotime($row['end_date'])) ?>
                                </td>
                                <td><?= $status_badge ?></td>
                                <td>
                                    <a href="?delete=<?= $row['promo_id'] ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('ยืนยันการลบ?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>