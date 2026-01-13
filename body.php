<?php
if(isset($_GET['link'])){
    $link = $_GET['link'];
}else{
    $link="home";
}

if($link=='home'){
include_once "index.php";
}

if($link=='home'){
include_once "banner.php";

}
elseif($link=='women'){
    include_once "perfume_forwomen/index.php";
}



?>