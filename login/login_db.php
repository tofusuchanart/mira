<?php
session_start();
require '../config.php';

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users 
        WHERE email = :email 
        AND password = :password";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $password);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // เก็บข้อมูลลง Session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['email'] = $user['email']; // เพิ่มสำหรับหน้า contact
    $_SESSION['role'] = $user['role'];

    if ($user['role'] === 'owner') {
        header("Location: ../admin/index_ad.php");
    } else {
        header("Location: ../users/index_users.php");
    }
    exit();
} else {
    // ส่งกลับไปหน้า login พร้อมค่า error
    header("Location: login.php?status=error");
    exit();
}
?>