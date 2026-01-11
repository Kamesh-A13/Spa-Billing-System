<?php
session_start();
include('../config/db.php');
include('navbar.php');

// Validate booking ID
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : null;
if (!$booking_id || $booking_id <= 0) {
    header('HTTP/1.1 400 Bad Request');
    die("Invalid or missing booking ID.");
}

// Fetch booking and service info
$stmt = $conn->prepare("
    SELECT b.*, s.name AS service_name, s.price AS service_price 
    FROM bookings b 
    JOIN services s ON b.service_id = s.id 
    WHERE b.id = ?
");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $booking_id);
if (!$stmt->execute()) {
    die("Database error: " . $stmt->error);
}
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('HTTP/1.1 404 Not Found');
    die("Booking not found for ID #$booking_id.");
}
$booking = $result->fetch_assoc();
$stmt->close();

// Fetch all services
$services = $conn->query("SELECT * FROM services");
if (!$services) {
    die("Database error: " . $conn->error);
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Invoice | Saanvi Spa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --spa-primary: #8e6c88;
            --spa-secondary: #d4b8c7;
            --spa-light: #f9f5f8;
            --spa-dark: #4a3a4a;
        }

        body {
            background-color: var(--spa-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .invoice-header {
            color: var(--spa-dark);
            padding: 1.5rem 0;
            margin-bottom: 1rem;
        }

        .invoice-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 2rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--spa-dark);
        }

        .service-item {
            padding: 0.75rem;
            border: 1px solid #eee;
            border-radius: 5px;
            margin-bottom: 0.5rem;
            background-color: #f9f9f9;
        }

        .btn-spa {
            background-color: var(--spa-primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
        }

        .btn-spa:hover {
            background-color: var(--spa-dark);
        }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="invoice-header">
        <h1 class="display-5 fw-bold"><i class="fas fa-file-invoice me-2"></i> Create Invoice</h1>
        <p class="lead text-muted">Generate invoice for Booking #<?= htmlspecialchars($booking['id']) ?></p>
    </div>

    <div class="invoice-card">
        <form method="POST" action="save_invoice.php" id="invoiceForm">
            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id']) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <h5 class="mb-3"><i class="fas fa-user-circle me-2"></i> Customer Information</h5>
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control"
                           value="<?= htmlspecialchars($booking['customer_name']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control"
                           value="<?= htmlspecialchars($booking['phone']) ?>"
                           pattern="[0-9]{10,15}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($booking['email']) ?>">
                </div>
            </div>

            <h5 class="mb-3"><i class="fas fa-spa me-2"></i> Services</h5>
            <div class="mb-4">
                <?php while ($srv = $services->fetch_assoc()): ?>
                    <div class="service-item">
                        <div class="form-check">
                            <input class="form-check-input service-checkbox" type="checkbox"
                                   name="service_ids[]" value="<?= $srv['id'] ?>"
                                   id="service-<?= $srv['id'] ?>"
                                   data-price="<?= $srv['price'] ?>"
                                   <?= $srv['id'] == $booking['service_id'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="service-<?= $srv['id'] ?>">
                                <strong><?= htmlspecialchars($srv['name']) ?></strong> - ₹<?= number_format($srv['price'], 2) ?>
                                <small class="text-muted d-block"><?= htmlspecialchars($srv['description'] ?? '') ?></small>
                            </label>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <h5 class="mb-3"><i class="fas fa-calculator me-2"></i> Pricing</h5>
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="enable_gst" onclick="toggleGST()">
                        <label class="form-check-label" for="enable_gst">Apply GST</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Discount %</label>
                    <input type="number" name="discount_percent" id="discount"
                           class="form-control" value="0" min="0" max="100" step="0.01"
                           oninput="updateTotal()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">GST %</label>
                    <input type="number" name="gst_percent" id="gst"
                           class="form-control" value="0" min="0" step="0.01"
                           oninput="updateTotal()" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subtotal</label>
                    <input type="text" name="subtotal" id="subtotal"
                           class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Grand Total</label>
                    <input type="text" name="grand_total" id="grand_total"
                           class="form-control" readonly>
                </div>
            </div>

            <h5 class="mb-3"><i class="fas fa-credit-card me-2"></i> Payment</h5>
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="upi">UPI</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Status</label>
                    <select name="status" class="form-select" required>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Remarks</label>
                    <input type="text" name="remarks" class="form-control" placeholder="Any special instructions">
                </div>
            </div>

            <div class="d-flex justify-content-end no-print">
                <button type="submit" class="btn btn-spa">
                    <i class="fas fa-save me-2"></i> Generate Invoice
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function clampInputs() {
        const discountField = document.getElementById('discount');
        const gstField = document.getElementById('gst');

        if (discountField.value > 100) discountField.value = 100;
        if (discountField.value < 0) discountField.value = 0;
        if (gstField.value < 0) gstField.value = 0;
    }

    function updateTotal() {
        clampInputs();
        const checkboxes = document.querySelectorAll('.service-checkbox:checked');
        let subtotal = 0;
        checkboxes.forEach(cb => {
            subtotal += parseFloat(cb.dataset.price);
        });

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const gst = document.getElementById('gst').disabled ? 0 : parseFloat(document.getElementById('gst').value) || 0;

        const discountAmount = subtotal * discount / 100;
        const taxableAmount = subtotal - discountAmount;
        const gstAmount = taxableAmount * gst / 100;
        const grandTotal = taxableAmount + gstAmount;

        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('grand_total').value = grandTotal.toFixed(2);
    }

    function toggleGST() {
        const gstField = document.getElementById('gst');
        gstField.disabled = !gstField.disabled;
        if (gstField.disabled) gstField.value = 0;
        updateTotal();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.service-checkbox').forEach(cb => {
            cb.addEventListener('change', updateTotal);
        });

        document.getElementById('invoiceForm').addEventListener('submit', function (e) {
            const checkedServices = document.querySelectorAll('.service-checkbox:checked');
            if (checkedServices.length === 0) {
                e.preventDefault();
                alert('Please select at least one service.');
            }
        });

        updateTotal();
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
