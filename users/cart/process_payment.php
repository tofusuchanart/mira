<?php
session_start();
require_once "../../config.php";

// 1. ตรวจสอบว่าต้องมีสินค้าในตะกร้า
if (empty($_SESSION['cart'])) {
    header("Location: mycart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // 2. คำนวณราคาทั้งหมด
        $total_price = 0;
        $ids = implode(',', array_keys($_SESSION['cart']));
        $sql_products = "SELECT * FROM products WHERE product_id IN ($ids)";
        $stmt_products = $conn->query($sql_products);
        $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $p) {
            $qty = $_SESSION['cart'][$p['product_id']];
            $total_price += $p['price'] * $qty;
        }

        // 3. บันทึกข้อมูลลงตาราง orders (ตามโครงสร้าง SQL ของคุณ)
        // ตรวจสอบว่ามี user_id ใน session ไหม ถ้าไม่มีให้ใช้ id จากการ login (สมมติว่าเป็น $_SESSION['user_id'])
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        if (!$user_id) {
            throw new Exception("กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อ");
        }

        $sql_order = "INSERT INTO orders (user_id, total_price, status, order_date) 
                      VALUES (?, ?, 'pending', NOW())";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->execute([$user_id, $total_price]);
        
        $order_id = $conn->lastInsertId();

        // 4. บันทึกรายการสินค้าลงตาราง order_items (ตามโครงสร้าง SQL ของคุณ)
        $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);

        foreach ($products as $p) {
            $qty = $_SESSION['cart'][$p['product_id']];
            $stmt_item->execute([$order_id, $p['product_id'], $qty, $p['price']]);
        }

        // 5. จัดการไฟล์สลิปและบันทึกลงตาราง payments (ตามโครงสร้าง SQL ของคุณ)
        $slip_name = null;
        if (isset($_FILES['slip_img']) && $_FILES['slip_img']['error'] == 0) {
            $ext = pathinfo($_FILES['slip_img']['name'], PATHINFO_EXTENSION);
            $slip_name = "SLIP_" . time() . "_" . uniqid() . "." . $ext;
            
            // ตรวจสอบ path โฟลเดอร์ (ปรับให้ตรงกับที่คุณสร้างไว้)
            $upload_path = "../../uploads/slips/"; 
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            move_uploaded_file($_FILES['slip_img']['tmp_name'], $upload_path . $slip_name);
        }

        $sql_payment = "INSERT INTO payments (order_id, payment_proof, payment_method, payment_date, payment_status) 
                        VALUES (?, ?, 'Bank Transfer', NOW(), 'success')";
        $stmt_payment = $conn->prepare($sql_payment);
        $stmt_payment->execute([$order_id, $slip_name]);

        $conn->commit();

        // 6. ล้างตะกร้าและไปหน้าแจ้งผล
        unset($_SESSION['cart']);
        header("Location: checkout.php?status=success");
        exit;

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        // พ่น Error จริงออกมาดูถ้ายังพังอยู่
        die("Error: " . $e->getMessage());
    }
}