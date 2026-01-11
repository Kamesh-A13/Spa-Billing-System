<?php
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['customer_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $service_id = (int)$_POST['service_id'];
    $preferred_date = $_POST['preferred_date'];
    $preferred_time = $_POST['preferred_time'];

    $stmt = $conn->prepare("INSERT INTO bookings (customer_name, phone, email, service_id, preferred_date, preferred_time) VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param('sssiss', $name, $phone, $email, $service_id, $preferred_date, $preferred_time);

    if ($stmt->execute()) {
        echo "✅ Booking submitted successfully!" ;
    } else {
        echo "❌ Booking failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
