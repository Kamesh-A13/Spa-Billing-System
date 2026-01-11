<?php
include('../config/db.php');
include('navbar.php');
$id = $_GET['id'];
$status = $_GET['status'];

$allowed = ['pending', 'accepted', 'rejected', 'completed'];
if (in_array($status, $allowed)) {
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

header("Location: manage_bookings.php");
exit();


