<?php
include('../config/db.php');
include('navbar.php');

// Initialize search variables
$search_date = $_GET['search_date'] ?? '';
$search_service = $_GET['search_service'] ?? '';

// Build base SQL query
$sql = "SELECT b.*, s.name AS service_name 
        FROM bookings b 
        JOIN services s ON b.service_id = s.id";

// Add search conditions if provided
$where = [];
$params = [];
$types = '';

if (!empty($search_date)) {
    $where[] = "b.preferred_date = ?";
    $params[] = $search_date;
    $types .= 's';
}

if (!empty($search_service)) {
    $where[] = "s.id = ?";
    $params[] = $search_service;
    $types .= 'i';
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY b.created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get all services for dropdown
$services = $conn->query("SELECT * FROM services ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | Spa Billing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .booking-header {
            color: var(--spa-dark);
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }
        
        .search-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .table thead {
            background-color: var(--spa-primary);
            color: white;
        }
        
        .table th {
            font-weight: 500;
        }
        
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 500;
            border-radius: 50rem;
        }
        
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            margin: 0.1rem;
            min-width: 80px;
        }
        
        .btn-search {
            background-color: var(--spa-primary);
            color: white;
            border: none;
        }
        
        .btn-search:hover {
            background-color: var(--spa-dark);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="booking-header">
            <h1 class="display-5 fw-bold"><i class="fas fa-calendar-alt me-2"></i> Booking Management</h1>
            <p class="lead text-muted">View and manage all spa appointments</p>
        </div>
        
        <div class="search-card">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search by Date</label>
                    <input type="date" name="search_date" class="form-control" value="<?= htmlspecialchars($search_date) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Search by Service</label>
                    <select name="search_service" class="form-select">
                        <option value="">All Services</option>
                        <?php while ($service = $services->fetch_assoc()): ?>
                            <option value="<?= $service['id'] ?>" <?= $search_service == $service['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($service['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-search me-2">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                    <?php if (!empty($search_date) || !empty($search_service)): ?>
                        <a href="manage_bookings.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['customer_name']) ?></strong><br>
                                    <small class="text-muted"><?= $row['phone'] ?></small><br>
                                    <small class="text-muted"><?= $row['email'] ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['service_name']) ?></td>
                                <td><?= date('M j, Y', strtotime($row['preferred_date'])) ?></td>
                                <td><?= date('h:i A', strtotime($row['preferred_time'])) ?></td>
                                <td>
                                    <span class="status-badge bg-<?= match($row['status']) {
                                        'pending' => 'secondary',
                                        'accepted' => 'primary',
                                        'rejected' => 'danger',
                                        'completed' => 'success',
                                    } ?>"><?= ucfirst($row['status']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap">
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <a href="update_booking.php?id=<?= $row['id'] ?>&status=accepted" class="btn btn-success btn-action">
                                                <i class="fas fa-check me-1"></i> Accept
                                            </a>
                                            <a href="update_booking.php?id=<?= $row['id'] ?>&status=rejected" class="btn btn-danger btn-action">
                                                <i class="fas fa-times me-1"></i> Reject
                                            </a>
                                        <?php elseif ($row['status'] === 'accepted'): ?>
                                            <a href="update_booking.php?id=<?= $row['id'] ?>&status=completed" class="btn btn-primary btn-action">
                                                <i class="fas fa-spa me-1"></i> Complete
                                            </a>
                                        <?php elseif ($row['status'] === 'completed'): ?>
                                            <a href="create_invoice.php?booking_id=<?= $row['id'] ?>" class="btn btn-warning btn-action">
                                                <i class="fas fa-file-invoice me-1"></i> Invoice
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">No actions</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No bookings found. <?= (!empty($search_date) || !empty($search_service)) ? 'Try different search criteria.' : '' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>