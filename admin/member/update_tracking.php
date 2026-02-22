<?php
session_start();
require_once "../../config.php"; // ปรับ Path ตามจริง
/** @var PDO $conn */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? '';
    $tracking = $_POST['tracking_number'] ?? '';

    if ($order_id && $tracking) {
        try {
            $sql = "UPDATE orders SET tracking_number = :tracking WHERE order_id = :id";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                ':tracking' => $tracking,
                ':id' => $order_id
            ]);

            if ($result) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปเดตข้อมูลได้']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    }
}