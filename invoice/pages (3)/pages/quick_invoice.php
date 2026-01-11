<?php
include('../config/db.php');

// Load all services from DB
$services = $conn->query("SELECT id, name, price FROM services");
if (!$services) {
    die("SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quick Invoice - Saanvi Spa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
  <h2 class="mb-4">Create Quick Invoice</h2>
  <form action="save_invoice.php" method="POST" id="invoiceForm">
    <div class="mb-3 row">
      <div class="col-md-6">
        <label for="customer_name" class="form-label">Customer Name</label>
        <input type="text" class="form-control" name="customer_name" id="customer_name" required>
      </div>
      <div class="col-md-6">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" class="form-control" name="phone" id="phone">
      </div>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" class="form-control" name="email" id="email">
    </div>
    <div class="mb-3">
      <label for="address" class="form-label">Address</label>
      <input type="text" class="form-control" name="address" id="address">
    </div>
    <div class="mb-3">
      <label class="form-label">Select Services</label>
      <?php while($row = $services->fetch_assoc()): ?>
        <div class="form-check">
          <input class="form-check-input service-checkbox" type="checkbox"
                 name="service_ids[]" value="<?= $row['id'] ?>"
                 data-name="<?= htmlspecialchars($row['name']) ?>"
                 data-amount="<?= $row['price'] ?>"
                 id="service<?= $row['id'] ?>">
          <label class="form-check-label" for="service<?= $row['id'] ?>">
            <?= htmlspecialchars($row['name']) ?> - ₹<?= $row['price'] ?>
          </label>
        </div>
      <?php endwhile; ?>
    </div>
    <div class="row mb-3">
      <div class="col-md-4">
        <label for="discount" class="form-label">Discount (%)</label>
        <input type="number" class="form-control" name="discount_percent" id="discount" value="0">
      </div>
      <div class="col-md-4">
        <label for="gst" class="form-label">GST (%)</label>
        <input type="number" class="form-control" name="gst_percent" id="gst" value="0">
      </div>
      <div class="col-md-4">
        <label for="total_amount" class="form-label">Total Amount (₹)</label>
        <input type="text" class="form-control" id="total_amount" readonly>
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-md-6">
        <label for="payment_method" class="form-label">Payment Method</label>
        <select name="payment_method" id="payment_method" class="form-control">
          <option value="cash">Cash</option>
          <option value="card">Card</option>
          <option value="upi">UPI</option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="status" class="form-label">Payment Status</label>
        <select name="status" id="status" class="form-control">
          <option value="paid">Paid</option>
          <option value="pending">Pending</option>
        </select>
      </div>
    </div>
    <div class="mb-3">
      <label for="remarks" class="form-label">Remarks</label>
      <textarea name="remarks" id="remarks" class="form-control" rows="2"></textarea>
    </div>
    <button type="submit" class="btn btn-success">Generate Invoice</button>
  </form>
</div>
<script>
  function updateTotal() {
    let total = 0;
    document.querySelectorAll('.service-checkbox:checked').forEach(cb => {
      total += parseFloat(cb.getAttribute('data-amount')) || 0;
    });
    let discount = parseFloat(document.getElementById('discount').value) || 0;
    let gst = parseFloat(document.getElementById('gst').value) || 0;
    total = total - (total * discount / 100);
    total = total + (total * gst / 100);
    document.getElementById('total_amount').value = total.toFixed(2);
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.service-checkbox').forEach(cb => {
      cb.addEventListener('change', updateTotal);
    });
    document.getElementById('discount').addEventListener('input', updateTotal);
    document.getElementById('gst').addEventListener('input', updateTotal);
    updateTotal();

    document.getElementById('invoiceForm').addEventListener('submit', function (e) {
      const checked = document.querySelectorAll('.service-checkbox:checked');
      if (checked.length === 0) {
        e.preventDefault();
        alert('Please select at least one service.');
      }
    });
  });
</script>
</body>
</html>