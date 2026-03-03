<?php
session_start();
require '../config.php';

// รับค่าจาก Form
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// --- 1. ตรวจสอบความยาวรหัสผ่าน (Server-side Validation) ---
if (strlen($password) < 6) {
    header("Location: login.php?status=invalid_password");
    exit();
}

// --- 2. เตรียม SQL (ดึงข้อมูลผู้ใช้ตาม Email) ---
// หมายเหตุ: แนะนำให้ใช้การเก็บรหัสผ่านแบบ password_hash ในอนาคต
$sql = "SELECT * FROM users WHERE email = :email AND password = :password";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // --- 3. เข้าสู่ระบบสำเร็จ ---
        $_SESSION['user_id']  = $user['user_id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email']    = $user['email'];
        $_SESSION['role']     = $user['role'];

        // แยกเส้นทางตามสิทธิ์ (Role)
        if ($user['role'] === 'owner') {
            header("Location: ../admin/index_ad.php");
        } else {
            header("Location: ../users/index_users.php");
        }
        exit();

    } else {
        // --- 4. อีเมลหรือรหัสผ่านผิด ---
        header("Location: login.php?status=error");
        exit();
    }

} catch (PDOException $e) {
    // กรณี Error เกี่ยวกับ Database
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
}
?>