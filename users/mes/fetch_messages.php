<?php
session_start();
require_once "../../config.php";

$user_id = $_SESSION['user_id'] ?? null;
$topic = $_GET['topic'] ?? '';

$html = '';
$adminCount = 0;

if ($user_id && $topic) {
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE user_id = ? AND subject = ? ORDER BY created_at ASC");
    $stmt->execute([$user_id, $topic]);
    $messages = $stmt->fetchAll();

    foreach ($messages as $msg) {
        // ข้อความลูกค้า
        $html .= '<div class="bubble me">';
        // ถ้ามีไฟล์แนบ (รูปภาพ)
        if (!empty($msg['attachment_path'])) {
            $html .= '<img src="../../uploads/chat/'.$msg['attachment_path'].'" class="img-fluid rounded mb-2 d-block" style="max-height:200px;">';
        }
        if (!empty($msg['message'])) {
            $html .= htmlspecialchars($msg['message']);
        }
        $html .= '</div>';
        
        // ข้อความแอดมิน
        if (!empty($msg['admin_reply'])) {
            $html .= '<div class="bubble admin">' . htmlspecialchars($msg['admin_reply']) . '</div>';
            $adminCount++;
        }
    }
}

echo json_encode(['html' => $html, 'adminCount' => $adminCount]);