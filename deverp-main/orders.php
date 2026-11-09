<?php

session_start();
if (!isset($_SESSION["user_email"])) {
    header("Location: index.php");
    exit;
}

include "db.php";

$message = "";
$orders = [];
try {
    $orders = $conn->query(
        "SELECT o.id, o.created_at, o.created_by, o.total, o.cash_received, o.change_due, " .
        "SUM(oi.quantity) AS item_count FROM orders o " .
        "JOIN order_items oi ON oi.order_id = o.id " .
        "GROUP BY o.id, o.created_at, o.created_by, o.total, o.cash_received, o.change_due " .
        "ORDER BY o.created_at DESC, o.id DESC LIMIT 100"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $error) {
    $message = "Orders could not be loaded. Run orders.sql in the database first.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders | Cheesy Bees</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="dashboard.php">Cheesy <span>Bees</span></a>
    <nav><a href="dashboard.php">Dashboard</a><a href="products.php">Products</a><a href="logout.php">Log out</a></nav>
</header>
<main class="page-shell">
    <div class="page-heading">
        <div><p class="eyebrow">Sales</p><h1>Order history</h1></div>
        <a class="button-link warm" href="new_order.php">New order</a>
    </div>
    <section class="panel table-wrap">
        <?php if ($message !== "") { ?>
            <p class="notice" role="alert"><?php echo htmlspecialchars($message); ?></p>
        <?php } elseif (!$orders) { ?>
            <p class="notice">No sales yet. <a href="new_order.php">Create the first order</a>.</p>
        <?php } else { ?>
        <table>
            <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Cashier</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $order) { ?>
            <tr>
                <td>#<?php echo (int) $order["id"]; ?></td>
                <td><?php echo htmlspecialchars($order["created_at"]); ?></td>
                <td><?php echo (int) $order["item_count"]; ?></td>
                <td class="price"><?php echo htmlspecialchars($order["total"]); ?></td>
                <td><?php echo htmlspecialchars($order["created_by"]); ?></td>
                <td><a class="text-action" href="order.php?id=<?php echo (int) $order["id"]; ?>">View</a></td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </section>
</main>
</body>
</html>
