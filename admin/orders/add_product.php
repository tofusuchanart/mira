<?php
// 1. ปรับ path ถอยหลัง 1 ชั้นเพื่อเรียกใช้ config.php
require_once "../../config.php"; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $sex = $_POST['sex']; // รับค่าเพศที่เลือก
    
    // 2. จัดการเรื่องรูปภาพ
    $filename = $_FILES['product_image']['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $new_filename = uniqid('p_') . "." . $ext;
    $target = "../../photo/" . $new_filename; 

    try {
        // เพิ่ม sex เข้าไปใน SQL Query
        $sql = "INSERT INTO products (product_name, price, description, image, sex) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$product_name, $price, $description, $new_filename, $sex])) {
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target)) {
                echo "<script>alert('เพิ่มสินค้าสำเร็จ!'); window.location='manage_order.php';</script>";
            } else {
                echo "<script>alert('บันทึกข้อมูลสำเร็จ แต่รูปภาพไม่ถูกอัปโหลด'); window.location='manage_order.php';</script>";
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
    <title>Add New Product - MIRA Admin</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../admin/photo_ad/ro.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            padding: 50px 0;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        .form-label {
            color: #f8a5c2;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white !important;
            padding: 12px;
            transition: 0.3s;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #f8a5c2;
            box-shadow: none;
        }

        /* ตกแต่ง Radio Buttons ให้เป็นปุ่ม */
        .sex-selection {
            display: flex;
            gap: 10px;
        }

        .sex-option {
            flex: 1;
        }

        .sex-option input[type="radio"] {
            display: none;
        }

        .sex-option label {
            display: block;
            text-align: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.9rem;
        }

        .sex-option input[type="radio"]:checked + label {
            background: linear-gradient(45deg, #f8a5c2, #f78fb3);
            border-color: transparent;
            color: white;
            box-shadow: 0 5px 15px rgba(248, 165, 194, 0.3);
        }

        .upload-box {
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            background: rgba(255, 255, 255, 0.02);
        }

        .btn-mira-primary {
            background: linear-gradient(45deg, #f8a5c2, #f78fb3);
            border: none; color: white; border-radius: 12px;
            padding: 14px; font-weight: bold; transition: 0.3s;
        }

        .btn-mira-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(248, 165, 194, 0.3);
            color: white;
        }

        .btn-mira-outline {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white; border-radius: 12px;
            padding: 14px; transition: 0.3s;
        }

        .icon-header { font-size: 3rem; color: #f8a5c2; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="glass-card">
                <div class="text-center mb-4">
                    <i class="bi bi-plus-circle-dotted icon-header"></i>
                    <h2 class="fw-bold">เพิ่มสินค้าใหม่</h2>
                    <p class="text-white-50">กรอกรายละเอียดเพื่อนำสินค้าขึ้นหน้าเว็บไซต์</p>
                </div>

                <form action="add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-tag me-2"></i>ชื่อสินค้า</label>
                        <input type="text" name="product_name" class="form-control" placeholder="เช่น น้ำหอมกลิ่น Summer Fresh" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-currency-dollar me-2"></i>ราคา (บาท)</label>
                            <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-gender-ambiguous me-2"></i>ประเภทเพศ</label>
                            <div class="sex-selection">
                                <div class="sex-option">
                                    <input type="radio" name="sex" id="male" value="male">
                                    <label for="male">ชาย</label>
                                </div>
                                <div class="sex-option">
                                    <input type="radio" name="sex" id="female" value="female">
                                    <label for="female">หญิง</label>
                                </div>
                                <div class="sex-option">
                                    <input type="radio" name="sex" id="unisex" value="unisex" checked>
                                    <label for="unisex">ทั้งหมด</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-card-text me-2"></i>รายละเอียดสินค้า</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="ระบุสรรพคุณ..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-image me-2"></i>รูปภาพสินค้า</label>
                        <div class="upload-box">
                            <input type="file" name="product_image" id="imgInput" class="form-control mb-2" accept="image/*" required>
                            <div class="form-text text-white-50">รองรับไฟล์ .jpg, .jpeg, .png</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <a href="manage_order.php" class="btn btn-mira-outline w-100">ยกเลิก</a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-mira-primary w-100">บันทึกสินค้า</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>