<?php

session_start();

if (!isset($_SESSION["user_email"])) {

    header("Location: index.php");
    exit;

}

include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $category = trim($_POST["category"]);
    $price_input = trim($_POST["price"]);
    $is_available = isset($_POST["is_available"]) ? 1 : 0;

    if ($name == "" || $category == "") {

        $message = "Please enter a product name and category.";

    } elseif (!is_numeric($price_input) || $price_input < 0) {

        $message = "Please enter a valid price.";

    } else {

        $price = (float) $price_input;

        $sql = "INSERT INTO products (name, category, price, is_available) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdi", $name, $category, $price, $is_available);

        if ($stmt->execute()) {

            header("Location: products.php");
            exit;

        } else {

            $message = "Product could not be added.";

        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Product | Cheesy Bees</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="topbar">
    <a class="brand" href="dashboard.php">Cheesy <span>Bees</span></a>

    <nav>
        <a href="products.php">Products</a>
        <a href="logout.php">Log out</a>
    </nav>
</header>

<main class="page-shell">

<div class="page-heading">
    <div>
        <p class="eyebrow">Menu studio</p>
        <h1>Add a product.</h1>
    </div>
</div>

<section class="panel editor-card">

<form method="POST" class="form-card">

    <div class="field">
        <label>Name</label>
        <input type="text" name="name" required>
    </div>

    <div class="field">
        <label>Category</label>
        <input type="text" name="category" required>
    </div>

    <div class="field">
        <label>Price</label>
        <input type="number" name="price" step="0.01" min="0" required>
    </div>

    <div class="check-field">
        <input type="checkbox" name="is_available" value="1" checked>
        <label>
        Available for sale
        </label>
    </div>

    <button type="submit">Save Product</button>

</form>

<p class="form-message" role="alert">
    <?php echo htmlspecialchars($message); ?>
</p>

</section>

<a class="back-link" href="products.php">← Back to products</a>

</main>

</body>

</html>
