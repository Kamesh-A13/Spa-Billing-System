<?php
session_start();
include('../config/db.php');

// Validate required fields
$required = ['customer_name', 'phone', 'service_ids', 'payment_method', 'status'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        die(json_encode(['error' => "Required field '$field' is missing."]));
    }
}

if (!is_array($_POST['service_ids'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid service selection']));
}

// Sanitize inputs
$customer_name     = trim($_POST['customer_name']);
$phone             = trim($_POST['phone']);
$email             = trim($_POST['email'] ?? '');
$booking_id        = intval($_POST['booking_id'] ?? 0);
$discount_percent  = max(0, min(100, floatval($_POST['discount_percent'] ?? 0)));
$gst_percent       = max(0, floatval($_POST['gst_percent'] ?? 0));
$payment_method    = in_array($_POST['payment_method'], ['cash', 'card', 'upi']) ? $_POST['payment_method'] : 'cash';
$status            = in_array($_POST['status'], ['paid', 'pending']) ? $_POST['status'] : 'pending';
$remarks           = trim($_POST['remarks'] ?? '');
$service_ids       = array_map('intval', $_POST['service_ids']);

// Validate phone number
if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid phone number format']));
}

// Validate email if present
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid email format']));
}

// Fetch service prices
$subtotal = 0;
$service_prices = [];

foreach ($service_ids as $service_id) {
    $stmt = $conn->prepare("SELECT price FROM services WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        die(json_encode(['error' => 'DB error: ' . $conn->error]));
    }

    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        http_response_code(400);
        die(json_encode(['error' => "Service ID $service_id not found"]));
    }

    $service = $result->fetch_assoc();
    $price = floatval($service['price']);
    $subtotal += $price;
    $service_prices[$service_id] = $price;
    $stmt->close();
}

// Calculate totals
$discount_amount = ($subtotal * $discount_percent) / 100;
$taxable         = $subtotal - $discount_amount;
$gst_amount      = ($taxable * $gst_percent) / 100;
$grand_total     = $taxable + $gst_amount;

// Begin transaction
$conn->begin_transaction();

try {
    // Insert into invoices (no staff columns)
    $stmt = $conn->prepare("
        INSERT INTO invoices 
        (customer_name, phone, email, discount_percent, gst_percent, 
         total_amount, grand_total, payment_method, status, remarks) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

    $stmt->bind_param(
        "sssddddsss",
        $customer_name, $phone, $email,
        $discount_percent, $gst_percent,
        $subtotal, $grand_total,
        $payment_method, $status,
        $remarks
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $invoice_id = $stmt->insert_id;
    $stmt->close();

    // Insert invoice items
    foreach ($service_prices as $service_id => $price) {
        $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, service_id, price) VALUES (?, ?, ?)");
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param("iid", $invoice_id, $service_id, $price);
        if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

        $stmt->close();
    }

    // Update booking status if needed
    if ($booking_id > 0) {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?");
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param("i", $booking_id);
        if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

        $stmt->close();
    }

    $conn->commit();
    header("Location: view_invoice.php?id=$invoice_id");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    die("Error creating invoice: " . $e->getMessage());
}
