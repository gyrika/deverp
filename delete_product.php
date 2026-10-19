<?php

session_start();

if (!isset($_SESSION["user_email"])) {

    header("Location: index.php");
    exit;

}

include "db.php";

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST["id"]) || !ctype_digit($_POST["id"])) {

    header("Location: products.php");
    exit;

}

$id = (int) $_POST["id"];

$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: products.php");
exit;

?>
