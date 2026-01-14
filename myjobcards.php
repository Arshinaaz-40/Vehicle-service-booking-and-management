<?php
session_start();
include("db.php");

// ✅ Ensure mechanic is logged in
if (!isset($_SESSION['mechanic_loggedin']) || $_SESSION['mechanic_loggedin'] !== true) {
    header("Location: mechaniclogin.php");
    exit();
}

// ✅ Get mechanic name from session
$mechanic_name = $_SESSION['mechanic_name'] ?? '';

// ✅ Mark notifications as read when viewed
if (isset($_GET['mark_read']) && $_GET['mark_read'] == '1') {
    $conn->query("UPDATE notifications SET is_read=1 WHERE user_type='mechanic' AND user_name='$mechanic_name'");
}

// ✅ Fetch unread notifications
$notif_sql = "SELECT * FROM notifications WHERE user_type='mechanic' AND user_name=? AND is_read=0 ORDER BY created_at DESC";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("s", $mechanic_name);
$notif_stmt->execute();
$notifications = $notif_stmt->get_result();

// ✅ Fetch job cards assigned to this mechanic
$sql = "SELECT * FROM jobcards WHERE mechanic_name = ? ORDER BY 
        CASE 
            WHEN status = 'Rejected' THEN 1
            WHEN status = 'In Progress' THEN 2
            WHEN status = 'Pending' THEN 3
            ELSE 4
        END,
        created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mechanic_name);
