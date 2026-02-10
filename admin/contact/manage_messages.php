<?php
session_start();
require_once "../../config.php"; 

// ตรวจสอบสิทธิ์ (เฉพาะ Owner เท่านั้น)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

$status_msg = "";

// ส่วนการลบข้อความ
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE message_id = ?");
    if ($stmt->execute([$delete_id])) {
        $status_msg = "ลบข้อความเรียบร้อยแล้วค่ะ ✨";
    }
}

// ดึงข้อความติดต่อทั้งหมด และ JOIN กับตาราง users เพื่อดูว่าใครส่งมา
$sql = "SELECT m.*, u.fullname, u.email as user_email 
        FROM contact_messages m 
        LEFT JOIN users u ON m.user_id = u.user_id 
        ORDER BY m.created_at DESC";
$stmt = $conn->query($sql);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages | MIRA Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --mira-pink-dark: #a34a67;
            --mira-pink-soft: #fdf5f7;
            --mira-pink-accent: #f8a5c2;
            --mira-bg: #fff0f5;
        }

        body {
            background-color: var(--mira-bg);
            font-family: 'Sarabun', sans-serif;
            color: #5d5d5d;
        }

        .mira-header {
            font-family: 'Playfair Display', serif;
            color: var(--mira-pink-dark);
            letter-spacing: 1px;
        }

        .admin-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            padding: 40px;
            box-shadow: 0 15px 35px rgba(163, 74, 103, 0.05);
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table thead th {
            border: none;
            color: var(--mira-pink-dark);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 15px;
        }

        .table tbody tr {
            background: white;
            transition: 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }

        .table tbody tr:hover {
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(163, 74, 103, 0.1);
        }

        .table tbody td {
            padding: 20px 15px;
            border: none;
            vertical-align: middle;
        }

        .table tbody td:first-child { border-radius: 15px 0 0 15px; }
        .table tbody td:last-child { border-radius: 0 15px 15px 0; }

        .btn-action {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-view { background: var(--mira-pink-soft); color: var(--mira-pink-dark); }
        .btn-view:hover { background: var(--mira-pink-dark); color: white; }
        
        .btn-delete { background: #fff5f5; color: #e57373; }
        .btn-delete:hover { background: #e57373; color: white; }

        .nav-back {
            color: var(--mira-pink-dark);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <a href="../index_ad.php" class="nav-back">
        <i class="bi bi-chevron-left me-2"></i> กลับหน้าDashboard
    </a>

    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mira-header fw-bold mb-0">ข้อความติดต่อ</h2>
                <p class="text-muted small">ข้อความสอบถามจากลูกค้า MIRA</p>
            </div>
            <span class="badge rounded-pill p-2 px-3" style="background: var(--mira-pink-dark);">
                <?= count($messages) ?> ข้อความ
            </span>
        </div>

        <?php if ($status_msg): ?>
            <script>
                Swal.fire({ icon: 'success', title: 'สำเร็จ', text: '<?= $status_msg ?>', confirmButtonColor: '#a34a67' });
            </script>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th width="15%">วันที่</th>
                        <th width="20%">ลูกค้า</th>
                        <th width="25%">หัวข้อ</th>
                        <th width="30%">ข้อความ</th>
                        <th width="10%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">ยังไม่มีข้อความติดต่อเข้ามาค่ะ</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td class="small"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($msg['fullname'] ?? 'บุคคลทั่วไป') ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($msg['user_email'] ?? 'ไม่มีอีเมล') ?></div>
                            </td>
                            <td><span class="fw-semibold" style="color: var(--mira-pink-dark);"><?= htmlspecialchars($msg['subject']) ?></span></td>
                            <td>
                                <div class="text-truncate" style="max-width: 250px;">
                                    <?= htmlspecialchars($msg['message']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="#" class="btn-action btn-view" onclick="viewDetail('<?= htmlspecialchars(addslashes($msg['message'])) ?>', '<?= htmlspecialchars($msg['subject']) ?>')">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="#" class="btn-action btn-delete" onclick="confirmDelete(<?= $msg['message_id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// ฟังก์ชันดูรายละเอียดข้อความ (ใช้ SweetAlert เพื่อความ Minimal)
function viewDetail(text, subject) {
    Swal.fire({
        title: subject,
        text: text,
        confirmButtonColor: '#a34a67',
        confirmButtonText: 'ปิด',
        borderRadius: '25px'
    });
}

// ฟังก์ชันยืนยันการลบ
function confirmDelete(id) {
    Swal.fire({
        title: 'ลบข้อความนี้?',
        text: "คุณต้องการลบข้อความนี้ออกจากระบบใช่หรือไม่",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#a34a67',
        cancelButtonColor: '#ccc',
        confirmButtonText: 'ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'manage_messages.php?delete_id=' + id;
        }
    })
}
</script>

</body>
</html>