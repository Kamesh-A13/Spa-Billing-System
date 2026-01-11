<?php
$host = "localhost";
$user = "root"; // or your MySQL username
$password = ""; // or your MySQL password
$database = "spa_billing_system";

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
