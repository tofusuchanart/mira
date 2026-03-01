<?php
$conn = new mysqli("localhost", "root", "", "mira_db");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $sex = $_POST['sex'];
    $desc = $_POST['description'];
    
    // เริ่ม Transaction เพื่อความปลอดภัยของข้อมูล
    $conn->begin_transaction();

    try {
        // 1. จัดการรูปภาพหลัก
        $main_image = "";
        if (!empty($_FILES['image']['name'])) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $main_image = "main_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "../../photo/" . $main_image);
        }

        // 2. Insert ลงตาราง products
        $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock, sex, description, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdisss", $name, $price, $stock, $sex, $desc, $main_image);
        $stmt->execute();
        
        $product_id = $conn->insert_id; // ดึง ID ล่าสุดที่เพิ่ง insert ไป

        // 3. จัดการรูปภาพย่อย (Gallery)
        if (!empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['gallery']['error'][$key] == 0) {
                    $g_ext = pathinfo($_FILES['gallery']['name'][$key], PATHINFO_EXTENSION);
                    $g_name = "gallery_" . uniqid() . "_" . $key . "." . $g_ext;
                    
                    if (move_uploaded_file($tmp_name, "../../photo/" . $g_name)) {
                        $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                        $stmt_img->bind_param("is", $product_id, $g_name);
                        $stmt_img->execute();
                    }
                }
            }
        }

        $conn->commit(); // ยืนยันการบันทึกทั้งหมด
        header("Location: manage_products.php");
    } catch (Exception $e) {
        $conn->rollback(); // หากเกิดข้อผิดพลาด ให้ยกเลิกที่ทำมาทั้งหมด
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Mira</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-[#FFF5F7] p-10 font-['Sarabun']">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-[2.5rem] shadow-sm border border-pink-50">
        <h2 class="text-2xl font-bold text-[#880E4F] mb-6">เพิ่มสินค้าใหม่</h2>
        <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">ชื่อสินค้า</label>
                <input type="text" name="product_name" required class="w-full bg-pink-50/30 border-none rounded-xl p-3 outline-none focus:ring-1 focus:ring-pink-200">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">ราคา (บาท)</label>
                    <input type="number" name="price" required class="w-full bg-pink-50/30 border-none rounded-xl p-3 outline-none focus:ring-1 focus:ring-pink-200">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">จำนวนสต็อก</label>
                    <input type="number" name="stock" required class="w-full bg-pink-50/30 border-none rounded-xl p-3 outline-none focus:ring-1 focus:ring-pink-200">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">ประเภท (เพศ)</label>
                <select name="sex" class="w-full bg-pink-50/30 border-none rounded-xl p-3 outline-none">
                    <option value="unisex">Unisex</option>
                    <option value="female">Female</option>
                    <option value="male">Male</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">รายละเอียดสินค้า</label>
                <textarea name="description" rows="3" class="w-full bg-pink-50/30 border-none rounded-xl p-3 outline-none focus:ring-1 focus:ring-pink-200"></textarea>
            </div>
          <div>
    <label class="block text-xs text-gray-400 mb-1">รูปภาพหลัก (Cover)</label>
    <input type="file" name="image" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-pink-50 file:text-pink-700">
</div>

<div class="p-4 border-2 border-dashed border-pink-100 rounded-2xl bg-pink-50/10">
    <label class="block text-xs text-[#F06292] font-semibold mb-2">รูปภาพประกอบอื่นๆ (เลือกได้หลายรูป)</label>
    <input type="file" name="gallery[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-white file:text-gray-600 shadow-sm">
    <p class="text-[10px] text-gray-400 mt-2">* กด Ctrl ค้างไว้เพื่อเลือกหลายรูป</p>
</div>
            
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-[#F06292] text-white py-3 rounded-full font-bold shadow-md hover:bg-[#D81B60] transition">บันทึกข้อมูล</button>
                <a href="manage_products.php" class="flex-1 text-center bg-gray-100 text-gray-400 py-3 rounded-full font-bold hover:bg-gray-200 transition">ยกเลิก</a>
            </div>
        </form>
    </div>

</body>
</html>