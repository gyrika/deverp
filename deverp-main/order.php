<?php

session_start();
if (!isset($_SESSION["user_email"])) {
    header("Location: index.php");
    exit;
}

include "db.php";

$order = null;
$items = [];
$message = "";
$id = $_GET["id"] ?? "";
if (!is_string($id) || !ctype_digit($id) || (int) $id < 1) {
    http_response_code(400);
    $message = "Invalid order number.";
} else {
    try {
        $stmt = $conn->prepare(
            "SELECT id, created_at, created_by, total, cash_received, change_due FROM orders WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        if (!$order) {
            http_response_code(404);
            $message = "Order not found.";
        } else {
            $stmt = $conn->prepare(
                "SELECT product_name, unit_price, quantity, line_total FROM order_items WHERE order_id = ? ORDER BY id"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    } catch (mysqli_sql_exception $error) {
        $message = "Order could not be loaded. Check that orders.sql has been run.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order | Cheesy Bees</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="dashboard.php">Cheesy <span>Bees</span></a>
    <nav><a href="dashboard.php">Dashboard</a><a href="products.php">Products</a><a href="orders.php">Orders</a><a href="logout.php">Log out</a></nav>
</header>
<main class="page-shell">
    <?php if ($message !== "") { ?>
        <section class="panel"><p class="notice" role="alert"><?php echo htmlspecialchars($message); ?></p></section>
    <?php } else { ?>
        <div class="page-heading">
            <div><p class="eyebrow">Cash sale</p><h1>Order #<?php echo (int) $order["id"]; ?></h1></div>
            <a class="button-link warm" href="new_order.php">New order</a>
        </div>
        <p class="welcome-text">Sold <?php echo htmlspecialchars($order["created_at"]); ?> by <?php echo htmlspecialchars($order["created_by"]); ?></p>
        <section class="panel table-wrap">
            <table>
                <thead><tr><th>Product</th><th>Unit price</th><th>Quantity</th><th>Line total</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($item["product_name"]); ?></td>
                    <td><?php echo htmlspecialchars($item["unit_price"]); ?></td>
                    <td><?php echo (int) $item["quantity"]; ?></td>
                    <td class="price"><?php echo htmlspecialchars($item["line_total"]); ?></td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </section>
        <section class="panel receipt-summary">
            <p><strong>Total:</strong> <?php echo htmlspecialchars($order["total"]); ?></p>
            <p><strong>Cash received:</strong> <?php echo htmlspecialchars($order["cash_received"]); ?></p>
            <p><strong>Change due:</strong> <?php echo htmlspecialchars($order["change_due"]); ?></p>
        </section>
    <?php } ?>
    <a class="back-link" href="orders.php">← Back to orders</a>
</main>
</body>
</html>
