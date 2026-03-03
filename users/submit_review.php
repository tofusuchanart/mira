<?php
session_start();
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    $img_name = null;
    $vid_name = null;

    // จัดการอัปโหลดไฟล์ (ตรวจสอบความปลอดภัยเพิ่มเติมในภายหลัง)
    if (!empty($_FILES['review_image']['name'])) {
        $img_name = time() . "_img_" . $_FILES['review_image']['name'];
        move_uploaded_file($_FILES['review_image']['tmp_name'], "../uploads/reviews/" . $img_name);
    }
    if (!empty($_FILES['review_video']['name'])) {
        $vid_name = time() . "_vid_" . $_FILES['review_video']['name'];
        move_uploaded_file($_FILES['review_video']['tmp_name'], "../uploads/reviews/" . $vid_name);
    }

    $sql = "INSERT INTO reviews (product_id, user_id, rating, comment, review_image, review_video) VALUES (?, ?, ?, ?, ?, ?)";
    $conn->prepare($sql)->execute([$product_id, $user_id, $rating, $comment, $img_name, $vid_name]);

    header("Location: product_detail.php?id=" . $product_id);
}