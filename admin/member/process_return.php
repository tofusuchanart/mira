<?php
session_start();
require_once "../../config.php"; // ปรับ Path ตามจริงของคุณ
/** @var PDO $conn */

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $reason = $_POST['reason'];
    $remark = $_POST['remark'];
    $restore_stock = isset($_POST['restore_stock']) ? true : false;

    try {
        $conn->beginTransaction();

        // 1. ดึงข้อมูลสินค้าในออเดอร์นี้ออกมาก่อน (เพื่อเอาไปคืนสต็อก)
        if ($restore_stock) {
            $stmtItems = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmtItems->execute([$order_id]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                // เพิ่มสต็อกกลับเข้าไปในตาราง products
                $updateStock = $conn->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
                $updateStock->execute([$item['quantity'], $item['product_id']]);
            }
        }

        // 2. อัปเดตสถานะออเดอร์ และบันทึกเหตุผลการคืน
$updateOrder = $conn->prepare("
    UPDATE orders SET 
        status = 'cancelled', 
        return_reason = ?, 
        return_remark = ?, 
        returned_at = NOW(),
        updated_at = NOW() 
    WHERE order_id = ?
");
$updateOrder->execute([$reason, $remark, $order_id]);

        // 3. (Optional) บันทึก Log การคืนสินค้า (ถ้าคุณมีตารางเก็บ Log)
        /*
        $logSql = "INSERT INTO order_logs (order_id, action, note) VALUES (?, 'return', ?)";
        $conn->prepare($logSql)->execute([$order_id, "เหตุผล: $reason | หมายเหตุ: $remark"]);
        */

        $conn->commit();
        
        // ส่งข้อความกลับไปแสดงใน SweetAlert2 ที่หน้าหลัก
        $_SESSION['success'] = "คืนสินค้าออเดอร์ #$order_id และจัดการสต็อกเรียบร้อยแล้วค่ะ 🥺✨";

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// ดีดกลับไปหน้าเดิม
header("Location: order_history.php");
exit();