<?php
include('../config/db.php');
include('navbar.php');
$services = $conn->query("SELECT * FROM services");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment | Spa Billing System</title>
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
            text-align: center;
        }
        
        .booking-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            padding: 2rem;
            margin: 0 auto;
            max-width: 800px;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--spa-dark);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border: 1px solid var(--spa-secondary);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--spa-primary);
            box-shadow: 0 0 0 0.25rem rgba(142, 108, 136, 0.25);
        }
        
        .btn-spa {
            background-color: var(--spa-primary);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }
        
        .btn-spa:hover {
            background-color: var(--spa-dark);
            transform: translateY(-2px);
        }
        
        .service-icon {
            margin-right: 10px;
            color: var(--spa-primary);
        }
        
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="booking-header">
            <h1 class="display-5 fw-bold"><i class="fas fa-calendar-plus me-2"></i> Book an Appointment</h1>
            <p class="lead text-muted">Schedule your relaxing spa experience</p>
        </div>
        
        <div class="booking-card">
            <form method="POST" action="submit_booking.php" class="row g-4">
                <div class="col-md-6">
                    <label class="form-label required-field">Customer Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="customer_name" class="form-control" placeholder="Your full name" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label required-field">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="tel" name="phone" class="form-control" placeholder="Your contact number" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Your email (optional)">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label required-field">Service</label>
                    <select name="service_id" class="form-select" required>
                        <option value="" selected disabled>Select a service...</option>
                        <?php while ($row = $services->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>">
                                <i class="fas fa-spa service-icon"></i> <?= htmlspecialchars($row['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label required-field">Preferred Date</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                        <input type="date" name="preferred_date" class="form-control" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label required-field">Preferred Time</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                        <input type="time" name="preferred_time" class="form-control" required>
                    </div>
                </div>
                
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-spa" target="blank">
                        <i class="fas fa-calendar-check me-2"></i> Book Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[type="date"]').min = today;
            
            // Add animation to form elements
            const formElements = document.querySelectorAll('.form-control, .form-select, .btn-spa');
            formElements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.querySelector('input[name="customer_name"]');
    const phoneInput = document.querySelector('input[name="phone"]');
    const emailInput = document.querySelector('input[name="email"]');

    async function fetchCustomerData() {
        const name = nameInput.value.trim();
        const phone = phoneInput.value.trim();

        if (!name && !phone) return;

        const response = await fetch(`get_customer.php?name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}`);
        if (!response.ok) return;

        const data = await response.json();

        if (data) {
            if (data.customer_name && !name) nameInput.value = data.customer_name;
            if (data.phone && !phone) phoneInput.value = data.phone;
            if (data.email) emailInput.value = data.email;
        }
    }

    nameInput.addEventListener('blur', fetchCustomerData);
    phoneInput.addEventListener('blur', fetchCustomerData);
});
    </script>
</body>
</html>