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

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] === 'owner') {
        header("Location: ../admin.php");
    } else {
        header("Location: dashboard_customer.php");
    }
    exit();

} else {
    echo "❌ Email หรือ Password ไม่ถูกต้อง";
}
?>
