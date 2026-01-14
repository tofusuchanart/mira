<?php
if(isset($_GET['link'])){
    $link = $_GET['link'];
}else{
    $link="home";
}

if($link=='home'){
include_once "index_users.php";
}
if($link=='home'){
include_once "banners.php";
}if($link=='home'){
include_once "mockup_popular.php";
}

elseif($link=='women'){
    include_once "../perfume_forwomen/index_w.php";
}
elseif($link=='men'){
    include_once "../perfume_formen/index_m.php";
}


?>