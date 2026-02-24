<?php
// get_messages.php
require_once "../../config.php";

if (isset($_GET['user_id'])) {
    $user_id = mysqli_real_escape_with_str($conn, $_GET['user_id']);

    // ดึงข้อมูลข้อความทั้งหมดที่เกี่ยวข้องกับ user_id นี้
    $sql = "SELECT * FROM contact_messages 
            WHERE user_id = '$user_id' 
            ORDER BY created_at ASC";
    
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            
            // 1. แสดงข้อความจากฝั่งลูกค้า (User)
            if (!empty($row['message'])) {
                echo '<div class="msg msg-user">';
                echo htmlspecialchars($row['message']);
                
                // ถ้ามีรูปภาพแนบมาด้วย (attachment_path)
                if (!empty($row['attachment_path'])) {
                    echo '<br><img src="../../uploads/chat/'.$row['attachment_path'].'" style="max-width:200px; border-radius:10px; margin-top:5px;">';
                }
                
                echo '<div style="font-size:10px; color:#aaa; margin-top:5px;">' . date('H:i', strtotime($row['created_at'])) . '</div>';
                echo '</div>';
            }

            // 2. แสดงข้อความจากฝั่งแอดมิน (Admin Reply)
            // เช็คว่ามีคำตอบจากแอดมินในแถวนี้หรือไม่
            if (!empty($row['admin_reply'])) {
                echo '<div class="msg msg-admin">';
                echo htmlspecialchars($row['admin_reply']);
                
                // แสดงเวลาที่แอดมินตอบ (ถ้ามี replied_at)
                $reply_time = !empty($row['replied_at']) ? date('H:i', strtotime($row['replied_at'])) : date('H:i', strtotime($row['created_at']));
                echo '<div style="font-size:10px; color:#eee; margin-top:5px; text-align:right;">' . $reply_time . '</div>';
                echo '</div>';
            }
        }
    } else {
        echo '<div style="text-align: center; color: #ccc; margin-top: 50px;">ไม่มีประวัติการสนทนา</div>';
    }
}

// ฟังก์ชันช่วยกัน SQL Injection (กรณีไม่ได้ใช้ Prepared Statement)
function mysqli_real_escape_with_str($conn, $str) {
    return mysqli_real_escape_string($conn, $str);
}
?>