<?php
require_once "../../config.php";

// เช็คก่อนเลยว่า $conn เป็น PDO จริงไหม
if (!($conn instanceof PDO)) {
    die("Error: Variable \$conn is not a PDO object. Current type: " . gettype($conn));
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    try {
        // --- ส่วนที่เพิ่มเข้ามา: เคลียร์ Badge เมื่อมีการเปิดอ่าน ---
        // เราจะ Update ข้อความทั้งหมดของ User คนนี้ที่ยังไม่ได้อ่าน (is_read = 0) ให้เป็นอ่านแล้ว (is_read = 1)
        $update_sql = "UPDATE contact_messages 
                       SET is_read = 1 
                       WHERE user_id = :user_id AND is_read = 0";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->execute(['user_id' => $user_id]);
        // -----------------------------------------------------

        $sql = "SELECT * FROM contact_messages 
                WHERE user_id = :user_id 
                ORDER BY created_at ASC";

        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->execute(['user_id' => $user_id]);

            $has_messages = false;
           // --- แก้ไขไฟล์ get_messages.php ---

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $has_messages = true;
    echo '<div class="msg-group">';
    
    // 1. ฝั่งลูกค้า (User): แสดงเมื่อมี message (ที่มาจากลูกค้า) หรือมีรูปที่แอดมินไม่ได้ตอบ
    // เช็คว่าถ้า admin_reply ว่าง แสดงว่าเป็นข้อความ/รูปจากลูกค้า
    if (!empty($row['message']) || (!empty($row['attachment_path']) && empty($row['admin_reply']))) {
        echo '<div class="msg msg-user">';
        
        if (!empty($row['message'])) {
            echo htmlspecialchars($row['message']);
        }
        
        // แสดงรูปเฉพาะที่เป็นของลูกค้าส่งมา
        if (!empty($row['attachment_path']) && empty($row['admin_reply'])) {
            if (!empty($row['message'])) echo '<br>';
            echo '<img src="../../uploads/chat/' . htmlspecialchars($row['attachment_path']) . '" style="max-width:200px; border-radius:10px; margin-top:5px; cursor:pointer;" onclick="window.open(this.src)">';
        }
        
        echo '<div class="msg-time">' . date('H:i', strtotime($row['created_at'])) . '</div></div>';
    }

    // 2. ฝั่งแอดมิน (Admin Reply): แสดงเมื่อมี admin_reply หรือมีรูปที่มาจากการตอบกลับ
    if (!empty($row['admin_reply']) || (!empty($row['attachment_path']) && !empty($row['admin_reply']))) {
        echo '<div class="msg msg-admin">';
        
        // แสดงข้อความแอดมิน
        if (!empty($row['admin_reply'])) {
            echo htmlspecialchars($row['admin_reply']);
        }

        // แสดงรูปภาพที่แอดมินส่ง (สำคัญตรงนี้!)
        if (!empty($row['attachment_path']) && !empty($row['admin_reply'])) {
            if (!empty($row['admin_reply'])) echo '<br>';
            echo '<img src="../../uploads/chat/' . htmlspecialchars($row['attachment_path']) . '" style="max-width:200px; border-radius:10px; margin-top:5px; border:1px solid rgba(255,255,255,0.3); cursor:pointer;" onclick="window.open(this.src)">';
        }

        $reply_time = !empty($row['replied_at']) ? date('H:i', strtotime($row['replied_at'])) : date('H:i', strtotime($row['created_at']));
        echo '<div class="msg-time" style="text-align:right;">' . $reply_time . '</div></div>';
    }
    
    echo '</div>'; // ปิด msg-group
}

            if (!$has_messages) {
                echo '<div style="text-align: center; color: #ccc; margin-top: 50px;">ไม่มีประวัติการสนทนา</div>';
            }
        } else {
            echo "Failed to prepare statement.";
        }

    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
}