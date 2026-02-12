<?php
require_once "../../config.php";
/** @var PDO $conn */
$stmt = $conn->query("SELECT COUNT(*) FROM users");
$user_count = ($stmt) ? $stmt->fetchColumn() : 0;
// 1. ดึงข้อมูลแบบ PDO
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$stmt = $conn->query($sql);

// 2. ดึงข้อมูลมาเก็บไว้ในตัวแปร $products (เป็น Array)
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. นับจำนวนจากตัวแปร Array โดยตรง
$total_products = count($products); 
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Mira</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #FFF5F7; }
        .product-card { border-radius: 2.5rem; }
        
        /* สไตล์ปุ่ม Dashboard ตามไฟล์แนบ */
        .mira-btn-dashboard {
            display: inline-flex;
            align-items: center;
            background-color: white;
            color: #333;
            border: 1px solid #333;
            padding: 6px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .mira-btn-dashboard:hover {
            background-color: #f8f8f8;
            border-color: #000;
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
            
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-semibold text-[#880E4F]">สินค้าคงคลัง</h1>
                    <p class="text-gray-500 text-sm mt-1">จัดการคลังสินค้า Mira ของคุณ</p>
                </div>
                <a href="add_product.php" class="bg-white border border-[#F06292] text-[#F06292] px-6 py-2 rounded-full hover:bg-[#F06292] hover:text-white transition-all shadow-sm">
                    + เพิ่มสินค้าใหม่
                </a>
            </div>
        </div>

        <div class="bg-white product-card shadow-sm overflow-hidden border border-pink-50">
            <div class="p-8 border-b border-gray-50 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-[#AD1457]">รายการสินค้าทั้งหมด (<?php echo count($products); ?>)</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-gray-400 text-xs uppercase border-b border-gray-50">
                            <th class="px-8 py-4">รูปภาพ</th>
                            <th class="px-8 py-4">ข้อมูลสินค้า</th>
                            <th class="px-8 py-4">เพศ</th>
                            <th class="px-8 py-4 text-center">คงเหลือ</th>
                            <th class="px-8 py-4 text-right">ราคา</th>
                            <th class="px-8 py-4 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach($products as $row): ?>
                        <tr class="hover:bg-pink-50/20 transition-colors group">
                            <td class="px-8 py-5">
                                <img src="../../photo/<?php echo htmlspecialchars($row['image']); ?>" class="w-16 h-16 rounded-2xl object-cover shadow-sm border border-pink-50" onerror="this.src='https://via.placeholder.com/100'">
                            </td>
                            <td class="px-8 py-5">
                                <div class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($row['product_name']); ?></div>
                                <div class="text-[11px] text-gray-400 truncate max-w-[200px]"><?php echo htmlspecialchars($row['description']); ?></div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 bg-pink-50 text-pink-500 rounded-full text-[10px] font-bold uppercase">
                                    <?php echo htmlspecialchars($row['sex']); ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-center text-sm <?php echo $row['stock'] <= 0 ? 'text-red-500 font-bold' : 'text-gray-600'; ?>">
                                <?php echo number_format($row['stock']); ?>
                            </td>
                            <td class="px-8 py-5 text-right font-bold text-gray-700">
                                ฿<?php echo number_format($row['price'], 2); ?>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="p-2 text-blue-400 hover:bg-blue-50 rounded-xl transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <button onclick="confirmDelete(<?php echo $row['product_id']; ?>)" class="p-2 text-red-400 hover:bg-red-50 rounded-xl transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'คุณแน่ใจไหม?',
            text: "ข้อมูลสินค้านี้จะถูกลบออกจากคลังถาวร!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F06292',
            cancelButtonColor: '#9CA3AF',
            confirmButtonText: 'ยืนยันการลบ',
            cancelButtonText: 'ยกเลิก',
            customClass: {
                popup: 'rounded-[2rem]'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'delete.php?id=' + id;
            }
        })
    }
    </script>
</body>
</html>