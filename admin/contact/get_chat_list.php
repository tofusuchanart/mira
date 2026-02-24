<?php
// get_chat_list.php
require_once "../../config.php";

// ดึงรายชื่อลูกค้า โดย Join กับข้อความล่าสุดของเขา
$sql = "SELECT 
            u.user_id, 
            u.fullname, 
            u.profile_img,
            -- นับข้อความที่แอดมินยังไม่ได้ตอบ (admin_reply เป็น NULL หรือว่าง)
            (SELECT COUNT(*) FROM contact_messages cm2 
             WHERE cm2.user_id = u.user_id 
             AND (cm2.admin_reply IS NULL OR cm2.admin_reply = '')) as unread_count,
            MAX(cm.created_at) as last_chat
        FROM users u
        INNER JOIN contact_messages cm ON u.user_id = cm.user_id
        GROUP BY u.user_id
        ORDER BY last_chat DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $user_id = $row['user_id'];
        $fullname = htmlspecialchars($row['fullname']);
        $unread = (int)$row['unread_count'];

        // จัดการรูปภาพ (ถ้าไม่มีให้ใช้ Avatar)
        $profile_pic = !empty($row['profile_img']) ? "../../uploads/profiles/" . $row['profile_img'] : "https://ui-avatars.com/api/?name=" . urlencode($fullname) . "&background=f8e1e7&color=9c3353";

        echo '<div class="chat-item" onclick="loadMessages(' . $user_id . ', \'' . $fullname . '\')">';
        echo '  <img src="' . $profile_pic . '" class="user-img" onerror="this.src=\'https://ui-avatars.com/api/?name=' . urlencode($fullname) . '\'">';
        echo '  <div class="user-info">';
        echo '    <h4>' . $fullname . '</h4>';
        echo '    <p>' . ($unread > 0 ? "มี $unread ข้อความใหม่" : "ถามเมื่อ: " . date('d/m H:i', strtotime($row['last_chat']))) . '</p>';
        echo '  </div>';

        if ($unread > 0) {
            echo '  <span class="badge">' . $unread . '</span>';
        }
        echo '</div>';
    }
} else {
    echo '<div style="padding:20px; text-align:center; color:#ccc;">ไม่มีรายการติดต่อ</div>';
}
