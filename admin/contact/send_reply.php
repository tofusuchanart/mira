<?php
include '../../config.php';

// ตรวจสอบว่าได้รับการส่งค่ามาจริงไหม
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $reply = isset($_POST['reply']) ? trim($_POST['reply']) : ''; // ตัดช่องว่างออก
    $attachment_path = null;
    $message_type = 'text';

    // จัดการอัปโหลดไฟล์
    if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] == 0) {
        $target_dir = "../../uploads/chat/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES["chat_file"]["name"], PATHINFO_EXTENSION));
        $file_name = time() . '_' . uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["chat_file"]["tmp_name"], $target_file)) {
            $attachment_path = $file_name;
            $image_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_ext, $image_types)) {
                $message_type = 'image';
            }
        }
    }

    // ป้องกันปัญหา "ส่งรูปอย่างเดียวแล้วหลงฝั่ง"
    if ($reply === '' && $attachment_path !== null) {
        $reply = ' '; 
    }

    // ถ้าไม่มีทั้งข้อความและรูป ไม่ต้องบันทึก
    if ($reply === '' && $attachment_path === null) {
        echo "empty_message";
        exit;
    }

    try {
        // *** จุดแก้ไขที่ 1: เปลี่ยนค่าสุดท้ายจาก 1 เป็น 0 เพื่อให้แจ้งเตือนฝั่งลูกค้า ***
        $sql = "INSERT INTO contact_messages (user_id, subject, message, admin_reply, message_type, attachment_path, replied_at, is_read) 
                VALUES (:user_id, 'Admin Reply', '', :reply, :m_type, :attach, CURRENT_TIMESTAMP, 0)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'user_id' => $user_id,
            'reply'   => $reply,
            'm_type'  => $message_type,
            'attach'  => $attachment_path
        ]);
        
        if ($result) {
            // *** จุดแก้ไขที่ 2: เปลี่ยน is_read = 1 เป็น is_read = 0 ***
            // เพื่อให้ข้อความชุดนี้ทั้งหมดแสดงสถานะ "ยังไม่อ่าน" สำหรับลูกค้า
            $update = "UPDATE contact_messages 
                       SET replied_at = CURRENT_TIMESTAMP, is_read = 0
                       WHERE user_id = :u_id AND (admin_reply IS NULL OR admin_reply = '' OR admin_reply = ' ')";
            $stmt_up = $conn->prepare($update);
            $stmt_up->execute(['u_id' => $user_id]);
            
            echo "success";
        } else {
            echo "error_db";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "invalid_request";
}
?>