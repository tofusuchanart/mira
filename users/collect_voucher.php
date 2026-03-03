<?php
require_once "../config.php";
session_start();

header('Content-Type: application/json');

// 1. เช็คว่า Login หรือยัง
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบก่อนเก็บคูปอง']);
    exit;
}

if (isset($_POST['promo_id'])) {
    $user_id = $_SESSION['user_id'];
    $promo_id = $_POST['promo_id'];

    try {
        // 2. เช็คว่าเคยเก็บโปรโมชั่นนี้ไปหรือยัง
        $check = $conn->prepare("SELECT uv_id FROM user_vouchers WHERE user_id = ? AND promo_id = ?");
        $check->execute([$user_id, $promo_id]);
        
        if ($check->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'คุณเก็บคูปองนี้ไปแล้ว']);
        } else {
            // 3. บันทึกการเก็บคูปอง
            $ins = $conn->prepare("INSERT INTO user_vouchers (user_id, promo_id) VALUES (?, ?)");
            if ($ins->execute([$user_id, $promo_id])) {
                echo json_encode(['status' => 'success', 'message' => 'เก็บคูปองเรียบร้อย!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถบันทึกได้']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบรหัสคูปอง']);
}
?>