<?php
include('../config/db.php');

$name = trim($_GET['name'] ?? '');
$phone = trim($_GET['phone'] ?? '');

if (!$name && !$phone) {
    echo json_encode([]);
    exit;
}

$query = "SELECT * FROM bookings WHERE 1=1";
$params = [];
$types = '';

if ($name !== '') {
    $query .= " AND customer_name = ?";
    $params[] = $name;
    $types .= 's';
}

if ($phone !== '') {
    $query .= " AND phone = ?";
    $params[] = $phone;
    $types .= 's';
}

$query .= " ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode([]);
}
