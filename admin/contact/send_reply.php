<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $reply = $_POST['reply'];
    $attachment_path = null;
    $message_type = 'text';

    // จัดการอัปโหลดไฟล์ (ถ้ามี)
    if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] == 0) {
        $target_dir = "uploads/chat/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES["chat_file"]["name"], PATHINFO_EXTENSION));
        $file_name = time() . '_' . uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["chat_file"]["tmp_name"], $target_file)) {
            $attachment_path = $target_file;
            $image_types = ['jpg', 'jpeg', 'png', 'gif'];
            $video_types = ['mp4', 'mov', 'avi'];
            
            if (in_array($file_ext, $image_types)) $message_type = 'image';
            elseif (in_array($file_ext, $video_types)) $message_type = 'video';
        }
    }

    // ในระบบของคุณ การตอบกลับคือการ UPDATE แถวล่าสุดที่ยังไม่ได้ตอบ หรือ INSERT แถวใหม่
    // เพื่อให้ประวัติไม่หาย แนะนำให้ INSERT แถวใหม่เป็นในนาม Admin
    $sql = "INSERT INTO contact_messages (user_id, subject, message, admin_reply, message_type, attachment_path, replied_at) 
            VALUES (?, 'Admin Reply', '', ?, ?, ?, CURRENT_TIMESTAMP)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $reply, $message_type, $attachment_path);
    
    if ($stmt->execute()) {
        // อัปเดตรายการเก่าของลูกค้าให้ถือว่า "อ่านแล้ว" (Replied)
        $update = "UPDATE contact_messages SET replied_at = CURRENT_TIMESTAMP WHERE user_id = ? AND admin_reply IS NULL";
        $stmt_up = $conn->prepare($update);
        $stmt_up->bind_param("i", $user_id);
        $stmt_up->execute();
        
        echo "success";
    } else {
        echo "error";
    }
}
?>