<?php
$host = "localhost";
$dbname = "mira_db";
$username = "root";
$password = "";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", // เปลี่ยนเป็น utf8mb4 เพื่อรองรับ Emoji
        $username,
        $password
    );
    // ตั้งค่าให้แสดง error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}
?>