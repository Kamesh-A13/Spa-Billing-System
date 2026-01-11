<?php
include('../config/db.php');

// Validate invoice ID
if (!isset($_POST['invoice_id']) || !is_numeric($_POST['invoice_id'])) {
    die("Invalid invoice ID.");
}

// Validate and sanitize inputs
$invoice_id = intval($_POST['invoice_id']);
$name = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
$phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
$email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
$address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
$service_ids = is_array($_POST['services'] ?? []) ? $_POST['services'] : [];
$discount_percent = floatval($_POST['discount_percent'] ?? 0);
$gst_percent = floatval($_POST['gst_percent'] ?? 0);
$payment_method = in_array($_POST['payment_method'] ?? '', ['cash', 'card', 'upi']) ? $_POST['payment_method'] : 'cash';
$status = in_array($_POST['status'] ?? '', ['paid', 'pending']) ? $_POST['status'] : 'pending';
$remarks = mysqli_real_escape_string($conn, $_POST['remarks'] ?? '');

// Validate required fields
if (empty($name) || empty($phone) || empty($email) || empty($service_ids)) {
    die("Required fields are missing.");
}

// Calculate amounts
$subtotal = 0;
$service_prices = [];

foreach ($service_ids as $sid) {
    $sid = intval($sid);
    $stmt = $conn->prepare("SELECT price FROM services WHERE id = ?");
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $sid);
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }
    $stmt->bind_result($price);
    $stmt->fetch();
    $stmt->close();
    
    if ($price === null) {
        die("Invalid service ID: " . $sid);
    }
    
    $subtotal += $price;
    $service_prices[$sid] = $price;
}

$discount_amount = ($subtotal * $discount_percent) / 100;
$gst_amount = (($subtotal - $discount_amount) * $gst_percent) / 100;
$grand_total = ($subtotal - $discount_amount) + $gst_amount;

// Start transaction
$conn->begin_transaction();

try {
    // Update invoice
    $stmt = $conn->prepare("
        UPDATE invoices SET 
        customer_name=?, phone=?, email=?, address=?, 
        discount_percent=?, gst_percent=?, total_amount=?, 
        grand_total=?, payment_method=?, status=?, remarks=?
        WHERE id=?
    ");
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("ssssddddsssi", 
        $name, $phone, $email, $address, 
        $discount_percent, $gst_percent, $subtotal, 
        $grand_total, $payment_method, $status, $remarks,
        $invoice_id
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Error executing statement: " . $stmt->error);
    }
    $stmt->close();

    // Update services
    $delete_stmt = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
    if (!$delete_stmt) {
        throw new Exception("Error preparing delete statement: " . $conn->error);
    }
    $delete_stmt->bind_param("i", $invoice_id);
    if (!$delete_stmt->execute()) {
        throw new Exception("Error executing delete statement: " . $delete_stmt->error);
    }
    $delete_stmt->close();
    
    foreach ($service_prices as $sid => $price) {
        $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, service_id, price) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing insert statement: " . $conn->error);
        }
        $stmt->bind_param("iid", $invoice_id, $sid, $price);
        if (!$stmt->execute()) {
            throw new Exception("Error executing insert statement: " . $stmt->error);
        }
        $stmt->close();
    }

    $conn->commit();
    header("Location: invoices.php");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    die("Error updating invoice: " . $e->getMessage());
}