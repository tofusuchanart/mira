<?php
require_once "../config.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $old_image = $_POST['old_image'];
    
    $new_image = $_FILES['product_image']['name'];

    if (!empty($new_image)) {
        // ถ้ามีการอัปโหลดรูปใหม่
        $ext = pathinfo($new_image, PATHINFO_EXTENSION);
        $file_name = uniqid('p_') . "." . $ext;
        move_uploaded_file($_FILES['product_image']['tmp_name'], "../photo/" . $file_name);
        
        // ลบรูปเก่าทิ้ง
        if (file_exists("../photo/" . $old_image)) {
            unlink("../photo/" . $old_image);
        }
    } else {
        // ถ้าไม่เปลี่ยนรูป ให้ใช้ชื่อเดิม
        $file_name = $old_image;
    }

    try {
        $sql = "UPDATE products SET product_name=?, price=?, description=?, image=? WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$name, $price, $desc, $file_name, $id])) {
            echo "<script>alert('แก้ไขสำเร็จ!'); window.location='admin.php';</script>";
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
    <title>Edit Product - MIRA</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow col-md-6 mx-auto">
            <div class="card-header bg-primary text-white"><h4>แก้ไขสินค้า</h4></div>
            <div class="card-body">
                <form action="edit.php?id=<?php echo $product['product_id']; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="old_image" value="<?php echo $product['image']; ?>">

                    <div class="mb-3">
                        <label class="form-label">ชื่อสินค้า</label>
                        <input type="text" name="product_name" class="form-control" value="<?php echo $product['product_name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ราคา</label>
                        <input type="number" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo $product['description']; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปภาพปัจจุบัน</label><br>
                        <img src="../photo/<?php echo $product['image']; ?>" width="100" class="mb-2 rounded">
                        <input type="file" name="product_image" class="form-control" accept="image/*">
                        <small class="text-muted text-danger">* เว้นว่างไว้หากไม่ต้องการเปลี่ยนรูป</small>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                        <a href="index_ad.php" class="btn btn-secondary">ยกเลิก</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>