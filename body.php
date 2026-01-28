<?php
// รับค่า link จาก URL ถ้าไม่มีให้กำหนดเป็น home
$link = isset($_GET['link']) ? $_GET['link'] : 'home';

switch ($link) {
    case 'home':
        // *** ห้าม include "index.php" ในนี้ เพราะ index.php คือไฟล์หลักที่เรียก body.php อยู่แล้ว ***
        include_once "banner.php";
        include_once "mockup_popular.php";
        break;

    case 'women':
        include_once "perfume_forwomen/index_w.php";
        break;

    case 'men':
        include_once "perfume_formen/index_m.php";
        break;

    default:
        // กรณีอื่นๆ ที่ไม่มีในเงื่อนไข ให้แสดงหน้าแรกเป็นพื้นฐาน
        include_once "banner.php";
        include_once "mockup_popular.php";
        break;
}
?>