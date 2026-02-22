<?php
$host = "localhost";
$username = "root";      // ปกติ XAMPP ใช้ root
$password = "";          // ปกติ XAMPP รหัสผ่านว่างเปล่า
$dbname = "mira_db";     // ❗ เปลี่ยนเป็นชื่อฐานข้อมูลที่คุณสร้างใน phpMyAdmin

// สร้างการเชื่อมต่อ
$conn = new mysqli($host, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตั้งค่าให้รองรับภาษาไทย
$conn->set_charset("utf8mb4");
?>