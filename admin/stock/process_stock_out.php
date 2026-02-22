<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $p_id = $_POST['product_id'];
    $qty = $_POST['quantity'];
    $remark = $_POST['remark']; // เหตุผลการตัดออก
    $user_name = $_SESSION['fullname'] ?? 'Admin';

    // 1. ตรวจสอบสต็อกปัจจุบันอีกครั้งป้องกันค่าติดลบ
    $check = $conn->query("SELECT stock FROM products WHERE product_id = $p_id")->fetch_assoc();
    
    if ($check['stock'] >= $qty) {
        // 2. อัปเดตลดจำนวนสินค้า
        $update_sql = "UPDATE products SET stock = stock - $qty WHERE product_id = $p_id";
        
        // 3. บันทึกลง Log เป็นประเภท 'out'
        $log_sql = "INSERT INTO stock_log (product_id, type, quantity, remark, created_by) 
                    VALUES ('$p_id', 'out', '$qty', '$remark', '$user_name')";

        if ($conn->query($update_sql) && $conn->query($log_sql)) {
            header("Location: manage_stock.php?status=success_out");
        } else {
            header("Location: manage_stock.php?status=error");
        }
    } else {
        // กรณีจำนวนที่กรอกมากกว่าที่มีอยู่จริง
        header("Location: manage_stock.php?status=insufficient");
    }
}
?>