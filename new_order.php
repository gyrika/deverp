<?php

session_start();
if (!isset($_SESSION["user_email"])) {
    header("Location: index.php");
    exit;
}

include "db.php";

function money_to_cents($value) {
    $value = (string) $value;
    if (!preg_match('/^(0|[1-9][0-9]{0,9})(\.[0-9]{1,2})?$/', $value)) {
        return null;
    }
    $parts = explode('.', $value, 2);
    return (int) $parts[0] * 100 + (int) str_pad($parts[1] ?? '', 2, '0');
}

function cents_to_money($cents) {
    return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
}

if (!isset($_SESSION["order_csrf"])) {
    $_SESSION["order_csrf"] = bin2hex(random_bytes(32));
}

$message = "";
$submitted_quantities = [];
$cash_input = "";
$transaction_started = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submitted_quantities = $_POST["quantity"] ?? [];
    $cash_input = is_scalar($_POST["cash_received"] ?? null)
        ? trim((string) $_POST["cash_received"]) : "";
    $posted_token = $_POST["csrf_token"] ?? null;

    if (!is_string($posted_token) || !hash_equals($_SESSION["order_csrf"], $posted_token)) {
        $message = "Your session expired. Refresh the page and try again.";
    } elseif (!is_array($submitted_quantities) || count($submitted_quantities) > 100) {
        $message = "Please choose valid products.";
        $submitted_quantities = [];
    } else {
        try {
            $selected = [];
            foreach ($submitted_quantities as $id => $quantity) {
                if (!ctype_digit((string) $id) || !is_scalar($quantity) ||
                    !ctype_digit((string) $quantity) || (int) $quantity > 99) {
                    throw new RuntimeException("Please enter quantities from 0 to 99.");
                }
                if ((int) $quantity > 0) {
                    $selected[(int) $id] = (int) $quantity;
                }
            }
            if (!$selected) {
                throw new RuntimeException("Choose at least one product.");
            }

            $cash_cents = money_to_cents($cash_input);
            if ($cash_cents === null) {
                throw new RuntimeException("Enter a valid cash amount with up to two decimal places.");
            }

            $conn->begin_transaction();
            $transaction_started = true;
            $items = [];
            $total_cents = 0;
            $product_stmt = $conn->prepare(
                "SELECT name, price FROM products WHERE id = ? AND is_available = 1 FOR UPDATE"
            );
            foreach ($selected as $product_id => $quantity) {
                $product_stmt->bind_param("i", $product_id);
                $product_stmt->execute();
                $product = $product_stmt->get_result()->fetch_assoc();
                if (!$product) {
                    throw new RuntimeException("A selected product is no longer available. Refresh and try again.");
                }
                $price_cents = money_to_cents($product["price"]);
                $line_cents = $price_cents * $quantity;
                $total_cents += $line_cents;
                if ($total_cents > 999999999999) {
                    throw new RuntimeException("This order is too large.");
                }
                $items[] = [$product_id, $product["name"], $price_cents, $quantity, $line_cents];
            }
            if ($cash_cents < $total_cents) {
                throw new RuntimeException("Cash received must cover the order total of " . cents_to_money($total_cents) . ".");
            }

            $total = cents_to_money($total_cents);
            $cash = cents_to_money($cash_cents);
            $change = cents_to_money($cash_cents - $total_cents);
            $created_by = $_SESSION["user_email"];
            $order_stmt = $conn->prepare(
                "INSERT INTO orders (created_by, total, cash_received, change_due) VALUES (?, ?, ?, ?)"
            );
            $order_stmt->bind_param("ssss", $created_by, $total, $cash, $change);
            $order_stmt->execute();
            $order_id = $conn->insert_id;

            $item_stmt = $conn->prepare(
                "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total) " .
                "VALUES (?, ?, ?, ?, ?, ?)"
            );
            foreach ($items as [$product_id, $name, $price_cents, $quantity, $line_cents]) {
                $unit_price = cents_to_money($price_cents);
                $line_total = cents_to_money($line_cents);
                $item_stmt->bind_param("iissis", $order_id, $product_id, $name, $unit_price, $quantity, $line_total);
                $item_stmt->execute();
            }
            $conn->commit();
            $transaction_started = false;
            $_SESSION["order_csrf"] = bin2hex(random_bytes(32));
            header("Location: order.php?id=" . $order_id);
            exit;
        } catch (RuntimeException $error) {
            if ($transaction_started) $conn->rollback();
            $message = $error->getMessage();
        } catch (mysqli_sql_exception $error) {
            if ($transaction_started) $conn->rollback();
            error_log("Order save failed: " . $error->getMessage());
            $message = $error->getCode() == 1146
                ? "Order tables are missing. Run orders.sql in the database or restart Docker Compose."
                : "Order could not be saved. Check the database logs.";
        }
    }
}

