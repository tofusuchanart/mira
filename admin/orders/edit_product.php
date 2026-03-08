<?php
$conn = new mysqli("localhost", "root", "", "mira_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit;
}

$id = $_GET['id'];

// --- 1. จัดการการลบรูปภาพย่อย ---
if (isset($_GET['delete_img'])) {
    $img_id = $_GET['delete_img'];
    $res = $conn->query("SELECT image_path FROM product_images WHERE image_id = $img_id");
    if ($img_data = $res->fetch_assoc()) {
        unlink("../../photo/" . $img_data['image_path']);
    }
    $conn->query("DELETE FROM product_images WHERE image_id = $img_id");
    header("Location: edit_product.php?id=$id");
    exit;
}

// 2. ดึงข้อมูลสินค้าหลัก
$product = $conn->query("SELECT * FROM products WHERE product_id = $id")->fetch_assoc();

// 3. ดึงรูปภาพย่อย
$gallery = $conn->query("SELECT * FROM product_images WHERE product_id = $id");

// 4. ดึงรายชื่อหมวดหมู่ทั้งหมด (เพื่อเอามาทำ Dropdown)
$categories = $conn->query("SELECT * FROM categories");

// 5. ส่วนการอัปเดตข้อมูล
// 5. ส่วนการอัปเดตข้อมูล
// 5. ส่วนการอัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $sex = $_POST['sex']; // รับค่าจาก Radio Button (male/female/unisex)
    $cat_id = !empty($_POST['category_id']) ? $_POST['category_id'] : "NULL";
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    $img_query = "";
    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../../photo/" . $image);
        $img_query = ", image='$image'";
    }

    // SQL Update (ตรวจสอบว่ามี sex='$sex' และคอมม่าถูกต้อง)
    $update_sql = "UPDATE products SET 
                    product_name='$name', 
                    price='$price', 
                    stock='$stock', 
                    sex='$sex', 
                    category_id=$cat_id, 
                    description='$desc' 
                    $img_query 
                    WHERE product_id=$id";
    
    // ... ส่วนที่เหลือคงเดิม ...

   
    if ($conn->query($update_sql)) {
        // จัดการอัปโหลดรูปภาพย่อยเพิ่ม
        if (!empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['gallery']['error'][$key] == 0) {
                    $g_name = "gallery_" . time() . "_" . uniqid() . ".jpg";
                    if (move_uploaded_file($tmp_name, "../../photo/" . $g_name)) {
                        $conn->query("INSERT INTO product_images (product_id, image_path) VALUES ($id, '$g_name')");
                    }
                }
            }
        }
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
              <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({ title: 'บันทึกสำเร็จ!', icon: 'success', confirmButtonColor: '#F06292' })
                    .then(() => { window.location.href = 'manage_products.php'; });
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
    <link rel="icon" href="../photo_ad/golo.png">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #FDF2F4;
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
            border-color: #F06292;
            background-color: #FFF;
            outline: none;
            box-shadow: 0 0 0 4px rgba(240, 98, 146, 0.05);
        }
    </style>
</head>

