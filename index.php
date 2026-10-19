<?php

session_start();

include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("s", $email);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            $_SESSION["user_email"] = $user["email"];

            header("Location: dashboard.php");
            exit;

        } else {

            $message = "Wrong email or password";

        }

    } else {

        $message = "Wrong email or password";

    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome Back | Cheesy Bees</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">

<main class="auth-card">

<div class="brand-mark">CB</div>

<p class="eyebrow">Cheesy Bees · Pizza House</p>

<h1>Welcome back.</h1>

<p class="intro">
    A calm little corner for your pizza shop.
</p>

<form method="POST" class="form-card">

    <div class="field">
        <label>Email</label>
        <input type="email" name="email" required>
    </div>

    <div class="field">
        <label>Password</label>
        <input type="password" name="password" required>
    </div>

    <button type="submit">Log in</button>

</form>

<p class="auth-switch">
    Do not have an account?
    <a href="register.php">Register here</a>
</p>

<p class="form-message" role="alert">
    <?php echo htmlspecialchars($message); ?>
</p>

</main>

</body>

</html>
