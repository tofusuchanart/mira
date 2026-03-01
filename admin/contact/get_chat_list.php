<?php
require_once "../../config.php";

try {
    // แก้ไข SQL: ให้นับจากคอลัมน์ is_read ที่เราเพิ่งเพิ่มเข้าไป
    $sql = "SELECT 
                u.user_id, 
                u.fullname, 
                u.profile_img,
                (SELECT COUNT(*) FROM contact_messages cm2 
                 WHERE cm2.user_id = u.user_id 
                 AND cm2.is_read = 0) as unread_count, -- เปลี่ยนตรงนี้ให้นับ is_read = 0
                MAX(cm.created_at) as last_chat
            FROM users u
            INNER JOIN contact_messages cm ON u.user_id = cm.user_id
            GROUP BY u.user_id
            ORDER BY last_chat DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $user_id = $row['user_id'];
        $fullname = htmlspecialchars($row['fullname']);
        $unread = (int)$row['unread_count'];

        // กำหนดรูปโปรไฟล์ (โค้ดเดิมของคุณ)
        $profile_pic = !empty($row['profile_img']) ? "../../register/photo/" . $row['profile_img'] : "https://ui-avatars.com/api/?name=" . urlencode($fullname) . "&background=f8e1e7&color=9c3353";

        // เพิ่ม class 'active' หรือจัดการ UI ตามความเหมาะสม
        echo '<div class="chat-item" onclick="loadMessages(' . $user_id . ', \'' . $fullname . '\')">';
        echo '  <div class="user-img-wrapper">'; // เพิ่ม wrapper เพื่อความสวยงาม
        echo '    <img src="' . $profile_pic . '" class="user-img" onerror="this.src=\'https://ui-avatars.com/api/?name=' . urlencode($fullname) . '\'">';
        echo '  </div>';
        echo '  <div class="user-info">';
        echo '    <h4>' . $fullname . '</h4>';
        echo '    <p>' . ($unread > 0 ? "<strong>มี $unread ข้อความใหม่</strong>" : "ล่าสุด: " . date('d/m H:i', strtotime($row['last_chat']))) . '</p>';
        echo '  </div>';

        // วงกลมแดง (Badge) จะแสดงเมื่อ unread_count > 0 เท่านั้น
        if ($unread > 0) {
            echo '  <span class="badge">' . $unread . '</span>';
        }
        echo '</div>';
    }
} catch (PDOException $e) {
    echo "Query Error: " . $e->getMessage();
}