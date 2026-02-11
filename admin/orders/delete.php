<?php
require_once "../../config.php";

$conn = new mysqli("localhost", "root", "", "mira_db");
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM products WHERE product_id = $id";
    if ($conn->query($sql)) {
        header("Location: manage_order.php?deleted=success");
    }
}
?>