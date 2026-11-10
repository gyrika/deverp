<?php

$host = getenv("DB_HOST");
$database = getenv("DB_NAME");
$user = getenv("DB_USER");
$password = getenv("DB_PASSWORD");

if (!$host || !$database || !$user || $password === false) {
    http_response_code(500);
    exit("Database configuration is missing.");
}

try {
    $conn = new mysqli($host, $user, $password, $database);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $error) {
    http_response_code(500);
    exit("Database connection failed.");
}
