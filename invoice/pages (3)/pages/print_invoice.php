<?php
include('../config/db.php');

$invoice_id = $_GET['id'] ?? null;
if (!$invoice_id || !is_numeric($invoice_id)) {
    die("Invalid invoice ID.");
}

// Fetch invoice data
$invoice_stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
if (!$invoice_stmt) {
    die("Error preparing statement: " . $conn->error);
}

$invoice_stmt->bind_param("i", $invoice_id);
if (!$invoice_stmt->execute()) {
    die("Error executing statement: " . $invoice_stmt->error);
}

$invoice_result = $invoice_stmt->get_result();
$invoice = $invoice_result->fetch_assoc();
$invoice_stmt->close();

if (!$invoice) {
    die("Invoice not found.");
}

// Fetch service items
$items_stmt = $conn->prepare("
    SELECT ii.price, s.name, s.description
    FROM invoice_items ii 
    JOIN services s ON ii.service_id = s.id 
    WHERE ii.invoice_id = ?
");
if (!$items_stmt) {
    die("Error preparing items statement: " . $conn->error);
}

$items_stmt->bind_param("i", $invoice_id);
if (!$items_stmt->execute()) {
    die("Error executing items statement: " . $items_stmt->error);
}

$items = $items_stmt->get_result();

// Calculate amounts for display
$subtotal = $invoice['total_amount'] ?? 0;
$discount_percent = $invoice['discount_percent'] ?? 0;
$gst_percent = $invoice['gst_percent'] ?? 0;
$discount_amount = $subtotal * $discount_percent / 100;
$taxable_amount = $subtotal - $discount_amount;
$gst_amount = $taxable_amount * $gst_percent / 100;
$grand_total = $invoice['grand_total'] ?? 0;
$status = strtolower($invoice['status'] ?? 'pending');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= htmlspecialchars($invoice['id'] ?? '') ?> | Saanvi Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white;
            font-family: Arial, sans-serif;
            font-size: 12pt;
        }
        .invoice-container {
            width: 100%;
            padding: 0;
            margin: 0;
        }
        .invoice-header {
            border-bottom: 2px solid #8e6c88;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .spa-logo {
            height: 80px;
            margin-bottom: 10px;
        }
        .invoice-title {
            font-size: 28px;
            color: #333;
            font-weight: bold;
        }
        .customer-details {
            background-color: #f9f5f8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #8e6c88 !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f5f8;
        }
        .badge-paid {
            background-color: #28a745;
            color: white;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .service-description {
            color: #6c757d;
            font-size: 0.9rem;
        }
        @page {
            size: auto;
            margin: 10mm;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="row">
                <div class="col-md-6">
                    <img src="logo.png" alt="Saanvi Spa Logo" class="spa-logo">
                    <div>4th floor, Ramee strand inn hotel, Hosur Rd, Singasandra</div>
                    <div>Bengaluru, Karnataka 560068</div>
                    <div>Phone: +91-77084 02672</div>
                    <div>Email: info@saanvispa.com</div>
                    <div>GSTIN: 29CABPR0553A1Z1</div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="invoice-title">INVOICE</div>
                    <div><strong>Date:</strong> <?= htmlspecialchars(date('d M Y', strtotime($invoice['created_at'] ?? 'now'))) ?></div>
                    <div><strong>Invoice #:</strong> SAANVI-<?= htmlspecialchars(str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT)) ?></div>
                </div>
            </div>
        </div>
        
        <div class="customer-details">
            <div class="row">
                <div class="col-md-6">
                    <h5>Customer Details</h5>
                    <div><strong>Name:</strong> <?= htmlspecialchars($invoice['customer_name'] ?? '') ?></div>
                    <div><strong>Phone:</strong> <?= htmlspecialchars($invoice['phone'] ?? '') ?></div>
                    <div><strong>Email:</strong> <?= htmlspecialchars($invoice['email'] ?? '') ?></div>
                </div>
                <div class="col-md-6">
                    <h5>Payment Information</h5>
                    <div class="mb-2">
                        <strong>Status:</strong> 
                        <span class="badge <?= $status === 'paid' ? 'badge-paid' : 'badge-pending' ?>">
                            <?= ucfirst($status) ?>
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Method:</strong> <?= ucfirst($invoice['payment_method'] ?? 'cash') ?>
                    </div>
                    <?php if (!empty($invoice['remarks'])): ?>
                    <div class="mb-2">
                        <strong>Remarks:</strong> <?= htmlspecialchars($invoice['remarks']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <table class="table table-bordered mb-4">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="45%">Description</th>
                    <th width="20%" class="text-right">Unit Price (₹)</th>
                    <th width="20%" class="text-right">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td>
                        <div><strong><?= htmlspecialchars($item['name'] ?? '') ?></strong></div>
                        <?php if (!empty($item['description'])): ?>
                        <div class="service-description"><?= htmlspecialchars($item['description']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><?= number_format($item['price'] ?? 0, 2) ?></td>
                    <td class="text-right"><?= number_format($item['price'] ?? 0, 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-end">SUBTOTAL</td>
                    <td class="text-right">₹<?= number_format($subtotal, 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-end">DISCOUNT (<?= $discount_percent ?>%)</td>
                    <td class="text-right">- ₹<?= number_format($discount_amount, 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-end">GST (<?= $gst_percent ?>%)</td>
                    <td class="text-right">+ ₹<?= number_format($gst_amount, 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-end"><strong>GRAND TOTAL</strong></td>
                    <td class="text-right"><strong>₹<?= number_format($grand_total, 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="mt-5 pt-4 border-top">
            <div class="row">
                <div class="col-md-12">
                    <h5>Thank You For Choosing Saanvi Spa!</h5>
                    <p>We appreciate your trust in our services. Your wellness is our priority.</p>
                    <p class="mt-3">
                        <strong>Visit us again soon!</strong><br>
                        For appointments: +91-77084 02672
                    </p>
                </div>
            </div>
        </div>
        
        <div class="mt-4 pt-3 text-center small text-muted">
            <p>This is a computer generated invoice. No signature required.</p>
        </div>
    </div>
</body>
</html>