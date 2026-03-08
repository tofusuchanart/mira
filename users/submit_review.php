<?php
session_start();
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $product_id = $_POST['product_id'];
    $order_id = $_POST['order_id']; // รับค่า order_id เพิ่ม
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    // 1. กรองคำหยาบ (ใส่เพิ่มเพื่อความสุภาพ)
    $bad_words = ["มึง", "กู", "ควย", "เย็ด", "สัด", "เหี้ย", "ไอ้สัส"]; 
    $filtered_comment = str_ireplace($bad_words, "***", $comment);

    $img_name = null;
    $vid_name = null;

    // 2. จัดการอัปโหลดไฟล์รูปภาพ
    if (!empty($_FILES['review_image']['name'])) {
        $img_name = time() . "_img_" . $_FILES['review_image']['name'];
        move_uploaded_file($_FILES['review_image']['tmp_name'], "../uploads/reviews/" . $img_name);
    }
    
    // 3. จัดการอัปโหลดไฟล์วิดีโอ
    if (!empty($_FILES['review_video']['name'])) {
        $vid_name = time() . "_vid_" . $_FILES['review_video']['name'];
        move_uploaded_file($_FILES['review_video']['tmp_name'], "../uploads/reviews/" . $vid_name);
    }

    // 4. บันทึกลงฐานข้อมูล
    try {
       // ... (โค้ดส่วนอัปโหลดไฟล์ของคุณเหมือนเดิม) ...

// เพิ่ม order_id ในคำสั่ง INSERT
$sql = "INSERT INTO reviews (product_id, user_id, order_id, rating, comment, review_image, review_video, review_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->execute([
    $product_id, 
    $user_id, 
    $order_id, // เพิ่มตัวนี้เข้าไป
    $rating, 
    $comment, 
    $img_name, 
    $vid_name
]);

header("Location: od_details.php?id=" . $order_id);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}