<?php

session_start();

if (!isset($_SESSION["user_email"])) {

    header("Location: index.php");
    exit;

}

include "db.php";

$message = "";
$sql = "SELECT id, name, category, price, is_available FROM products ORDER BY name";
$stmt = $conn->prepare($sql);
$result = null;

if ($stmt === false) {

    if ($conn->errno == 1146) {

        $message = "The products table has not been created yet. Run the products.sql file in the simple_site database, then refresh this page.";

    } else {

        $message = "Products could not be loaded. Please check your database connection.";

    }

} elseif ($stmt->execute()) {

    $result = $stmt->get_result();

} else {

    $message = "Products could not be loaded. Please try again.";

}

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products | Cheesy Bees</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="topbar">
    <a class="brand" href="dashboard.php">Cheesy <span>Bees</span></a>

    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Log out</a>
    </nav>
</header>

<main class="page-shell">

<div class="page-heading">
    <div>
        <p class="eyebrow">Menu studio</p>
        <h1>Products</h1>
    </div>

    <a class="button-link warm" href="add_product.php">Add product</a>
</div>

<section class="panel table-wrap">
<?php if ($message != "") { ?>
    <p class="notice">
        <?php echo htmlspecialchars($message); ?>
    </p>
<?php } else { ?>
<table>
    <tr>
        <th>Name</th>
        <th>Category</th>
        <th>Price</th>
        <th>Available</th>
        <th>Actions</th>
    </tr>

    <?php while ($product = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($product["name"]); ?></td>
            <td><?php echo htmlspecialchars($product["category"]); ?></td>
            <td class="price"><?php echo number_format($product["price"], 2); ?></td>
            <td>
                <?php
                if ($product["is_available"] == 1) {
                    echo '<span class="status available">Available</span>';
                } else {
                    echo '<span class="status unavailable">Paused</span>';
                }
                ?>
            </td>
            <td class="actions">
                <a class="text-action" href="edit_product.php?id=<?php echo $product["id"]; ?>">Edit</a>

                <form class="delete-form" method="POST" action="delete_product.php">
                    <input type="hidden" name="id" value="<?php echo $product["id"]; ?>">
                    <button class="delete-button" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php } ?>
</table>
<?php } ?>
</section>

<a class="back-link" href="dashboard.php">← Back to dashboard</a>

</main>

</body>

</html>
