<?php
session_start();

// ✅ Check if Mechanic is logged in
if (!isset($_SESSION['mechanic_loggedin']) || $_SESSION['mechanic_loggedin'] !== true) {
    header("Location: mechaniclogin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mechanic Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-light">

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">Mechanic Dashboard</a>
      <div>
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container py-5">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Welcome Mechanic</h2>
      <p class="text-muted">Handle assigned job cards and update service status</p>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-md-4 col-sm-6">
        <div class="card shadow-lg border-0 text-center p-4 h-100">
          <div class="card-body">
            <i class="bi bi-wrench-adjustable display-3 text-warning mb-3"></i>
            <h5 class="fw-bold">My Job Cards</h5>
            <p class="text-muted">View and manage your assigned jobs.</p>
            <a href="myjobcards.php" class="btn btn-warning text-white w-100">Go</a>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="card shadow-lg border-0 text-center p-4 h-100">
          <div class="card-body">
            <i class="bi bi-clipboard-check display-3 text-success mb-3"></i>
            <h5 class="fw-bold">Update Status</h5>
            <p class="text-muted">Mark work as complete or pending.</p>
            <a href="updatestatus.php" class="btn btn-success w-100">Go</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 Vehicle Service Management | Mechanic Dashboard</p>
  </footer>
</body>
</html>
