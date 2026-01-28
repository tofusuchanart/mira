<?php
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $user_id = $_POST['user_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    try {
        $sql = "INSERT INTO reviews (product_id, user_id, rating, comment, review_date) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$product_id, $user_id, $rating, $comment])) {
            echo "<script>alert('ขอบคุณสำหรับรีวิวของคุณ!'); window.location='index_users.php';</script>";
        }
    } catch(PDOException $e) {
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>