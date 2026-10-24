<?php
$host = "localhost";
$port = 3309;
$dbname = "studycircle_db";
$username = "root";
$password = "";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $dbname,
    $port
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