try {
    $products = $conn->query(
        "SELECT id, name, category, price FROM products WHERE is_available = 1 ORDER BY category, name"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $error) {
    $products = [];
    $message = "Products could not be loaded. Check the database setup.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Order | Cheesy Bees</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="dashboard.php">Cheesy <span>Bees</span></a>
    <nav><a href="dashboard.php">Dashboard</a><a href="products.php">Products</a><a href="orders.php">Orders</a><a href="logout.php">Log out</a></nav>
</header>
<main class="page-shell">
    <div class="page-heading">
        <div><p class="eyebrow">Cash checkout</p><h1>New order</h1></div>
        <a class="button-link" href="orders.php">Order history</a>
    </div>
    <section class="panel order-panel">
        <?php if ($message !== "") { ?><p class="notice" role="alert"><?php echo htmlspecialchars($message); ?></p><?php } ?>
        <?php if (!$products && $message === "") { ?>
            <p class="notice">There are no available products. <a href="add_product.php">Add a product</a> to start an order.</p>
        <?php } elseif ($products) { ?>
        <form method="POST" class="order-form" id="order-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["order_csrf"]); ?>">
            <div class="table-wrap"><table>
                <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Quantity</th></tr></thead>
                <tbody>
                <?php foreach ($products as $product) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($product["name"]); ?></td>
                    <td><?php echo htmlspecialchars($product["category"]); ?></td>
                    <td class="price"><?php echo number_format((float) $product["price"], 2); ?></td>
                    <td><input class="quantity-input" type="number" name="quantity[<?php echo (int) $product["id"]; ?>]" min="0" max="99" step="1" value="<?php echo htmlspecialchars(is_scalar($submitted_quantities[$product["id"]] ?? null) ? (string) $submitted_quantities[$product["id"]] : "0"); ?>" data-price="<?php echo htmlspecialchars($product["price"]); ?>" aria-label="Quantity for <?php echo htmlspecialchars($product["name"]); ?>"></td>
                </tr>
                <?php } ?>
                </tbody>
            </table></div>
            <div class="checkout-summary">
                <p><strong>Total:</strong> <span id="order-total">0.00</span></p>
                <div class="field"><label for="cash_received">Cash received</label><input id="cash_received" type="number" name="cash_received" min="0" step="0.01" value="<?php echo htmlspecialchars($cash_input); ?>" required></div>
                <p><strong>Change:</strong> <span id="change-due">0.00</span></p>
                <button type="submit">Complete sale</button>
            </div>
        </form>
        <?php } ?>
    </section>
</main>
<script>
const quantities = document.querySelectorAll('.quantity-input');
const cash = document.querySelector('#cash_received');
function updateTotal() {
    let total = 0;
    quantities.forEach(input => {
        total += Math.round(Number(input.dataset.price) * 100) * Number(input.value || 0);
    });
    document.querySelector('#order-total').textContent = (total / 100).toFixed(2);
    document.querySelector('#change-due').textContent = Math.max(0, Number(cash.value || 0) - total / 100).toFixed(2);
}
if (cash) {
    quantities.forEach(input => input.addEventListener('input', updateTotal));
    cash.addEventListener('input', updateTotal);
    updateTotal();
}
</script>
</body>
</html>
