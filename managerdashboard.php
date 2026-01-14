<?php
session_start();

// ✅ Check if Manager is logged in
if (!isset($_SESSION['manager_loggedin']) || $_SESSION['manager_loggedin'] !== true) {
    header("Location: managerlogin.php");
    exit();
}

// Database connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$managerName = $_SESSION['manager_name'] ?? 'Manager';

// Get unread notifications count
$notif_count = 0;
$notif_result = $conn->query("SELECT COUNT(*) as count FROM manager_notifications WHERE is_read=0");
if ($notif_result) {
    $notif_row = $notif_result->fetch_assoc();
    $notif_count = $notif_row['count'];
}

// Get recent notifications
$notifications = $conn->query("SELECT * FROM manager_notifications WHERE is_read=0 ORDER BY created_at DESC LIMIT 5");

// Mark as read if requested
if (isset($_GET['mark_read'])) {
    $notif_id = intval($_GET['mark_read']);
    $conn->query("UPDATE manager_notifications SET is_read=1 WHERE id=$notif_id");
    header("Location: managerdashboard.php");
    exit();
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $conn->query("UPDATE manager_notifications SET is_read=1");
    header("Location: managerdashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manager Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    .notification-badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background: #dc3545;
      color: white;
      border-radius: 50%;
      padding: 4px 8px;
      font-size: 0.75rem;
      font-weight: bold;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    .notification-card {
      background: #fff3cd;
      border-left: 4px solid #ffc107;
      padding: 15px;
      margin-bottom: 10px;
      border-radius: 5px;
      transition: all 0.3s;
    }
    .notification-card:hover {
      background: #ffe69c;
      transform: translateX(5px);
    }
  </style>
</head>
<body class="bg-light">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">Manager Dashboard</a>
      <div>
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container py-5">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Welcome, <?php echo htmlspecialchars($managerName); ?> 👋</h2>
      <p class="text-muted">Manage quotations, job cards, invoices, employees, and reports</p>
    </div>

    <!-- ✅ NOTIFICATIONS SECTION -->
    <?php if ($notif_count > 0): ?>
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <span>
          <i class="bi bi-bell-fill"></i> 
          <strong>New Notifications (<?= $notif_count ?>)</strong>
        </span>
        <a href="?mark_all_read=1" class="btn btn-sm btn-dark">
          <i class="bi bi-check-all"></i> Mark All as Read
        </a>
      </div>
      <div class="card-body">
        <?php while ($notif = $notifications->fetch_assoc()): ?>
          <div class="notification-card">
            <div class="d-flex justify-content-between">
              <div>
                <i class="bi bi-info-circle text-warning"></i>
                <?= htmlspecialchars($notif['message']) ?>
              </div>
              <div>
                <small class="text-muted me-3">
                  <?= date('d M Y, h:i A', strtotime($notif['created_at'])) ?>
                </small>
                <a href="?mark_read=<?= $notif['id'] ?>" class="btn btn-sm btn-outline-success">
                  <i class="bi bi-check"></i>
                </a>
              </div>
            </div>
            <div class="mt-2">
              <a href="invoice.php?jobcard_id=<?= $notif['jobcard_id'] ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-file-earmark-text"></i> Generate Invoice
              </a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">

      <!-- Send Quotation -->
      <div class="col-md-4 col-sm-6">
        <div class="card shadow-lg text-center p-4 border-0">
          <div class="card-body">
            <i class="bi bi-envelope display-3 text-primary mb-3"></i>
            <h5 class="fw-bold">Send Quotation</h5>
            <p class="text-muted">Prepare and send quotations.</p>
            <a href="mail.php" class="btn btn-primary w-100">Go</a>
          </div>
        </div>
      </div>

      <!-- CREATE JOB CARD -->
      <div class="col-md-4 col-sm-6">
        <div class="card shadow-lg text-center p-4 border-0">
          <div class="card-body">
            <i class="bi bi-card-checklist display-3 text-success mb-3"></i>
            <h5 class="fw-bold">Create Job Card</h5>
            <p class="text-muted">Assign and track job cards.</p>
            <a href="jobcard.php" class="btn btn-success w-100">Go</a>
          </div>
        </div>
      </div>

      <!-- GENERATE INVOICE -->
      <div class="col-md-4 col-sm-6">
        <div class="card shadow-lg text-center p-4 border-0" style="position: relative;">
          <?php if ($notif_count > 0): ?>
            <span class="notification-badge"><?= $notif_count ?></span>
          <?php endif; ?>
          <div class="card-body">
            <i class="bi bi-file-earmark-text display-3 text-info mb-3"></i>
            <h5 class="fw-bold">Generate Invoice</h5>
            <p class="text-muted">Create and print invoices.</p>
            <a href="invoice.php" class="btn btn-info text-white w-100">Go</a>
          </div>
        </div>
      </div>

      <!-- Employee List -->
      <div class="col-md-4 col-sm-6">
        <div class="card shadow-lg text-center p-4 border-0">
          <div class="card-body">
            <i class="bi bi-people display-3 text-warning mb-3"></i>
            <h5 class="fw-bold">Employee List</h5>
            <p class="text-muted">View and manage employees.</p>
            <a href="emplist.php" class="btn btn-warning text-white w-100">Go</a>
          </div>
        </div>
      </div>

      <!-- Vehicle List -->
      <div class="col-md-4 col-sm-6">
        <div class="card shadow-lg text-center p-4 border-0">
          <div class="card-body">
            <i class="bi bi-car-front display-3 text-secondary mb-3"></i>
            <h5 class="fw-bold">Vehicle List</h5>
            <p class="text-muted">Manage customer vehicles.</p>
            <a href="viewbookings.php" class="btn btn-secondary text-white w-100">Go</a>
          </div>
        </div>
      </div>

      <!-- View Verified Jobs -->
      <div class="col-md-4 col-sm-6">
        <div class="card shadow-lg text-center p-4 border-0">
          <div class="card-body">
            <i class="bi bi-patch-check display-3 text-primary mb-3"></i>
            <h5 class="fw-bold">Verified Jobs</h5>
            <p class="text-muted">View supervisor verified jobs.</p>
            <a href="verified_jobs.php" class="btn btn-primary w-100">Go</a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 Vehicle Service Management | Manager Dashboard</p>
  </footer>

</body>
</html>
<?php $conn->close(); ?>