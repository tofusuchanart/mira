<?php
require_once "../../config.php";

// เช็คก่อนเลยว่า $conn เป็น PDO จริงไหม
if (!($conn instanceof PDO)) {
    die("Error: Variable \$conn is not a PDO object. Current type: " . gettype($conn));
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    try {
        $sql = "SELECT * FROM contact_messages 
                WHERE user_id = :user_id 
                ORDER BY created_at ASC";

        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->execute(['user_id' => $user_id]);

            // ใช้การวนลูปแบบดั้งเดิมของ PDO ที่เรียบง่ายที่สุด
            $has_messages = false;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $has_messages = true;
                
                // --- ส่วนแสดงผลข้อความ (เหมือนเดิม) ---
                echo '<div class="msg-group">';
                if (!empty($row['message'])) {
                    echo '<div class="msg msg-user">' . htmlspecialchars($row['message']);
                    if (!empty($row['attachment_path'])) {
                        echo '<br><img src="../../uploads/chat/' . htmlspecialchars($row['attachment_path']) . '" style="max-width:200px; border-radius:10px; margin-top:5px;">';
                    }
                    echo '<div style="font-size:10px; color:#aaa; margin-top:5px;">' . date('H:i', strtotime($row['created_at'])) . '</div></div>';
                }

                if (!empty($row['admin_reply'])) {
                    echo '<div class="msg msg-admin">' . htmlspecialchars($row['admin_reply']);
                    $reply_time = !empty($row['replied_at']) ? date('H:i', strtotime($row['replied_at'])) : date('H:i', strtotime($row['created_at']));
                    echo '<div style="font-size:10px; color:#eee; margin-top:5px; text-align:right;">' . $reply_time . '</div></div>';
                }
                echo '</div>';
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