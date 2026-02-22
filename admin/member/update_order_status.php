<?php
session_start();
require_once "../../config.php";
/** @var PDO $conn */

if (isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];

    try {
        $conn->beginTransaction(); // ใช้ Transaction เพื่อความปลอดภัยของข้อมูล

        // 1. ดึงสถานะปัจจุบันมาเช็คก่อน
        $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $current_status = $stmt->fetchColumn();

        // 2. Logic การตัดสต็อก (เมื่อเปลี่ยนจาก 'pending' เป็น 'paid')
        if ($current_status == 'pending' && $new_status == 'paid') {
            $items = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $items->execute([$order_id]);
            while ($row = $items->fetch(PDO::FETCH_ASSOC)) {
                // ลดสต็อก
                $updateStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
                $updateStock->execute([$row['quantity'], $row['product_id']]);
            }
        }

        // 3. Logic การคืนสต็อก (เมื่อมีการ 'cancelled' หรือ 'return')
        if ($new_status == 'cancelled' && ($current_status == 'paid' || $current_status == 'shipped')) {
            $items = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $items->execute([$order_id]);
            while ($row = $items->fetch(PDO::FETCH_ASSOC)) {
                // เพิ่มสต็อกคืน
                $updateStock = $conn->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
                $updateStock->execute([$row['quantity'], $row['product_id']]);
            }
        }

        // 4. อัปเดตสถานะออเดอร์
        $updateOrder = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $updateOrder->execute([$new_status, $order_id]);

        $conn->commit();
        $_SESSION['success'] = "อัปเดตสถานะออเดอร์ #$order_id เรียบร้อยแล้ว";

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
header("Location: order_history.php");
exit();