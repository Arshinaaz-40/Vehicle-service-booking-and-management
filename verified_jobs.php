<?php
session_start();

// Check if Manager is logged in
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

// Fetch verified jobs
$sql = "SELECT j.*, 
        (SELECT COUNT(*) FROM invoices WHERE jobcard_id = j.id) as has_invoice
        FROM jobcards j 
        WHERE j.status='Verified'
        ORDER BY j.verified_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verified Jobs - Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .job-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-left: 5px solid #28a745;
    }
    .job-header {
      display: flex;
      justify-content: between;
      align-items: center;
      border-bottom: 2px solid #e9ecef;
      padding-bottom: 15px;
      margin-bottom: 15px;
    }
    .detail-row {
      display: flex;
      margin-bottom: 10px;
    }
    .detail-label {
      font-weight: 600;
      color: #495057;
      width: 180px;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="managerdashboard.php">
        <i class="bi bi-briefcase"></i> Manager Dashboard
      </a>
      <div>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold text-success">
        <i class="bi bi-patch-check"></i> Verified Jobs (Ready for Invoice)
      </h2>
      <a href="managerdashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($job = $result->fetch_assoc()): ?>
        <div class="job-card">
          <div class="job-header">
            <div>
              <h4 class="mb-1">
                <span class="text-success">Job #<?= $job['id'] ?></span>
                <?php if ($job['has_invoice'] > 0): ?>
                  <span class="badge bg-info ms-2">
                    <i class="bi bi-check-circle"></i> Invoice Generated
                  </span>
                <?php else: ?>
                  <span class="badge bg-warning text-dark ms-2">
                    <i class="bi bi-exclamation-triangle"></i> Invoice Pending
                  </span>
                <?php endif; ?>
              </h4>
              <small class="text-muted">
                <i class="bi bi-calendar-check"></i> 
                Verified on: <?= date('d M Y, h:i A', strtotime($job['verified_at'])) ?>
              </small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-car-front"></i> Vehicle Type:</span>
                <span><?= htmlspecialchars($job['vehicle_type']) ?></span>
              </div>
              <?php if ($job['other_vehicle_name']): ?>
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-info-circle"></i> Vehicle Name:</span>
                <span><?= htmlspecialchars($job['other_vehicle_name']) ?></span>
              </div>
              <?php endif; ?>
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-clipboard-check"></i> Job Description:</span>
                <span><?= htmlspecialchars($job['job_description']) ?></span>
              </div>
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-person-gear"></i> Mechanic:</span>
                <span><?= htmlspecialchars($job['mechanic_name']) ?></span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-calendar-event"></i> Delivery Date:</span>
                <span><?= date('d M Y', strtotime($job['delivery_date'])) ?></span>
              </div>
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-person-check"></i> Verified By:</span>
                <span><?= htmlspecialchars($job['verified_by']) ?></span>
              </div>
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-calendar-plus"></i> Created:</span>
                <span><?= date('d M Y', strtotime($job['created_at'])) ?></span>
              </div>
            </div>
          </div>

          <?php if ($job['supervisor_comments']): ?>
          <div class="alert alert-success mt-3">
            <strong><i class="bi bi-chat-left-text"></i> Supervisor Comments:</strong><br>
            <?= nl2br(htmlspecialchars($job['supervisor_comments'])) ?>
          </div>
          <?php endif; ?>

          <?php if ($job['remarks']): ?>
          <div class="mt-3">
            <strong><i class="bi bi-journal-text"></i> Job Remarks:</strong>
            <p class="mb-0 ms-3"><?= nl2br(htmlspecialchars($job['remarks'])) ?></p>
          </div>
          <?php endif; ?>

          <div class="mt-3 text-end">
            <?php if ($job['has_invoice'] == 0): ?>
              <a href="invoice.php?jobcard_id=<?= $job['id'] ?>" class="btn btn-success">
                <i class="bi bi-file-earmark-text"></i> Generate Invoice
              </a>
            <?php else: ?>
              <a href="invoice.php?jobcard_id=<?= $job['id'] ?>" class="btn btn-info">
                <i class="bi bi-eye"></i> View Invoice
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="alert alert-info text-center">
        <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
        <h4 class="mt-3">No Verified Jobs Found</h4>
        <p>All verified jobs have invoices generated or there are no verified jobs yet.</p>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>