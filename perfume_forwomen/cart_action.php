<?php
session_start();

// ตรวจสอบว่ามีการส่ง action มาหรือไม่
$action = isset($_GET['action']) ? $_GET['action'] : '';

/**
 * 1. ACTION: ADD (เพิ่มสินค้าลงตะกร้า)
 * เรียกใช้จากหน้า product_detail.php ผ่านฟอร์ม POST
 */
if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // ถ้ายังไม่มีตะกร้าใน Session ให้สร้างอาเรย์ว่าง
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // ถ้ามีสินค้านี้อยู่แล้วให้บวกเพิ่ม ถ้ายังไม่มีให้กำหนดค่าใหม่
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // แจ้งเตือนและกลับไปหน้าเดิม หรือไปหน้าตะกร้า
    echo "<script>
            alert('เพิ่มสินค้าลงในรถเข็นเรียบร้อยแล้ว');
            window.location.href = 'product_detail.php?id=$product_id'; 
          </script>";
    exit;
}

/**
 * 2. ACTION: UPDATE (ปรับเพิ่ม/ลด จำนวนสินค้า)
 * เรียกใช้จากหน้า view_cart.php ผ่านลิงก์ GET
 */
if ($action == 'update' && isset($_GET['id']) && isset($_GET['qty'])) {
    $product_id = $_GET['id'];
    $new_qty = (int)$_GET['qty'];

    // ถ้าจำนวนมากกว่า 0 ให้ปรับยอดตามที่ส่งมา
    if ($new_qty > 0) {
        $_SESSION['cart'][$product_id] = $new_qty;
    } else {
        // ถ้าลดจนเหลือ 0 หรือน้อยกว่า ให้ลบสินค้านั้นทิ้ง
        unset($_SESSION['cart'][$product_id]);
    }

    header("Location: view_cart.php");
    exit;
}

/**
 * 3. ACTION: REMOVE (ลบสินค้าออกชิ้นเดียว)
 * เรียกใช้จากหน้า view_cart.php ผ่านลิงก์ GET
 */
if ($action == 'remove' && isset($_GET['id'])) {
    $product_id = $_GET['id'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }

    header("Location: view_cart.php");
    exit;
}

/**
 * 4. ACTION: CLEAR (ล้างตะกร้าทั้งหมด)
 */
if ($action == 'clear') {
    unset($_SESSION['cart']);
    header("Location: view_cart.php");
    exit;
}

// หากไม่มี action ที่ตรงเงื่อนไข ให้ดีดกลับหน้าแรก
header("Location: index.php");
exit;
?>