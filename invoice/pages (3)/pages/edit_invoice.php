<?php
session_start();
include('../config/db.php');
include('navbar.php');

if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    die("Invoice ID is missing.");
}

$invoice_id = intval($_GET['id']);
if ($invoice_id <= 0) {
    header('HTTP/1.1 400 Bad Request');
    die("Invalid invoice ID.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

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

    if (empty($name) || empty($phone) || empty($email) || empty($service_ids)) {
        die("Required fields are missing.");
    }

    $subtotal = 0;
    $service_prices = [];

    foreach ($service_ids as $sid) {
        $sid = intval($sid);
        $stmt = $conn->prepare("SELECT price FROM services WHERE id = ?");
        $stmt->bind_param("i", $sid);
        $stmt->execute();
        $stmt->bind_result($price);
        $stmt->fetch();
        $stmt->close();

        $subtotal += $price;
        $service_prices[$sid] = $price;
    }

    $discount_amount = ($subtotal * $discount_percent) / 100;
    $gst_amount = (($subtotal - $discount_amount) * $gst_percent) / 100;
    $grand_total = ($subtotal - $discount_amount) + $gst_amount;

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("
            UPDATE invoices SET 
            customer_name=?, phone=?, email=?, address=?, 
            discount_percent=?, gst_percent=?, total_amount=?, 
            grand_total=?, payment_method=?, status=?, remarks=?
            WHERE id=?
        ");
        $stmt->bind_param("ssssddddsssi", 
            $name, $phone, $email, $address, 
            $discount_percent, $gst_percent, $subtotal, 
            $grand_total, $payment_method, $status, $remarks,
            $invoice_id
        );
        $stmt->execute();
        $stmt->close();

        $delete_stmt = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
        $delete_stmt->bind_param("i", $invoice_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        foreach ($service_prices as $sid => $price) {
            $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, service_id, price) VALUES (?, ?, ?)");
            $stmt->bind_param("iid", $invoice_id, $sid, $price);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        header("Location: view_invoice.php?id=$invoice_id");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Error updating invoice: " . $e->getMessage());
    }
}

// Fetch invoice data
$stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();
$stmt->close();

if (!$invoice) {
    header('HTTP/1.1 404 Not Found');
    die("Invoice not found.");
}

$all_services = $conn->query("SELECT * FROM services");

$selected_services = [];
$stmt = $conn->prepare("SELECT service_id FROM invoice_items WHERE invoice_id = ?");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $selected_services[] = $row['service_id'];
}
$stmt->close();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Invoice | Saanvi Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Edit Invoice #<?= htmlspecialchars($invoice_id) ?></h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($invoice['customer_name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($invoice['phone']) ?>" pattern="[0-9]{10,15}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($invoice['email']) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($invoice['address']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Services</label><br>
                    <?php $all_services->data_seek(0); ?>
                    <?php while ($service = $all_services->fetch_assoc()): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="services[]" id="service<?= $service['id'] ?>" value="<?= $service['id'] ?>"
                                <?= in_array($service['id'], $selected_services) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="service<?= $service['id'] ?>">
                                <?= htmlspecialchars($service['name']) ?> (₹<?= number_format($service['price'], 2) ?>)
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Discount %</label>
                        <input type="number" name="discount_percent" class="form-control" value="<?= htmlspecialchars($invoice['discount_percent']) ?>" min="0" max="100" step="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">GST %</label>
                        <input type="number" name="gst_percent" class="form-control" value="<?= htmlspecialchars($invoice['gst_percent']) ?>" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash" <?= $invoice['payment_method'] == 'cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="card" <?= $invoice['payment_method'] == 'card' ? 'selected' : '' ?>>Card</option>
                            <option value="upi" <?= $invoice['payment_method'] == 'upi' ? 'selected' : '' ?>>UPI</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="paid" <?= $invoice['status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="pending" <?= $invoice['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Remarks</label>
                    <input type="text" name="remarks" class="form-control" value="<?= htmlspecialchars($invoice['remarks']) ?>">
                </div>

                <div class="d-flex justify-content-between">
                    <a href="invoices.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
