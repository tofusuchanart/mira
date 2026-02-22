<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $p_id = $_POST['product_id'];
    $qty = $_POST['quantity'];
    $supplier = $_POST['supplier'];
    $remark = $_POST['remark'];
    $user_name = $_SESSION['fullname'] ?? 'Admin'; // อ้างอิงชื่อคนทำรายการ

    // 1. อัปเดตจำนวนในตารางสินค้า
    $update_sql = "UPDATE products SET stock = stock + $qty WHERE product_id = $p_id";
    
    // 2. บันทึกลง Log เพื่อทำรายงาน
    $log_sql = "INSERT INTO stock_log (product_id, type, quantity, supplier, remark, created_by) 
                VALUES ('$p_id', 'in', '$qty', '$supplier', '$remark', '$user_name')";

    if ($conn->query($update_sql) && $conn->query($log_sql)) {
        header("Location: manage_stock.php?status=success");
    } else {
        header("Location: manage_stock.php?status=error");
    }
}
?>