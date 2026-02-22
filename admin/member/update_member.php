<?php
session_start();
require_once "../../config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $user_id  = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $address  = $_POST['address'];
    $role     = $_POST['role'];

    try {
        $sql = "UPDATE users SET fullname = :f, email = :e, phone = :p, address = :a, role = :r WHERE user_id = :id";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            ':f' => $fullname, ':e' => $email, ':p' => $phone, 
            ':a' => $address, ':r' => $role, ':id' => $user_id
        ]);

        if ($result) {
            $_SESSION['success'] = "อัปเดตข้อมูลเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดต";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("Location: member.php"); // ปรับชื่อไฟล์ให้ตรงกับหน้าตารางสมาชิกของคุณ
    exit();
} else {
    // บรรทัดที่เคยมีปัญหา (line 47) ต้องแน่ใจว่าปีกกาข้างบนปิดครบแล้ว
    header("Location: member.php");
    exit();
}
?>