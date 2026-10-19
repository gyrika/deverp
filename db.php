<?php
$host = getenv("DB_HOST") ?: "db";
$user = getenv("DB_USER") ?: "cheesy_user";
$password = getenv("DB_PASSWORD") ?: "change_this_password";
$database = getenv("DB_NAME") ?: "simple_site";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed.");
}
