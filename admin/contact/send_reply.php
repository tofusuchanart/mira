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
    // ถ้าไม่มีข้อความพิมพ์มา แต่มีรูป ให้ตั้งค่า admin_reply เป็นช่องว่าง (Space) แทนค่าว่างเปล่า (Empty String)
    // เพื่อให้ Logic ฝั่งดึงข้อมูล (fetch) ตรวจเจอว่าแถวนี้มีข้อมูลแอดมิน
    if ($reply === '' && $attachment_path !== null) {
        $reply = ' '; 
    }

    // ถ้าไม่มีทั้งข้อความและรูป ไม่ต้องบันทึก
    if ($reply === '' && $attachment_path === null) {
        echo "empty_message";
        exit;
    }

    try {
        // ใช้ PDO ในการ Insert ข้อความตอบกลับ
        // สำคัญ: subject ต้องเป็น 'Admin Reply' เสมอเพื่อใช้แยกฝั่งในหน้า Chat
        $sql = "INSERT INTO contact_messages (user_id, subject, message, admin_reply, message_type, attachment_path, replied_at, is_read) 
                VALUES (:user_id, 'Admin Reply', '', :reply, :m_type, :attach, CURRENT_TIMESTAMP, 1)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'user_id' => $user_id,
            'reply'   => $reply,
            'm_type'  => $message_type,
            'attach'  => $attachment_path
        ]);
        
        if ($result) {
            // อัปเดตข้อความเก่าๆ ที่ลูกค้าส่งมา ให้ถือว่าแอดมินตอบแล้วทั้งหมด
            $update = "UPDATE contact_messages 
                       SET replied_at = CURRENT_TIMESTAMP, is_read = 1
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