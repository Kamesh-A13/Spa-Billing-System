<?php
include('../config/db.php');
include('navbar.php');
// Get counts for dashboard cards
$totalCustomers = $conn->query("SELECT COUNT(*) as total FROM customers")->fetch_assoc()['total'];
$totalBookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
$completedSessions = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'completed'")->fetch_assoc()['total'];
$totalRevenue = $conn->query("SELECT SUM(grand_total) as total FROM invoices")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Spa Billing System</title>
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
        
        .dashboard-header {
            color: var(--spa-dark);
            padding: 1.5rem 0;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .stat-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            position: relative;
            min-height: 150px;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--spa-primary), var(--spa-secondary));
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.2;
            position: absolute;
            right: 20px;
            top: 20px;
        }
        
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
        }
        
        .revenue-card {
            background: linear-gradient(135deg, var(--spa-dark), var(--spa-primary));
            color: white;
        }
        
        .customers-card {
            background-color: #fff;
            color: var(--spa-dark);
        }
        
        .bookings-card {
            background-color: #fff;
            color: var(--spa-dark);
        }
        
        .sessions-card {
            background-color: #fff;
            color: var(--spa-dark);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-3">
        <div class="dashboard-header">
            <h1 class="display-5 fw-bold"><i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard</h1>
            <p class="text-muted mb-0">Quick overview of your spa business performance</p>
        </div>
        
        <div class="row">
            <!-- Customers Card -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card customers-card">
                    <div class="card-body p-4">
                        <i class="fas fa-users stat-icon"></i>
                        <h5 class="card-title text-uppercase small text-muted mb-3">Total Customers</h5>
                        <p class="stat-value mb-0"><?= $totalCustomers ?></p>
                        <div class="mt-2">
                            <span class="badge bg-primary">+5% this month</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bookings Card -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card bookings-card">
                    <div class="card-body p-4">
                        <i class="fas fa-calendar-check stat-icon"></i>
                        <h5 class="card-title text-uppercase small text-muted mb-3">Total Bookings</h5>
                        <p class="stat-value mb-0"><?= $totalBookings ?></p>
                        <div class="mt-2">
                            <span class="badge bg-success">+12% this week</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sessions Card -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card sessions-card">
                    <div class="card-body p-4">
                        <i class="fas fa-spa stat-icon"></i>
                        <h5 class="card-title text-uppercase small text-muted mb-3">Completed Sessions</h5>
                        <p class="stat-value mb-0"><?= $completedSessions ?></p>
                        <div class="mt-2">
                            <span class="badge bg-info">+8% today</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Revenue Card -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card revenue-card">
                    <div class="card-body p-4">
                        <i class="fas fa-rupee-sign stat-icon"></i>
                        <h5 class="card-title text-uppercase small text-white-50 mb-3">Total Revenue</h5>
                        <p class="stat-value mb-0">₹<?= number_format($totalRevenue, 2) ?></p>
                        <div class="mt-2">
                            <span class="badge bg-light text-dark">+15% this month</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Dashboard Sections -->
        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Recent Bookings</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Recent booking data will appear here</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Pending Actions</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Pending actions will appear here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple animation for cards on load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });
    </script>
</body>
</html>