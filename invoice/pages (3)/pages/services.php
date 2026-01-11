<?php
include('../config/db.php');
include('navbar.php');

// Handle add service form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO services (name, price, duration, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdis", $name, $price, $duration, $description);
    $stmt->execute();
    $stmt->close();
    header("Location: services.php");
    exit();
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM services WHERE id = $id");
    header("Location: services.php");
    exit();
}

// Fetch all services
$services = $conn->query("SELECT * FROM services ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services | Spa Billing System</title>
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
        
        .services-header {
            color: var(--spa-dark);
            padding: 1.5rem 0;
            margin-bottom: 1rem;
        }
        
        .add-service-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .services-table-container {
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
        
        .btn-spa {
            background-color: var(--spa-primary);
            color: white;
            border: none;
        }
        
        .btn-spa:hover {
            background-color: var(--spa-dark);
        }
        
        .service-icon {
            color: var(--spa-primary);
            margin-right: 8px;
        }
        
        .price-badge {
            background-color: rgba(142, 108, 136, 0.1);
            color: var(--spa-dark);
            font-weight: 500;
            padding: 0.35em 0.65em;
            border-radius: 50rem;
        }
        
        .duration-badge {
            background-color: rgba(212, 184, 199, 0.2);
            color: var(--spa-dark);
            padding: 0.35em 0.65em;
            border-radius: 50rem;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="services-header">
            <h1 class="display-5 fw-bold"><i class="fas fa-spa me-2"></i> Manage Services</h1>
            <p class="lead text-muted">Add, edit, or remove spa services</p>
        </div>
        
        <div class="add-service-card">
            <h5 class="mb-3"><i class="fas fa-plus-circle me-2"></i> Add New Service</h5>
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Service Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Hot Stone Massage" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Price (₹)</label>
                    <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Duration (min)</label>
                    <input type="number" name="duration" class="form-control" placeholder="60" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" placeholder="Short description">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-spa w-100">
                        <i class="fas fa-save me-1"></i> Add
                    </button>
                </div>
            </form>
        </div>
        
        <div class="services-table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($services->num_rows > 0): ?>
                            <?php while ($row = $services->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td>
                                    <i class="fas fa-spa service-icon"></i>
                                    <strong><?= htmlspecialchars($row['name']) ?></strong>
                                </td>
                                <td>
                                    <span class="price-badge">
                                        ₹<?= number_format($row['price'], 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="duration-badge">
                                        <?= $row['duration'] ?> min
                                    </span>
                                </td>
                                <td>
                                    <?= !empty($row['description']) ? htmlspecialchars($row['description']) : '<span class="text-muted">No description</span>' ?>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <a href="#" class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#editServiceModal<?= $row['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="services.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this service?')" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No services found. Add your first service above.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple animation for table rows
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateX(10px)';
                setTimeout(() => {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>