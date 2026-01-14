<?php
session_start();

// ✅ Check if Admin is logged in
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: adminlogin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-light">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">Admin Dashboard</a>
      <div>
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Main -->
  <div class="container py-5">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Welcome Admin</h2>
      <p class="text-muted">Full control over system data and users</p>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-md-4 col-sm-6">
        <div class="card shadow-lg border-0 text-center p-4">
          <div class="card-body">
            <i class="bi bi-person-plus display-3 text-success mb-3"></i>
            <h5 class="fw-bold">Register Employees</h5>
            <p class="text-muted">Add new staff accounts.</p>
            <a href="empreg.php" class="btn btn-success w-100">Go</a>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="card shadow-lg border-0 text-center p-4">
          <div class="card-body">
            <i class="bi bi-people display-3 text-primary mb-3"></i>
            <h5 class="fw-bold">Employee List</h5>
            <p class="text-muted">Manage all users and roles.</p>
            <a href="emplist.php" class="btn btn-primary w-100">Go</a>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="card shadow-lg border-0 text-center p-4">
          <div class="card-body">
            <i class="bi bi-bar-chart display-3 text-warning mb-3"></i>
            <h5 class="fw-bold">Reports</h5>
            <p class="text-muted">View overall system reports.</p>
            <a href="reports.php" class="btn btn-warning text-white w-100">Go</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 Vehicle Service Management | Admin Dashboard</p>
  </footer>
</body>
</html>
