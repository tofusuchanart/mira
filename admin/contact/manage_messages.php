<?php
session_start();
require_once "../../config.php"; 

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

$status_msg = "";

// --- 1. จัดการข้อมูล (POST/GET Actions) ก่อนเริ่มดึงข้อมูลแสดงผล ---

// แทนที่ส่วน 'reply' เดิมด้วยโค้ดชุดใหม่ที่มีการส่ง Email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $msg_id = $_POST['message_id'];
    $reply_text = $_POST['reply_content'];
    
    // อัปเดตข้อมูลลงฐานข้อมูล
    $stmt = $conn->prepare("UPDATE contact_messages SET admin_reply = ?, replied_at = NOW() WHERE message_id = ?");
    if ($stmt->execute([$reply_text, $msg_id])) {
        
        // ดึงข้อมูลอีเมลลูกค้าและหัวข้อเดิมเพื่อส่ง Notification
        $stmt_info = $conn->prepare("SELECT m.subject, u.email, u.fullname FROM contact_messages m 
                                     LEFT JOIN users u ON m.user_id = u.user_id 
                                     WHERE m.message_id = ?");
        $stmt_info->execute([$msg_id]);
        $client = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if ($client && !empty($client['email'])) {
            $to = $client['email'];
            $subject_email = "MIRA Admin ได้ตอบกลับข้อความของคุณแล้ว: " . $client['subject'];
            
            $message_email = "
            <html>
            <head><title>การตอบกลับจาก MIRA</title></head>
            <body style='font-family: Sarabun, sans-serif;'>
                <h2 style='color: #a34a67;'>สวัสดีค่ะ คุณ {$client['fullname']}</h2>
                <p>แอดมินได้ตอบกลับข้อความในหัวข้อ <strong>'{$client['subject']}'</strong> เรียบร้อยแล้วค่ะ</p>
                <div style='background: #fdf2f4; padding: 20px; border-left: 4px solid #a34a67;'>
                    <strong>ข้อความจากแอดมิน:</strong><br>
                    " . nl2br(htmlspecialchars($reply_text)) . "
                </div>
                <p style='color: #888; font-size: 0.8rem; margin-top: 20px;'>
                    ขอบคุณที่ใช้บริการ MIRA ค่ะ<br>
                    *นี่เป็นอีเมลแจ้งเตือนอัตโนมัติ กรุณาอย่าตอบกลับอีเมลนี้
                </p>
            </body>
            </html>";

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: MIRA Support <support@yourdomain.com>" . "\r\n";

            @mail($to, $subject_email, $message_email, $headers);
        }
        $status_msg = "ส่งข้อความตอบกลับและแจ้งเตือนผ่านอีเมลเรียบร้อยแล้วค่ะ ✨";
    }
}

// ส่วนการลบ (เหมือนเดิม)
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE message_id = ?");
    if ($stmt->execute([$delete_id])) {
        $status_msg = "ลบข้อความเรียบร้อยแล้วค่ะ ✨";
    }
}

// --- 2. ดึงข้อมูลจาก Database (หลังจากจัดการ Action เสร็จ ข้อมูลในตารางจะได้เป็นปัจจุบัน) ---

$sql = "SELECT m.*, u.fullname, u.email as user_email 
        FROM contact_messages m 
        LEFT JOIN users u ON m.user_id = u.user_id 
        ORDER BY m.created_at DESC";
$stmt = $conn->query($sql);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 3. คำนวณสถิติ ---
$replied_count = 0;
$new_this_month = 0;
$current_month = date('m');
$current_year = date('Y');

foreach($messages as $m) {
    if(!empty($m['admin_reply'])) $replied_count++; 
    $msg_month = date('m', strtotime($m['created_at']));
    $msg_year = date('Y', strtotime($m['created_at']));
    if($msg_month == $current_month && $msg_year == $current_year) {
        $new_this_month++;
    }
}
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



        /* เพิ่มสไตล์สำหรับปุ่ม Link กลับ Dashboard */
    .back-link {
        text-decoration: none;
        color: #94a3b8; /* สีเทาตามรูป */
        font-size: 0.95rem;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
    }
    .back-link:hover {
        color: #b3365b;
    }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-start mb-5">
        <div>
            <a href="../index_ad.php" class="back-link">
            <i class="bi bi-arrow-left"></i> กลับสู่หน้า Dashboard
        </a>
            <h1 class="mira-title">ข้อความลูกค้า</h1>
            <p class="text-muted">บริหารจัดการข้อความจากลูกค้า</p>
        </div>
        
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
            <div class="h2 fw-bold mb-0"><?= $new_this_month ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="icon-box" style="background: #f1f8e9; color: #8bc34a;">
                <i class="bi bi-check2-all"></i>
            </div>
            <div class="text-muted small">ตอบกลับแล้ว</div>
            <div class="h2 fw-bold mb-0"><?= $replied_count ?></div>
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
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn-action btn-view" title="ดูรายละเอียด" 
                        onclick="viewDetail('<?= htmlspecialchars(addslashes($msg['message'])) ?>', '<?= htmlspecialchars($msg['subject']) ?>', '<?= htmlspecialchars(addslashes($msg['admin_reply'] ?? '')) ?>')">
                        <i class="bi bi-eye"></i>
                    </button>
                    
                    <button class="btn-action" style="color: #42a5f5;" title="ตอบกลับ"
                        onclick="replyMessage(<?= $msg['message_id'] ?>, '<?= htmlspecialchars($msg['fullname'] ?? 'ลูกค้า') ?>')">
                        <i class="bi bi-reply-fill"></i>
                    </button>

                    <button class="btn-action btn-delete" title="ลบข้อความ"
                        onclick="confirmDelete(<?= $msg['message_id'] ?>)">
                        <i class="bi bi-trash"></i>
                    </button>
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

// ฟังก์ชันสำหรับส่งการตอบกลับ
function replyMessage(id, name) {
    Swal.fire({
        title: `ตอบกลับคุณ ${name}`,
        input: 'textarea',
        inputPlaceholder: 'พิมพ์ข้อความตอบกลับที่นี่...',
        inputAttributes: { 'aria-label': 'พิมพ์ข้อความตอบกลับที่นี่' },
        showCancelButton: true,
        confirmButtonColor: '#a34a67',
        confirmButtonText: 'ส่งข้อความ',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-5', confirmButton: 'rounded-pill px-4', cancelButton: 'rounded-pill px-4' },
        preConfirm: (text) => {
            if (!text) { Swal.showValidationMessage('กรุณาพิมพ์ข้อความก่อนส่งค่ะ'); }
            return text;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // สร้าง Form ส่งค่าแบบ POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="message_id" value="${id}">
                <input type="hidden" name="reply_content" value="${result.value}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// แก้ไขฟังก์ชันดูรายละเอียดให้แสดงข้อความที่ตอบไปแล้วด้วย
function viewDetail(text, subject, reply) {
    let content = `<div class="text-start p-3" style="font-size: 0.95rem; line-height: 1.6; color: #666;">
                    <strong>ข้อความจากลูกค้า:</strong><br>${text}`;
    if(reply) {
        content += `<hr><strong style="color: #a34a67;">ข้อความตอบกลับจากแอดมิน:</strong><br>${reply}`;
    }
    content += `</div>`;

    Swal.fire({
        title: subject,
        html: content,
        confirmButtonColor: '#a34a67',
        confirmButtonText: 'ปิดหน้าต่าง',
        customClass: { popup: 'rounded-5', confirmButton: 'rounded-pill px-4' }
    });
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