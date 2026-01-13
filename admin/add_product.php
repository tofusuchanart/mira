<?php
// 1. ปรับ path ถอยหลัง 1 ชั้นเพื่อเรียกใช้ config.php ที่อยู่ด้านนอก.png
require_once "../config.php"; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    
    // 2. จัดการเรื่องรูปภาพ
    $filename = $_FILES['product_image']['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION); // ดึงนามสกุลไฟล์
    $new_filename = uniqid('p_') . "." . $ext; // ตั้งชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ
    
    // ปรับ target ให้ถอยออกไปเก็บที่โฟลเดอร์ photo ด้านนอก.png
    $target = "../photo/" . $new_filename; 

    try {
        // 3. เตรียมคำสั่ง SQL (ตรวจสอบชื่อตารางและคอลัมน์ในฐานข้อมูล mr_db ของคุณ)
        $sql = "INSERT INTO products (product_name, price, description, image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$product_name, $price, $description, $new_filename])) {
            // 4. ย้ายไฟล์รูปภาพไปเก็บในโฟลเดอร์ photo ด้านนอก
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target)) {
                echo "<script>alert('เพิ่มสินค้าสำเร็จ!'); window.location='admin.php';</script>";
            } else {
                echo "<script>alert('บันทึกข้อมูลสำเร็จ แต่รูปภาพไม่ถูกอัปโหลด'); window.location='admin.php';</script>";
            }
        }
    } catch(PDOException $e) {
        die("เกิดข้อผิดพลาด: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Mira Admin</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; }
        .card { border: none; border-radius: 15px; }
        .card-header { border-radius: 15px 15px 0 0 !important; }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header bg-success text-white py-3">
                        <h4 class="mb-0 text-center">เพิ่มสินค้าใหม่</h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="add_product.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ชื่อสินค้า</label>
                                <input type="text" name="product_name" class="form-control" placeholder="ระบุชื่อสินค้า" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">ราคา (บาท)</label>
                                <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">รายละเอียดสินค้า</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="ข้อมูลเพิ่มเติมเกี่ยวกับสินค้า..."></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">รูปภาพสินค้า</label>
                                <input type="file" name="product_image" class="form-control" accept="image/*" required>
                                <div class="form-text text-muted">รองรับไฟล์ .jpg, .jpeg, .png</div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">บันทึกข้อมูลสินค้า</button>
                                <a href="admin.php" class="btn btn-light">ยกเลิกและกลับหน้าจัดการ</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>