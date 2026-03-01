<?php
session_start();
require_once "../../config.php";

$user_id = $_SESSION['user_id'] ?? null;
$topic = $_GET['topic'] ?? '';

$html = '';
$adminCount = 0;

// แก้ไขส่วน Query ให้ยืดหยุ่นขึ้น
if ($user_id) {
    // ดึงข้อความทั้งหมดของ user คนนี้ โดยเรียงตามเวลา
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll();

    // --- แก้ไขไฟล์ fetch_messages.php ---

foreach ($messages as $msg) {
    // --- 1. จัดการรูปภาพ (Attachment) ---
    // เช็คก่อนว่าในแถวนี้มีรูปไหม?
    if (!empty($msg['attachment_path'])) {
        // ถ้ามีรูป และ admin_reply ว่าง => รูปนี้ลูกค้าเป็นคนส่ง
        if (empty($msg['admin_reply'])) {
            $html .= '<div class="bubble me">';
            $html .= '<img src="../../uploads/chat/'.$msg['attachment_path'].'" class="img-fluid rounded d-block" style="max-height:200px; cursor:pointer;" onclick="window.open(this.src)">';
            $html .= '</div>';
        } 
        // ถ้ามีรูป และ admin_reply ไม่ว่าง => รูปนี้แอดมินเป็นคนส่งมาพร้อมคำตอบ
        else {
            $html .= '<div class="bubble admin">';
            $html .= '<img src="../../uploads/chat/'.$msg['attachment_path'].'" class="img-fluid rounded d-block" style="max-height:200px; cursor:pointer;" onclick="window.open(this.src)">';
            $html .= '</div>';
        }
    }

    // --- 2. จัดการข้อความตัวอักษร (Text) ---
    // ข้อความจากฝั่งลูกค้า (Me)
    if (!empty($msg['message'])) {
        $html .= '<div class="bubble me">' . htmlspecialchars($msg['message']) . '</div>';
    }
    
    // ข้อความจากฝั่งแอดมิน (Admin)
    if (!empty($msg['admin_reply'])) {
        $html .= '<div class="bubble admin">' . htmlspecialchars($msg['admin_reply']) . '</div>';
        $adminCount++;
    }
}
}

echo json_encode(['html' => $html, 'adminCount' => $adminCount]);