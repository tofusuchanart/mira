<?php
// add_category_ajax.php
$conn = new mysqli("localhost", "root", "", "mira_db");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_name'])) {
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    
    // ตรวจสอบว่ามีชื่อนี้อยู่แล้วหรือไม่
    $check = $conn->query("SELECT category_id FROM categories WHERE category_name = '$name'");
    
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'มีหมวดหมู่นี้อยู่แล้ว']);
    } else {
        $sql = "INSERT INTO categories (category_name) VALUES ('$name')";
        if ($conn->query($sql)) {
            echo json_encode([
                'success' => true, 
                'new_id' => $conn->insert_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }
}
?>