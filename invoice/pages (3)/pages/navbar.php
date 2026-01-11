<!-- navbar.php -->
<nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background-color: #8e6c88;">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
      <img src="logo.png" alt="Saanvi Spa Logo" height="30" class="me-2">
      <span>Saanvi Spa Admin</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">
            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="booking_form.php">
            <i class="fas fa-calendar-plus me-1"></i> New Booking
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="manage_bookings.php">
            <i class="fas fa-calendar-check me-1"></i> Manage Bookings
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="quick_invoice.php">
            <i class="fas fa-bolt me-1"></i> Quick Invoice
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="services.php">
            <i class="fas fa-spa me-1"></i> Services
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="invoices.php">
            <i class="fas fa-file-invoice me-1"></i> Invoices
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="analytics.php">
            <i class="fas fa-chart-line me-1"></i> Analytics
          </a>
        </li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-user-circle me-1"></i> Admin
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
