<?php
$conn = new mysqli("localhost", "root", "", "mira_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// 1. ตรวจสอบ ID สินค้า
if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM products WHERE product_id = $id";
$result = $conn->query($sql);
$product = $result->fetch_assoc();

if (!$product) {
    die("<script>alert('ไม่พบข้อมูลสินค้า'); window.location='manage_products.php';</script>");
}

// 2. ส่วนการอัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $sex = $_POST['sex'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    
    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . $_FILES['image']['name']; // ตั้งชื่อใหม่กันชื่อซ้ำ
        $target = "uploads/" . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $img_query = ", image='$image'";
    } else {
        $img_query = ""; 
    }

    $update_sql = "UPDATE products SET 
                    product_name='$name', 
                    price='$price', 
                    stock='$stock', 
                    sex='$sex', 
                    description='$desc' 
                    $img_query 
                    WHERE product_id=$id";
    
    if ($conn->query($update_sql)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
              <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'บันทึกสำเร็จ!',
                        text: 'ข้อมูลสินค้าถูกอัปเดตเรียบร้อยแล้ว',
                        icon: 'success',
                        confirmButtonColor: '#F06292'
                    }).then(() => {
                        window.location.href = 'manage_products.php';
                    });
                });
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Mira</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: #FDF2F4; /* ชมพูอ่อนมากแบบในรูป */
        }
        .mira-card {
            background: white;
            border-radius: 2.5rem;
            box-shadow: 0 10px 25px -5px rgba(240, 98, 146, 0.1);
        }
        .input-mira {
            background-color: #F9F9F9;
            border: 1px solid #F1F1F1;
            transition: all 0.3s ease;
        }
        .input-mira:focus {
            background-color: #FFF;
            border-color: #F06292;
            box-shadow: 0 0 0 4px rgba(240, 98, 146, 0.05);
        }
    </style>
</head>
<body class="p-6 md:p-12">


<div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <a href="../index_ad.php" class="inline-flex items-center text-sm text-gray-400 hover:text-[#F06292] transition-colors mb-4 group">
                <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                กลับสู่หน้า Dashboard
            </a>
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-semibold text-[#880E4F]">แก้ไขสินค้า</h1>
                <p class="text-gray-400 text-sm mt-1">ปรับแต่งข้อมูลสินค้าและสต็อกของคุณ</p>
            </div>
            
        </div>

        <div class="mira-card p-8 md:p-12">
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-4">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest">รูปภาพสินค้า</label>
                        <div class="relative group">
                            <div class="w-full h-64 rounded-[2rem] overflow-hidden bg-gray-50 border-2 border-dashed border-gray-100 flex items-center justify-center">
                                <img src="uploads/<?php echo $product['image']; ?>" id="preview" class="w-full h-full object-cover">
                            </div>
                            <div class="mt-4">
                                <input type="file" name="image" id="imgInput" class="hidden">
                                <label for="imgInput" class="cursor-pointer block text-center py-3 rounded-2xl bg-pink-50 text-[#F06292] text-sm font-semibold hover:bg-[#F06292] hover:text-white transition-all">
                                    เปลี่ยนรูปภาพ
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">ชื่อสินค้า</label>
                            <input type="text" name="product_name" value="<?php echo $product['product_name']; ?>" required 
                                   class="w-full input-mira rounded-2xl p-4 outline-none text-gray-700">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">ราคา (฿)</label>
                                <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required 
                                       class="w-full input-mira rounded-2xl p-4 outline-none text-gray-700">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">จำนวน</label>
                                <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required 
                                       class="w-full input-mira rounded-2xl p-4 outline-none text-gray-700">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">สำหรับเพศ</label>
                            <div class="flex gap-2">
                                <?php foreach(['male', 'female', 'unisex'] as $g): ?>
                                <label class="flex-1">
                                    <input type="radio" name="sex" value="<?php echo $g; ?>" <?php echo ($product['sex'] == $g) ? 'checked' : ''; ?> class="hidden peer">
                                    <div class="text-center py-2 rounded-xl border border-gray-100 text-gray-400 text-xs uppercase cursor-pointer peer-checked:bg-[#F06292] peer-checked:text-white peer-checked:border-[#F06292] transition-all">
                                        <?php echo $g; ?>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-50">

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">รายละเอียดสินค้า</label>
                    <textarea name="description" rows="4" 
                              class="w-full input-mira rounded-2xl p-4 outline-none text-gray-700"><?php echo $product['description']; ?></textarea>
                </div>

                <div class="flex flex-col md:flex-row gap-4 pt-4">
                    <button type="submit" class="flex-1 bg-[#880E4F] text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-[#AD1457] transition-all transform active:scale-[0.98]">
                        บันทึกการเปลี่ยนแปลง
                    </button>
                    <a href="manage_order.php" class="flex-1 text-center py-4 rounded-2xl bg-gray-50 text-gray-400 font-bold hover:bg-gray-100 transition-all">
                        ยกเลิก
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ระบบ Preview รูปภาพทันทีที่เลือกไฟล์
        imgInput.onchange = evt => {
            const [file] = imgInput.files
            if (file) {
                preview.src = URL.createObjectURL(file)
            }
        }
    </script>

</body>
</html>