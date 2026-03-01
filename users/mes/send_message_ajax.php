<?php
session_start();
require_once "../../config.php";

$user_id = $_SESSION['user_id'] ?? null;
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';
$attachment_path = null;

// ตรวจสอบการอัปโหลดไฟล์
if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] == 0) {
    $upload_dir = "../../uploads/chat/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $file_ext = pathinfo($_FILES['chat_file']['name'], PATHINFO_EXTENSION);
    $new_filename = "chat_" . time() . "_" . uniqid() . "." . $file_ext;
    $target_path = $upload_dir . $new_filename;

    if (move_uploaded_file($_FILES['chat_file']['tmp_name'], $target_path)) {
        $attachment_path = $new_filename;
    }
}

// ... (โค้ดอัปโหลดไฟล์เดิมของคุณ) ...

if ($user_id && ($message || $attachment_path)) {
    // เพิ่ม is_read = 0 เข้าไปในการ Insert ด้วย
    $stmt = $conn->prepare("INSERT INTO contact_messages (user_id, subject, message, attachment_path, is_read) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([$user_id, $subject, $message, $attachment_path]);
    echo "success";
}