$stmt->execute();
$result = $stmt->get_result();

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status='In Progress' THEN 1 ELSE 0 END) as inprogress,
    SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status='Verified' THEN 1 ELSE 0 END) as verified,
    SUM(CASE WHEN status='Rejected' THEN 1 ELSE 0 END) as rejected
    FROM jobcards WHERE mechanic_name=?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("s", $mechanic_name);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Job Cards</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: #dc3545;
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 0.7rem;
    }
    .notification-item {
      padding: 15px;
      border-left: 4px solid #0d6efd;
      background: #e3f2fd;
      margin-bottom: 10px;
      border-radius: 5px;
      animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn {
      from { opacity: 0; transform: translateX(-20px); }
      to { opacity: 1; transform: translateX(0); }
    }
    .stats-mini {
      display: flex;
      gap: 15px;
      margin: 20px 0;
      flex-wrap: wrap;
    }
    .stats-mini .stat {
      padding: 10px 20px;
      border-radius: 8px;
      text-align: center;
    }
    .job-status-rejected { border-left: 5px solid #dc3545 !important; background: #ffebee; }
    .job-status-pending { border-left: 5px solid #6c757d !important; }
    .job-status-inprogress { border-left: 5px solid #ffc107 !important; background: #fff8e1; }
    .job-status-completed { border-left: 5px solid #28a745 !important; background: #e8f5e9; }
    .job-status-verified { border-left: 5px solid #0d6efd !important; background: #e3f2fd; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="mechanicdashboard.php">
        <i class="bi bi-tools"></i> Mechanic Dashboard
      </a>
      <div>
        <span class="text-white me-3">👤 <?= htmlspecialchars($mechanic_name) ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary fw-bold">
        <i class="bi bi-clipboard-check"></i> My Job Cards
      </h2>
      <a href="mechanicdashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <!-- Notifications Section -->
    <?php if ($notifications->num_rows > 0): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
          <span>
            <i class="bi bi-bell-fill"></i> 
            <strong>New Notifications (<?= $notifications->num_rows ?>)</strong>
          </span>
          <a href="?mark_read=1" class="btn btn-sm btn-light">
            <i class="bi bi-check-all"></i> Mark All as Read
          </a>
        </div>
        <div class="card-body">
          <?php while ($notif = $notifications->fetch_assoc()): ?>
            <div class="notification-item">
              <div class="d-flex justify-content-between">
                <div>
                  <i class="bi bi-info-circle text-primary"></i>
                  <?= htmlspecialchars($notif['message']) ?>
                </div>
                <small class="text-muted">
                  <?= date('d M Y, h:i A', strtotime($notif['created_at'])) ?>
                </small>
              </div>
              <?php if ($notif['jobcard_id']): ?>
                <a href="#job<?= $notif['jobcard_id'] ?>" class="btn btn-sm btn-primary mt-2">
                  <i class="bi bi-eye"></i> View Job Card
                </a>
              <?php endif; ?>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="stats-mini">
      <div class="stat bg-light border">
        <strong><?= $stats['total'] ?></strong><br>
        <small>Total Jobs</small>
      </div>
      <div class="stat" style="background: #f8f9fa; border: 2px solid #6c757d;">
        <strong><?= $stats['pending'] ?></strong><br>
        <small>Pending</small>
      </div>
      <div class="stat" style="background: #fff8e1; border: 2px solid #ffc107;">
        <strong><?= $stats['inprogress'] ?></strong><br>
        <small>In Progress</small>
      </div>
      <div class="stat" style="background: #e8f5e9; border: 2px solid #28a745;">
        <strong><?= $stats['completed'] ?></strong><br>
        <small>Completed</small>
      </div>
      <div class="stat" style="background: #e3f2fd; border: 2px solid #0d6efd;">
        <strong><?= $stats['verified'] ?></strong><br>
        <small>Verified</small>
      </div>
      <div class="stat" style="background: #ffebee; border: 2px solid #dc3545;">
        <strong><?= $stats['rejected'] ?></strong><br>
        <small>Rejected</small>
      </div>
    </div>

    <!-- Job Cards -->
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <i class="bi bi-briefcase"></i> Assigned Job Cards
      </div>
      <div class="card-body">
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): 
            $statusClass = 'job-status-' . strtolower(str_replace(' ', '', $row['status']));
          ?>
            <div class="card mb-3 <?= $statusClass ?>" id="job<?= $row['id'] ?>">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-8">
                    <h5 class="card-title">
                      <i class="bi bi-hash"></i> Job Card #<?= $row['id'] ?>
                      <?php if ($row['status'] === 'Rejected'): ?>
                        <span class="badge bg-danger">⚠️ ACTION REQUIRED</span>
                      <?php endif; ?>
                    </h5>
                    <p class="mb-1">
                      <strong>Vehicle:</strong> <?= htmlspecialchars($row['vehicle_type']) ?>
                      <?php if ($row['other_vehicle_name']): ?>
                        - <?= htmlspecialchars($row['other_vehicle_name']) ?>
                      <?php endif; ?>
                    </p>
                    <p class="mb-1">
                      <strong>Job:</strong> <?= htmlspecialchars($row['job_description']) ?>
                    </p>
                    <p class="mb-1">
                      <strong>Delivery Date:</strong> 
                      <?= date('d M Y', strtotime($row['delivery_date'])) ?>
                    </p>
                    <?php if ($row['remarks']): ?>
                      <p class="mb-1 text-muted">
                        <i class="bi bi-chat-left-text"></i> 
                        <em><?= htmlspecialchars($row['remarks']) ?></em>
                      </p>
                    <?php endif; ?>
                    <?php if ($row['supervisor_comments']): ?>
                      <div class="alert alert-warning mt-2 mb-0">
                        <strong><i class="bi bi-person-badge"></i> Supervisor Feedback:</strong><br>
                        <?= htmlspecialchars($row['supervisor_comments']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="col-md-4 text-end">
                    <?php
                      $badgeClass = '';
                      $icon = '';
                      switch($row['status']) {
                        case 'Pending': $badgeClass = 'bg-secondary'; $icon = 'hourglass'; break;
                        case 'In Progress': $badgeClass = 'bg-warning text-dark'; $icon = 'gear'; break;
                        case 'Completed': $badgeClass = 'bg-success'; $icon = 'check-circle'; break;
                        case 'Verified': $badgeClass = 'bg-primary'; $icon = 'patch-check'; break;
                        case 'Rejected': $badgeClass = 'bg-danger'; $icon = 'x-circle'; break;
                      }
                    ?>
                    <span class="badge <?= $badgeClass ?> mb-2" style="font-size: 1rem;">
                      <i class="bi bi-<?= $icon ?>"></i> <?= $row['status'] ?>
                    </span>
                    <br>
                    <small class="text-muted d-block mb-3">
                      Created: <?= date('d M Y', strtotime($row['created_at'])) ?>
                    </small>
                    <a href="updatestatus.php?job_id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                      <i class="bi bi-pencil"></i> Update Status
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="alert alert-info text-center">
            <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
            <h5 class="mt-3">No Job Cards Assigned</h5>
            <p class="mb-0">You don't have any job cards assigned to you yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>