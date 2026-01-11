<?php
include('../config/db.php');
include('navbar.php');
// Function to fetch top services in a given date range
function getTopServices($conn, $startDate) {
    $sql = "
        SELECT s.name, COUNT(ii.service_id) AS total_count
        FROM invoice_items ii
        JOIN invoices i ON ii.invoice_id = i.id
        JOIN services s ON ii.service_id = s.id
        WHERE i.invoice_date >= ?
        GROUP BY ii.service_id
        ORDER BY total_count DESC
        LIMIT 5
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $startDate);
    $stmt->execute();
    return $stmt->get_result();
}

// Get start dates
$thisWeek = date('Y-m-d', strtotime('monday this week'));
$thisMonth = date('Y-m-01');
$thisYear = date('Y-01-01');

// Query results
$topWeek = getTopServices($conn, $thisWeek);
$topMonth = getTopServices($conn, $thisMonth);
$topYear = getTopServices($conn, $thisYear);
?>

<!DOCTYPE html>
<html lang="en">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<head>
  <title>Analytics</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
  <div class="container">
    <h2 class="mb-4">Top Services Analytics</h2>

    <div class="row">
      <!-- This Week -->
      <div class="col-md-4">
        <div class="card shadow">
          <div class="card-header bg-primary text-white">Top Services This Week</div>
          <div class="card-body">
            <?php if ($topWeek->num_rows > 0): ?>
              <ul class="list-group">
                <?php while ($row = $topWeek->fetch_assoc()): ?>
                  <li class="list-group-item d-flex justify-content-between">
                    <?= htmlspecialchars($row['name']) ?>
                    <span class="badge bg-primary"><?= $row['total_count'] ?></span>
                  </li>
                <?php endwhile; ?>
              </ul>
            <?php else: ?>
              <p>No data found.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- This Month -->
      <div class="col-md-4">
        <div class="card shadow">
          <div class="card-header bg-success text-white">Top Services This Month</div>
          <div class="card-body">
            <?php if ($topMonth->num_rows > 0): ?>
              <ul class="list-group">
                <?php while ($row = $topMonth->fetch_assoc()): ?>
                  <li class="list-group-item d-flex justify-content-between">
                    <?= htmlspecialchars($row['name']) ?>
                    <span class="badge bg-success"><?= $row['total_count'] ?></span>
                  </li>
                <?php endwhile; ?>
              </ul>
            <?php else: ?>
              <p>No data found.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- This Year -->
      <div class="col-md-4">
        <div class="card shadow">
          <div class="card-header bg-warning text-dark">Top Services This Year</div>
          <div class="card-body">
            <?php if ($topYear->num_rows > 0): ?>
              <ul class="list-group">
                <?php while ($row = $topYear->fetch_assoc()): ?>
                  <li class="list-group-item d-flex justify-content-between">
                    <?= htmlspecialchars($row['name']) ?>
                    <span class="badge bg-warning text-dark"><?= $row['total_count'] ?></span>
                  </li>
                <?php endwhile; ?>
              </ul>
            <?php else: ?>
              <p>No data found.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
