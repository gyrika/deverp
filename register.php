<?php

include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $message = "Your password must be at least 6 characters long.";

    } elseif ($password !== $confirm_password) {

        $message = "The passwords do not match.";

    } else {

        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            $message = "An account with this email already exists.";

        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_sql = "INSERT INTO users (email, password) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ss", $email, $hashed_password);

            if ($insert_stmt->execute()) {

                $message = "Registration successful. You can now log in.";

            } else {

                $message = "Registration failed. Please try again.";

            }
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account | Cheesy Bees</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">

<main class="auth-card">

<div class="brand-mark">CB</div>

<p class="eyebrow">Cheesy Bees · Pizza House</p>

<h1>Make an account.</h1>

<p class="intro">
    Start with a simple, welcoming space for your shop.
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

    <div class="field">
        <label>Confirm password</label>
        <input type="password" name="confirm_password" required>
    </div>

    <button type="submit">Create account</button>

</form>

<p class="form-message" role="alert">
    <?php echo htmlspecialchars($message); ?>
</p>

<p class="auth-switch">
    Already have an account?
    <a href="index.php">Log in here</a>
</p>

</main>

</body>

</html>
