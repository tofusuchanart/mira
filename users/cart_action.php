<?php
session_start();

// ตรวจสอบว่ามีการส่ง action มาหรือไม่
$action = isset($_GET['action']) ? $_GET['action'] : '';

/**
 * 1. ACTION: ADD (เพิ่มสินค้าลงตะกร้า)
 */
if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id']; // ป้องกันข้อมูลแปลกปลอม
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // กลับไปยังหน้าที่กดมา (ใช้ HTTP_REFERER เพื่อให้ยืดหยุ่น)
    $goback = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index_users.php';
    header("Location: $goback");
    exit;
}

/**
 * 2. ACTION: UPDATE (ปรับเพิ่ม/ลด จำนวนสินค้า)
 */
if ($action == 'update' && isset($_GET['id']) && isset($_GET['qty'])) {
    $product_id = (int)$_GET['id'];
    $new_qty = (int)$_GET['qty'];

    if ($new_qty > 0) {
        $_SESSION['cart'][$product_id] = $new_qty;
    } else {
        unset($_SESSION['cart'][$product_id]);
    }

    // เปลี่ยนชื่อไฟล์ตรงนี้ให้ตรงกับหน้าตะกร้าของคุณจริงๆ
    header("Location: cart/mycart.php"); 
    exit;
}

/**
 * 3. ACTION: REMOVE (ลบสินค้าออกชิ้นเดียว)
 */
if ($action == 'remove' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }

    header("Location: cart/mycart.php");
    exit;
}

/**
 * 4. ACTION: CLEAR (ล้างตะกร้าทั้งหมด)
 */
if ($action == 'clear') {
    unset($_SESSION['cart']);
    header("Location: cart/mycart.php");
    exit;
}

// หากไม่มี action ที่ตรงเงื่อนไข ให้ดีดกลับหน้าหลัก
header("Location: ../index_users.php");
exit;
?>