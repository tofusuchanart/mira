<?php
require_once "../../config.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

// หากไม่พบสินค้าให้กลับไปหน้าหลัก
if (!$product) {
    header("Location: admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $old_image = $_POST['old_image'];
    
    $new_image = $_FILES['product_image']['name'];

    if (!empty($new_image)) {
        $ext = pathinfo($new_image, PATHINFO_EXTENSION);
        $file_name = uniqid('p_') . "." . $ext;
        move_uploaded_file($_FILES['product_image']['tmp_name'], "../photo/" . $file_name);
        
        if (file_exists("../photo/" . $old_image) && $old_image != "") {
            unlink("../photo/" . $old_image);
        }
    } else {
        $file_name = $old_image;
    }

    try {
        $sql = "UPDATE products SET product_name=?, price=?, description=?, image=? WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$name, $price, $desc, $file_name, $id])) {
            echo "<script>alert('แก้ไขข้อมูลเรียบร้อยแล้ว!'); window.location='admin.php';</script>";
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - MIRA Admin</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../../admin/photo_ad/ro.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            padding: 40px 0;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }

        .form-label {
            color: #f8a5c2;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            padding: 12px;
            transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #f8a5c2;
            color: white;
            box-shadow: none;
        }

        .current-img-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
            border: 1px dashed rgba(255, 255, 255, 0.3);
        }

        .btn-save {
            background: linear-gradient(45deg, #f8a5c2, #f78fb3);
            border: none;
            color: white;
            border-radius: 12px;
            padding: 12px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(248, 165, 194, 0.4);
            color: white;
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 12px;
            padding: 12px;
            transition: 0.3s;
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        ::placeholder { color: rgba(255,255,255,0.4) !important; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="glass-card">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">แก้ไขข้อมูลสินค้า</h2>
                    <p class="text-white-50 small">ID สินค้า: #<?php echo $product['product_id']; ?></p>
                </div>

                <form action="edit.php?id=<?php echo $product['product_id']; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="old_image" value="<?php echo $product['image']; ?>">

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-tag me-2"></i>ชื่อสินค้า</label>
                        <input type="text" name="product_name" class="form-control" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-currency-dollar me-2"></i>ราคา (บาท)</label>
                        <input type="number" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-card-text me-2"></i>รายละเอียดสินค้า</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-image me-2"></i>รูปภาพสินค้า</label>
                        <div class="current-img-box">
                            <p class="small text-white-50 mb-2">รูปภาพปัจจุบัน</p>
                            <img src="../photo/<?php echo $product['image']; ?>" width="120" class="rounded shadow-sm mb-3">
                            <input type="file" name="product_image" class="form-control" accept="image/*">
                            <small class="text-white-50 d-block mt-2" style="font-size: 0.75rem;">* เลือกไฟล์ใหม่เฉพาะเมื่อต้องการเปลี่ยนรูปภาพ</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <a href="manage_order.php" class="btn btn-cancel w-100">
                                <i class="bi bi-x-circle me-2"></i>ยกเลิก
                            </a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-save w-100">
                                <a href="manage_order.php" class="btn btn-cancel w-100">
                                <i class="bi bi-check-circle me-2"></i>บันทึกข้อมูล
                            </a>
                            </button>
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