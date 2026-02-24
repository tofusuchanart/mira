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

if ($user_id && ($message || $attachment_path)) {
    // เพิ่ม attachment_path เข้าไปใน SQL
    $stmt = $conn->prepare("INSERT INTO contact_messages (user_id, subject, message, attachment_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $subject, $message, $attachment_path]);
    echo "success";
}