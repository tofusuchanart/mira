<?php
$conn = new mysqli("localhost", "root", "", "mira_db");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $sex = $_POST['sex'];
    $desc = $_POST['description'];
    
    // จัดการอัปโหลดรูปภาพ
    $image = $_FILES['image']['name'];
    $target = "../../photo/" . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $sql = "INSERT INTO products (product_name, price, stock, sex, description, image) 
            VALUES ('$name', '$price', '$stock', '$sex', '$desc', '$image')";
    
    if ($conn->query($sql)) {
        header("Location: manage_products.php");
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
                <label class="block text-xs text-gray-400 mb-1">รูปภาพสินค้า</label>
                <input type="file" name="image" class="text-sm">
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-[#F06292] text-white py-3 rounded-full font-bold shadow-md hover:bg-[#D81B60] transition">บันทึกข้อมูล</button>
                <a href="manage_products.php" class="flex-1 text-center bg-gray-100 text-gray-400 py-3 rounded-full font-bold hover:bg-gray-200 transition">ยกเลิก</a>
            </div>
        </form>
    </div>

</body>
</html>