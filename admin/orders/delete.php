<?php
require_once "../../config.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // 1. ดึงชื่อไฟล์รูปภาพเดิมมาเพื่อลบทิ้ง
        $stmt_img = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
        $stmt_img->execute([$id]);
        $row = $stmt_img->fetch();
        
        if ($row && !empty($row['image'])) {
            unlink("../photo/" . $row['image']); // ลบไฟล์รูปในโฟลเดอร์
        }

        // 2. ลบข้อมูลจากฐานข้อมูล
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        if ($stmt->execute([$id])) {
            header("Location: ../index_ad.php");
            exit;
        }
    } catch(PDOException $e) {
        die("ลบไม่สำเร็จ: " . $e->getMessage());
    }
}
?>