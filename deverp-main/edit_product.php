<?php

session_start();

if (!isset($_SESSION["user_email"])) {

    header("Location: index.php");
    exit;

}

include "db.php";

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {

    header("Location: products.php");
    exit;

}

$id = (int) $_GET["id"];
$message = "";

$select_sql = "SELECT id, name, category, price, is_available FROM products WHERE id = ?";
$select_stmt = $conn->prepare($select_sql);
$select_stmt->bind_param("i", $id);
$select_stmt->execute();
$result = $select_stmt->get_result();

if ($result->num_rows !== 1) {

    header("Location: products.php");
    exit;

}

$product = $result->fetch_assoc();

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

        $update_sql = "UPDATE products SET name = ?, category = ?, price = ?, is_available = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssdii", $name, $category, $price, $is_available, $id);

        if ($update_stmt->execute()) {

            header("Location: products.php");
            exit;

        } else {

            $message = "Product could not be updated.";

        }
    }

    $product["name"] = $name;
    $product["category"] = $category;
    $product["price"] = $price_input;
    $product["is_available"] = $is_available;
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Product | Cheesy Bees</title>
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
        <h1>Refine the recipe.</h1>
    </div>
</div>

<section class="panel editor-card">

<form method="POST" class="form-card">

    <div class="field">
        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product["name"]); ?>" required>
    </div>

    <div class="field">
        <label>Category</label>
        <input type="text" name="category" value="<?php echo htmlspecialchars($product["category"]); ?>" required>
    </div>

    <div class="field">
        <label>Price</label>
        <input type="number" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($product["price"]); ?>" required>
    </div>

    <div class="check-field">
        <input
            type="checkbox"
            name="is_available"
            value="1"
            <?php if ($product["is_available"] == 1) { echo "checked"; } ?>
        >
        <label>
        Available for sale
        </label>
    </div>

    <button type="submit">Update Product</button>

</form>

<p class="form-message" role="alert">
    <?php echo htmlspecialchars($message); ?>
</p>

</section>

<a class="back-link" href="products.php">← Back to products</a>

</main>

</body>

</html>
