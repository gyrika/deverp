<?php

session_start();

if (!isset($_SESSION["user_email"])) {

    header("Location: index.php");
    exit;

}

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Cheesy Bees</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="topbar">
    <a class="brand" href="dashboard.php">Cheesy <span>Bees</span></a>

    <nav>
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
        <a href="logout.php">Log out</a>
    </nav>
</header>

<main class="page-shell dashboard-hero">

<p class="eyebrow">Your shop, at ease</p>

<h1>A good day starts with good dough.</h1>

<p class="welcome-text">
    Welcome back, 
    <?php echo htmlspecialchars($_SESSION["user_email"]); ?>
</p>

<section class="menu-card">
    <p class="eyebrow">Today’s workspace</p>
    <h2>Shape your menu.</h2>
    <p>
        Add pizzas, set prices, and gently manage what is ready for the oven.
    </p>

    <a class="button-link warm" href="products.php">Open products</a>
</section>

<section class="menu-card">
    <p class="eyebrow">Sales</p>
    <h2>Take an order.</h2>
    <p>Choose products, record cash received, and see the change due.</p>
    <a class="button-link warm" href="new_order.php">New order</a>
    <a class="dashboard-secondary-link" href="orders.php">Order history</a>
</section>

</main>

</body>

</html>
