<?php
include('../config/db.php');
include('navbar.php');
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Handle search and filtering
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where = [];
$params = [];
$types = '';

// Build WHERE clause
if (!empty($search)) {
    $where[] = "(customer_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $search_term = "%" . $conn->real_escape_string($search) . "%";
    array_push($params, $search_term, $search_term, $search_term);
    $types .= 'sss';
}

if (!empty($status_filter) && in_array($status_filter, ['paid', 'pending'])) {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($date_from)) {
    if (!DateTime::createFromFormat('Y-m-d', $date_from)) {
        die("Invalid From Date format.");
    }
    $where[] = "created_at >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    if (!DateTime::createFromFormat('Y-m-d', $date_to)) {
        die("Invalid To Date format.");
    }
    $where[] = "created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
    $types .= 's';
}

$where_clause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

$query = "SELECT * FROM invoices $where_clause ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$invoices = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Management | Saanvi Spa</title>
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

        .invoice-header {
            color: var(--spa-dark);
            padding: 1.5rem 0;
            margin-bottom: 1rem;
        }

        .search-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .invoice-table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table thead {
            background-color: var(--spa-primary);
            color: white;
        }

        .badge-paid {
            background-color: #28a745;
            color: white;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            margin: 0.1rem;
        }

        .filter-badge {
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="invoice-header">
        <h1 class="display-5 fw-bold"><i class="fas fa-file-invoice me-2"></i> Invoice Management</h1>
        <p class="lead text-muted">View and manage all spa invoices</p>
    </div>

    <div class="search-card">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control"
                       placeholder="Search by name, phone or email"
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="paid" <?= $status_filter === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control"
                       value="<?= htmlspecialchars($date_from) ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control"
                       value="<?= htmlspecialchars($date_to) ?>">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="col-md-1">
                <a href="invoices.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-sync-alt"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Filters Info -->
    <?php if (!empty($search) || !empty($status_filter) || !empty($date_from) || !empty($date_to)): ?>
        <div class="mb-3">
            <small class="text-muted">Active filters:</small>
            <?php if (!empty($search)): ?>
                <span class="badge bg-info text-dark filter-badge me-1">
                    Search: <?= htmlspecialchars($search) ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['search' => ''])) ?>" class="text-dark ms-1">
                        <i class="fas fa-times"></i>
                    </a>
                </span>
            <?php endif; ?>
            <?php if (!empty($status_filter)): ?>
                <span class="badge bg-info text-dark filter-badge me-1">
                    Status: <?= ucfirst($status_filter) ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['status' => ''])) ?>" class="text-dark ms-1">
                        <i class="fas fa-times"></i>
                    </a>
                </span>
            <?php endif; ?>
            <?php if (!empty($date_from)): ?>
                <span class="badge bg-info text-dark filter-badge me-1">
                    From: <?= $date_from ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['date_from' => ''])) ?>" class="text-dark ms-1">
                        <i class="fas fa-times"></i>
                    </a>
                </span>
            <?php endif; ?>
            <?php if (!empty($date_to)): ?>
                <span class="badge bg-info text-dark filter-badge me-1">
                    To: <?= $date_to ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['date_to' => ''])) ?>" class="text-dark ms-1">
                        <i class="fas fa-times"></i>
                    </a>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="invoice-table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($invoices->num_rows > 0): ?>
                    <?php while ($row = $invoices->fetch_assoc()): ?>
                        <tr>
                            <td>SAANVI-<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['customer_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($row['phone']) ?></small>
                            </td>
                            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td>₹<?= number_format($row['grand_total'], 2) ?></td>
                            <td>
                                <span class="badge <?= $row['status'] === 'paid' ? 'badge-paid' : 'badge-pending' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
    <div class="d-flex flex-wrap">
        <a href="view_invoice.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info action-btn">
            <i class="fas fa-eye"></i> View
        </a>
        <a href="edit_invoice.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning action-btn">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="print_invoice.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary action-btn" target="_blank">
            <i class="fas fa-print"></i> Print
        </a>
    </div>
</td>
</tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No invoices found.</td>
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
