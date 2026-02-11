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

// ดึงข้อความติดต่อทั้งหมด
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
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --mira-pink-dark: #a34a67; /* สีชมพูเข้มจากหัวข้อ Customer Directory */
            --mira-bg: #fdf2f4; /* สีพื้นหลังชมพูอ่อนมากแบบในรูป */
            --mira-card-radius: 2.5rem;
        }

        body {
            background-color: var(--mira-bg);
            font-family: 'Sarabun', sans-serif;
            color: #5d5d5d;
        }

        /* ปุ่ม Dashboard ด้านบนขวา */
        .btn-dashboard {
            background: white;
            border: 1px solid #333;
            color: #333;
            border-radius: 20px;
            padding: 5px 20px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn-dashboard:hover {
            background: #eee;
        }

        .mira-title {
            color: var(--mira-pink-dark);
            font-weight: 600;
            font-size: 2.5rem;
            margin-bottom: 5px;
        }

        /* การ์ดสถิติ (Stat Cards) */
        .stat-card {
            background: white;
            border-radius: 1.5rem;
            padding: 25px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            height: 100%;
        }

        .icon-box {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        /* ส่วนตารางข้อความ (Main Table Card) */
        .messages-container {
            background: white;
            border-radius: var(--mira-card-radius);
            padding: 40px;
            margin-top: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }

        .search-container {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 50px;
            padding: 10px 25px;
            margin-bottom: 25px;
        }

        .search-input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 0.95rem;
            color: #888;
        }

        .table thead th {
            border: none;
            color: #aaa;
            font-weight: 400;
            text-transform: none;
            font-size: 0.9rem;
            padding-bottom: 20px;
        }

        .table tbody tr {
            border-bottom: 1px solid #f8f8f8;
            transition: 0.2s;
        }

        .table tbody td {
            padding: 20px 10px;
            vertical-align: middle;
            border: none;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }

        /* ปุ่มจัดการ */
        .btn-action {
            border: none;
            background: none;
            padding: 5px 10px;
            border-radius: 8px;
            transition: 0.3s;
        }
        .btn-view { color: #888; }
        .btn-view:hover { color: var(--mira-pink-dark); background: var(--mira-bg); }
        .btn-delete { color: #ff8a8a; }
        .btn-delete:hover { color: #ff4d4d; background: #fff5f5; }

    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-start mb-5">
        <div>
            <h1 class="mira-title">Message Directory</h1>
            <p class="text-muted">บริหารจัดการข้อความติดต่อและสอบถามจากลูกค้า</p>
        </div>
        <a href="../index_ad.php" class="btn-dashboard">
            <i class="bi bi-grid me-2"></i> Dashboard
        </a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box" style="background: #fce4ec; color: #f06292;">
                    <i class="bi bi-chat-left-dots"></i>
                </div>
                <div class="text-muted small">ข้อความทั้งหมด</div>
                <div class="h2 fw-bold mb-0"><?= count($messages) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box" style="background: #e3f2fd; color: #42a5f5;">
                    <i class="bi bi-envelope-paper"></i>
                </div>
                <div class="text-muted small">มาใหม่เดือนนี้</div>
                <div class="h2 fw-bold mb-0">--</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box" style="background: #f1f8e9; color: #8bc34a;">
                    <i class="bi bi-check2-all"></i>
                </div>
                <div class="text-muted small">ตอบกลับแล้ว</div>
                <div class="h2 fw-bold mb-0">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box" style="background: #fff3e0; color: #ffb74d;">
                    <i class="bi bi-star"></i>
                </div>
                <div class="text-muted small">ระดับความสำคัญ</div>
                <div class="h2 fw-bold mb-0">Normal</div>
            </div>
        </div>
    </div>

    <div class="messages-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0" style="color: #333;">รายการข้อความทั้งหมด</h4>
        </div>

        <div class="search-container d-flex align-items-center">
            <i class="bi bi-search text-muted me-3"></i>
            <input type="text" id="msgSearch" class="search-input" placeholder="ค้นหาหัวข้อหรือชื่อลูกค้า...">
        </div>

        
        <div class="table-responsive">
            <table class="table" id="msgTable">
                <thead>
                    <tr>
                        <th width="25%">ข้อมูลลูกค้า</th>
                        <th width="15%">วันที่ได้รับ</th>
                        <th width="20%">หัวข้อสอบถาม</th>
                        <th width="30%">ตัวอย่างข้อความ</th>
                        <th width="10%" class="text-center">จัดการ</th>
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
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-secondary fw-bold" style="width: 40px; height: 40px; margin-right: 12px; font-size: 0.8rem;">
                                        <?= mb_substr($msg['fullname'] ?? 'G', 0, 1) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.95rem;"><?= htmlspecialchars($msg['fullname'] ?? 'บุคคลทั่วไป') ?></div>
                                        <div class="text-muted small" style="font-size: 0.8rem;"><?= htmlspecialchars($msg['user_email'] ?? 'ไม่มีอีเมล') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="small text-muted">
                                <?= date('Y-m-d H:i', strtotime($msg['created_at'])) ?>
                            </td>
                            <td>
                                <span class="badge rounded-pill fw-normal px-3 py-2" style="background: #fdf5f7; color: var(--mira-pink-dark); font-size: 0.8rem;">
                                    <?= htmlspecialchars($msg['subject']) ?>
                                </span>
                            </td>


                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 280px;">
                                    <?= htmlspecialchars($msg['message']) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn-action btn-view" onclick="viewDetail('<?= htmlspecialchars(addslashes($msg['message'])) ?>', '<?= htmlspecialchars($msg['subject']) ?>')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-action btn-delete" onclick="confirmDelete(<?= $msg['message_id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
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
// ระบบค้นหาเบื้องต้น
document.getElementById('msgSearch').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('#msgTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
    });
});

function viewDetail(text, subject) {
    Swal.fire({
        title: subject,
        html: `<div class="text-start p-3" style="font-size: 0.95rem; line-height: 1.6; color: #666;">${text}</div>`,
        confirmButtonColor: '#a34a67',
        confirmButtonText: 'เข้าใจแล้ว',
        customClass: {
            popup: 'rounded-5',
            confirmButton: 'rounded-pill px-4'
        }
    });
}



function confirmDelete(id) {
    Swal.fire({
        title: 'ลบข้อความนี้?',
        text: "ข้อมูลนี้จะหายไปจากระบบถาวร",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff8a8a',
        cancelButtonColor: '#eee',
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก',
        customClass: {
            popup: 'rounded-5',
            confirmButton: 'rounded-pill px-4',
            cancelButton: 'rounded-pill px-4 text-dark'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'manage_messages.php?delete_id=' + id;
        }
    })
}
</script>

<?php if ($status_msg): ?>
    <script>
        Swal.fire({ 
            icon: 'success', 
            title: 'เรียบร้อยค่ะ', 
            text: '<?= $status_msg ?>', 
            confirmButtonColor: '#a34a67',
            customClass: { popup: 'rounded-5', confirmButton: 'rounded-pill px-4' }
        });
    </script>
<?php endif; ?>

</body>
</html>