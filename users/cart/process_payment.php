<?php
session_start();
require_once "../../config.php";

if (empty($_SESSION['cart'])) {
    header("Location: mycart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) throw new Exception("กรุณาเข้าสู่ระบบ");

        // รับค่าช่องทางการชำระเงินที่ส่งมาจากหน้า Checkout
        $payment_method = $_POST['payment_method'] ?? 'Bank Transfer';
        $applied_promo_id = $_POST['applied_promo_id'] ?? 0;

        // --- 1. คำนวณราคาสินค้าจริง ---
        $total_price_raw = 0;
        $ids = implode(',', array_keys($_SESSION['cart']));
        $stmt_products = $conn->query("SELECT * FROM products WHERE product_id IN ($ids)");
        $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $p) {
            $qty = $_SESSION['cart'][$p['product_id']];
            $total_price_raw += $p['price'] * $qty;
        }

        // --- 2. ตรวจสอบคูปองและคำนวณส่วนลด ---
        $final_total_price = $total_price_raw;
        if ($applied_promo_id > 0) {
            $sql_v = "SELECT p.* FROM user_vouchers uv 
                      JOIN promotions p ON uv.promo_id = p.promo_id 
                      WHERE uv.user_id = ? AND uv.promo_id = ? AND uv.used_status = 'unused'";
            $stmt_v = $conn->prepare($sql_v);
            $stmt_v->execute([$user_id, $applied_promo_id]);
            $voucher = $stmt_v->fetch();

            if ($voucher) {
                $discount = ($voucher['discount_type'] == 'percentage')
                    ? ($total_price_raw * $voucher['discount_value']) / 100
                    : $voucher['discount_value'];
                $final_total_price = max(0, $total_price_raw - $discount);

                // อัปเดตสถานะคูปอง
                $sql_update_v = "UPDATE user_vouchers SET used_status = 'used', used_at = NOW() 
                                 WHERE user_id = ? AND promo_id = ?";
                $conn->prepare($sql_update_v)->execute([$user_id, $applied_promo_id]);
            }
        }

        // --- 3. บันทึก Order ---
        $sql_order = "INSERT INTO orders (user_id, total_price, status, order_date) VALUES (?, ?, 'pending', NOW())";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->execute([$user_id, $final_total_price]);
        $order_id = $conn->lastInsertId();

        // --- 4. บันทึกรายการสินค้า ---
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($products as $p) {
            $qty = $_SESSION['cart'][$p['product_id']];
            $stmt_item->execute([$order_id, $p['product_id'], $qty, $p['price']]);
        }

        // --- 5. จัดการไฟล์สลิป (เฉพาะกรณีโอนเงิน) ---
        $slip_name = null;
        if ($payment_method === 'Bank Transfer') {
            if (isset($_FILES['slip_img']) && $_FILES['slip_img']['error'] == 0) {
                $ext = strtolower(pathinfo($_FILES['slip_img']['name'], PATHINFO_EXTENSION));
                $slip_name = "SLIP_" . $order_id . "_" . time() . "." . $ext;
                $upload_path = "../../uploads/slips/";
                if (!is_dir($upload_path)) mkdir($upload_path, 0777, true);
                move_uploaded_file($_FILES['slip_img']['tmp_name'], $upload_path . $slip_name);
            } else {
                // ถ้าเลือกโอนเงินแต่ไม่มีไฟล์ส่งมา ให้แจ้ง Error
                throw new Exception("กรุณาอัปโหลดหลักฐานการโอนเงิน");
            }
        }

        // --- 6. บันทึก Payment ---
        // กำหนดสถานะเบื้องต้น: ถ้าโอนเงินให้เป็น success (รอตรวจสอบ) ถ้า COD ให้เป็น success หรือตามที่คุณกำหนดในระบบหลังบ้าน
        $payment_status = 'success'; 
        
        $sql_payment = "INSERT INTO payments (order_id, payment_proof, payment_method, payment_date, payment_status) 
                        VALUES (?, ?, ?, NOW(), ?)";
        $conn->prepare($sql_payment)->execute([
            $order_id, 
            $slip_name, 
            $payment_method, 
            $payment_status
        ]);

        $conn->commit();
        unset($_SESSION['cart']);
        header("Location: checkout.php?status=success");
        exit;

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        die("Error: " . $e->getMessage());
    }
}