<body class="p-6 md:p-12">

    <div class="max-w-6xl mx-auto">
        <div class="mira-card p-8 md:p-12">
            <h1 class="text-3xl font-semibold text-[#880E4F] mb-8">แก้ไขสินค้า</h1>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">

                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">รูปภาพหลัก</label>
                            <div class="relative w-full h-80 rounded-[2.5rem] overflow-hidden bg-gray-50 border-2 border-dashed border-gray-100 flex items-center justify-center">
                                <img src="../../photo/<?php echo $product['image']; ?>" id="preview" class="w-full h-full object-cover">
                            </div>
                            <input type="file" name="image" id="imgInput" class="hidden">
                            <label for="imgInput" class="cursor-pointer block text-center mt-4 py-3 rounded-2xl bg-pink-50 text-[#F06292] font-semibold hover:bg-[#F06292] hover:text-white transition-all">
                                เปลี่ยนรูปหลัก
                            </label>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">รูปภาพย่อยใน Gallery</label>
                            <div class="grid grid-cols-4 gap-3 mb-4">
                                <?php while ($img = $gallery->fetch_assoc()): ?>
                                    <div class="relative group aspect-square rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
                                        <img src="../../photo/<?php echo $img['image_path']; ?>" class="w-full h-full object-cover">
                                        <a href="?id=<?php echo $id; ?>&delete_img=<?php echo $img['image_id']; ?>"
                                            onclick="return confirm('ลบรูปนี้ใช่หรือไม่?')"
                                            class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                                            <i class="fa-solid fa-trash-can text-white"></i>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="p-4 border-2 border-dashed border-gray-100 rounded-3xl bg-gray-50/50">
                                <input type="file" name="gallery[]" multiple class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-white file:text-[#F06292] file:font-semibold file:shadow-sm">
                            </div>
                        </div>
                    </div>

                <div class="space-y-6">
    <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">ชื่อสินค้า</label>
        <input type="text" name="product_name" value="<?php echo $product['product_name']; ?>" required class="w-full input-mira rounded-2xl p-4 text-gray-700">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">ราคา (฿)</label>
            <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required class="w-full input-mira rounded-2xl p-4 text-gray-700">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">จำนวนในสต็อก</label>
            <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required class="w-full input-mira rounded-2xl p-4 text-gray-700">
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">เพศ (Gender)</label>
        <div class="grid grid-cols-3 gap-3">
            <?php
            // กำหนดตัวเลือกให้ตรงกับ ENUM('male', 'female', 'unisex')
            $genders = [
                'male'   => ['label' => 'ผู้ชาย', 'color' => 'blue', 'icon' => 'fa-mars'],
                'female' => ['label' => 'ผู้หญิง', 'color' => 'pink', 'icon' => 'fa-venus'],
                'unisex' => ['label' => 'Unisex', 'color' => 'purple', 'icon' => 'fa-venus-mars']
            ];

            foreach ($genders as $val => $info):
                $is_checked = ($product['sex'] == $val) ? 'checked' : '';
            ?>
                <label class="relative cursor-pointer">
                    <input type="radio" name="sex" value="<?php echo $val; ?>" class="peer hidden" <?php echo $is_checked; ?>>
                    <div class="flex items-center justify-center gap-2 p-3 rounded-xl border border-gray-100 bg-white text-gray-400 
                                peer-checked:bg-<?php echo $info['color']; ?>-500 peer-checked:text-white transition-all hover:bg-gray-50">
                        <i class="fa-solid <?php echo $info['icon']; ?> text-xs"></i>
                        <span class="text-xs font-bold"><?php echo $info['label']; ?></span>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">รายละเอียดสินค้า</label>
        <textarea name="description" rows="5" class="w-full input-mira rounded-3xl p-4 text-gray-700 outline-none"><?php echo $product['description']; ?></textarea>
    </div>
</div>

                <div class="flex flex-col md:flex-row gap-4 pt-8 border-t border-gray-50">
                    <button type="submit" class="flex-1 bg-[#880E4F] text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-[#AD1457] transition-all transform active:scale-95">
                        บันทึกการเปลี่ยนแปลงทั้งหมด
                    </button>
                    <a href="manage_products.php" class="flex-1 text-center py-4 rounded-2xl bg-gray-50 text-gray-400 font-bold hover:bg-gray-100 transition-all">
                        ยกเลิก
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ระบบ Preview รูปภาพหลัก
        const imgInput = document.getElementById('imgInput');
        const preview = document.getElementById('preview');
        imgInput.onchange = evt => {
            const [file] = imgInput.files
            if (file) {
                preview.src = URL.createObjectURL(file)
            }
        }
        
    </script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
async function addNewCategory() {
    const { value: catName } = await Swal.fire({
        title: 'เพิ่มหมวดหมู่ใหม่',
        input: 'text',
        inputLabel: 'ชื่อหมวดหมู่ที่ต้องการเพิ่ม',
        inputPlaceholder: 'เช่น น้ำหอมผู้ชาย, Body Mist...',
        showCancelButton: true,
        confirmButtonColor: '#F06292',
        cancelButtonText: 'ยกเลิก',
        confirmButtonText: 'บันทึก',
        inputValidator: (value) => {
            if (!value) {
                return 'กรุณากรอกชื่อหมวดหมู่!'
            }
        }
    });

    if (catName) {
        // ส่งข้อมูลไปยังไฟล์ PHP เพื่อบันทึก (สร้างไฟล์ใหม่ชื่อ add_category_ajax.php)
        fetch('add_category_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'category_name=' + encodeURIComponent(catName)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // เพิ่ม Option ใหม่เข้าไปใน Select ทันที
                const select = document.getElementById('category_id');
                const option = new Option(catName, data.new_id);
                select.add(option);
                select.value = data.new_id; // เลือกตัวที่เพิ่งเพิ่มให้เลย

                Swal.fire({
                    icon: 'success',
                    title: 'เพิ่มสำเร็จ!',
                    text: 'หมวดหมู่ ' + catName + ' พร้อมใช้งานแล้ว',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถเพิ่มได้', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
        });
    }
}
</script>
</body>

</html>