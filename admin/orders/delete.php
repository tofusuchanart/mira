<?php
session_start();
require_once "../../config.php"; 

// ตรวจสอบสิทธิ์ Admin/Owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

// ตรวจสอบว่ามี id และ status ส่งมาหรือไม่
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status']; // จะรับค่า 'active' หรือ 'inactive' มาจาก JavaScript

    try {
        // ใช้ตัวแปร $status ที่ส่งมา แทนการ Fix ค่า
        $sql = "UPDATE products SET status = ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$status, $id])) {
            // สำเร็จ! กลับไปหน้าเดิม
            header("Location: manage_products.php?update=success");
            exit();
        }
    } catch (PDOException $e) {
        // ถ้าเกิดข้อผิดพลาด
        header("Location: manage_products.php?error=db_error&msg=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // ถ้าส่งค่ามาไม่ครบ
    header("Location: manage_products.php");
    exit();
}