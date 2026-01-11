<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spa Billing System - Home</title>
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
        
        .spa-header {
            color: var(--spa-dark);
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }
        
        .action-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
            background: white;
        }
        
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .action-icon {
            font-size: 1.5rem;
            margin-right: 0.75rem;
            vertical-align: middle;
        }
        
        .spa-footer {
            color: var(--spa-dark);
            padding: 1.5rem 0;
            margin-top: 3rem;
            border-top: 1px solid var(--spa-secondary);
        }
        
        .action-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            color: var(--spa-dark);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .action-link:hover {
            background-color: rgba(142, 108, 136, 0.05);
            color: var(--spa-primary);
        }
        
        .action-title {
            font-weight: 500;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="spa-header text-center">
            <h1 class="display-5 fw-bold mb-2">💆 Spa Billing System</h1>
            <p class="text-muted">Manage your spa operations with ease</p>
        </div>
        
        <div class="row">
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="action-card h-100">
                    <a href="dashboard.php" class="action-link">
                        <span class="action-icon">📊</span>
                        <span class="action-title">Admin Dashboard</span>
                    </a>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="action-card h-100">
                    <a href="services.php" class="action-link">
                        <span class="action-icon">🛠</span>
                        <span class="action-title">Manage Services</span>
                    </a>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="action-card h-100">
                    <a href="create_invoice.php" class="action-link">
                        <span class="action-icon">➕</span>
                        <span class="action-title">Create Invoice</span>
                    </a>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="action-card h-100">
                    <a href="invoices.php" class="action-link">
                        <span class="action-icon">📋</span>
                        <span class="action-title">View Invoices</span>
                    </a>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="action-card h-100">
                    <a href="analytics.php" class="action-link">
                        <span class="action-icon">📈</span>
                        <span class="action-title">Service Analytics</span>
                    </a>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="action-card h-100">
                    <a href="booking_form.php" class="action-link">
                        <span class="action-icon">📝</span>
                        <span class="action-title">Booking Form (Customer)</span>
                    </a>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="action-card h-100">
                    <a href="manage_bookings.php" class="action-link">
                        <span class="action-icon">📅</span>
                        <span class="action-title">Manage Bookings</span>
                    </a>
                </div>
            </div>
        </div>
        
        <footer class="spa-footer text-center small">
            Built with ❤️ for your Spa Billing System
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple animation for cards on load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.action-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>