<?php
session_start();

// ✅ Check if Supervisor is logged in
if (!isset($_SESSION['supervisor_loggedin']) || $_SESSION['supervisor_loggedin'] !== true) {
    header("Location: supervisorlogin.php");
    exit();
}

$supervisor_name = $_SESSION['supervisor_name'] ?? 'Supervisor';

// Database connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get real-time statistics
$stats = [
    'total_jobs' => 0,
    'pending_verification' => 0,
    'verified_today' => 0,
    'rejected_jobs' => 0,
    'in_progress' => 0
];

// Total jobs
$result = $conn->query("SELECT COUNT(*) as count FROM jobcards");
if ($row = $result->fetch_assoc()) {
    $stats['total_jobs'] = $row['count'];
}

// Pending verification (Completed status)
$result = $conn->query("SELECT COUNT(*) as count FROM jobcards WHERE status='Completed'");
if ($row = $result->fetch_assoc()) {
    $stats['pending_verification'] = $row['count'];
}

// Verified today
$result = $conn->query("SELECT COUNT(*) as count FROM jobcards WHERE status='Verified' AND DATE(verified_at) = CURDATE()");
if ($row = $result->fetch_assoc()) {
    $stats['verified_today'] = $row['count'];
}

// Rejected jobs
$result = $conn->query("SELECT COUNT(*) as count FROM jobcards WHERE status='Rejected'");
if ($row = $result->fetch_assoc()) {
    $stats['rejected_jobs'] = $row['count'];
}

// In Progress
$result = $conn->query("SELECT COUNT(*) as count FROM jobcards WHERE status='In Progress'");
if ($row = $result->fetch_assoc()) {
    $stats['in_progress'] = $row['count'];
}

// Recent supervisor actions
$recent_actions = $conn->query("SELECT * FROM supervisor_actions ORDER BY action_date DESC LIMIT 5");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Supervisor Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .dashboard-container { padding: 30px 0; }
    .welcome-card {
      background: white;
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .stat-card {
      background: white;
      border-radius: 15px;
      padding: 25px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transition: transform 0.3s, box-shadow 0.3s;
      height: 100%;
    }
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .stat-icon {
      font-size: 3rem;
      margin-bottom: 15px;
    }
    .stat-number {
      font-size: 2.5rem;
      font-weight: bold;
      margin: 10px 0;
    }
    .stat-label {
      color: #6c757d;
      font-size: 0.95rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .action-card {
      background: white;
      border-radius: 15px;
      padding: 30px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transition: all 0.3s;
      height: 100%;
      border: 2px solid transparent;
    }
    .action-card:hover {
      transform: translateY(-8px);
      border-color: #667eea;
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    .action-icon {
      font-size: 3.5rem;
      margin-bottom: 20px;
    }
    .recent-activity {
      background: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      margin-top: 30px;
    }
    .activity-item {
      padding: 15px;
      border-left: 4px solid #667eea;
      background: #f8f9fa;
      margin-bottom: 10px;
      border-radius: 5px;
    }
    .urgent-badge {
      position: absolute;
      top: -10px;
      right: -10px;
      background: #dc3545;
      color: white;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(0,0,0,0.3);">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">
        <i class="bi bi-shield-check"></i> Supervisor Control Panel
      </a>
      <div>
        <span class="text-white me-3">👤 <?= htmlspecialchars($supervisor_name) ?></span>
        <a href="logout.php" class="btn btn-outline-light">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </div>
    </div>
  </nav>

  <div class="container dashboard-container">
    
    <!-- Welcome Card -->
    <div class="welcome-card">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h2 class="mb-2">Welcome back, <?= htmlspecialchars($supervisor_name) ?>! 👋</h2>
          <p class="text-muted mb-0">
            <i class="bi bi-calendar-event"></i> <?= date('l, F j, Y') ?>
          </p>
        </div>
        <div class="col-md-4 text-end">
          <div class="alert alert-info mb-0">
            <strong><?= $stats['pending_verification'] ?></strong> jobs awaiting verification
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3 col-sm-6">
        <div class="stat-card">
          <div class="stat-icon text-primary">
            <i class="bi bi-list-ul"></i>
          </div>
          <div class="stat-number text-primary"><?= $stats['total_jobs'] ?></div>
          <div class="stat-label">Total Jobs</div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="stat-card" style="position: relative;">
          <?php if ($stats['pending_verification'] > 0): ?>
            <span class="urgent-badge"><?= $stats['pending_verification'] ?></span>
          <?php endif; ?>
          <div class="stat-icon text-warning">
            <i class="bi bi-hourglass-split"></i>
          </div>
          <div class="stat-number text-warning"><?= $stats['pending_verification'] ?></div>
          <div class="stat-label">Pending Verification</div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="stat-card">
          <div class="stat-icon text-success">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <div class="stat-number text-success"><?= $stats['verified_today'] ?></div>
          <div class="stat-label">Verified Today</div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="stat-card">
          <div class="stat-icon text-danger">
            <i class="bi bi-x-circle-fill"></i>
          </div>
          <div class="stat-number text-danger"><?= $stats['rejected_jobs'] ?></div>
          <div class="stat-label">Rejected Jobs</div>
        </div>
      </div>
    </div>

    <!-- Action Cards -->
    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <div class="action-card">
          <div class="action-icon text-success">
            <i class="bi bi-check-circle"></i>
          </div>
          <h4 class="fw-bold mb-3">Verify Job Cards</h4>
          <p class="text-muted mb-4">
            Review and approve completed work from mechanics
          </p>
          <a href="verifyjobs.php" class="btn btn-success btn-lg px-5">
            <i class="bi bi-arrow-right-circle"></i> Start Verification
          </a>
          <?php if ($stats['pending_verification'] > 0): ?>
            <div class="alert alert-warning mt-3 mb-0">
              <strong><?= $stats['pending_verification'] ?></strong> jobs waiting for your review
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-md-6">
        <div class="action-card">
          <div class="action-icon text-primary">
            <i class="bi bi-bar-chart"></i>
          </div>
          <h4 class="fw-bold mb-3">Track Progress</h4>
          <p class="text-muted mb-4">
            Monitor all jobs and mechanic performance
          </p>
          <a href="trackprogress.php" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-arrow-right-circle"></i> View Dashboard
          </a>
          <div class="mt-3">
            <small class="text-muted">
              <i class="bi bi-gear"></i> <?= $stats['in_progress'] ?> jobs currently in progress
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <?php if ($recent_actions && $recent_actions->num_rows > 0): ?>
    <div class="recent-activity">
      <h4 class="fw-bold mb-4">
        <i class="bi bi-clock-history"></i> Recent Actions
      </h4>
      <?php while ($action = $recent_actions->fetch_assoc()): ?>
        <div class="activity-item">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <strong>
                <?php
                  echo match($action['action_type']) {
                    'verify' => '<i class="bi bi-check-circle text-success"></i> Verified',
                    'reject' => '<i class="bi bi-x-circle text-danger"></i> Rejected',
                    'comment' => '<i class="bi bi-chat-left-text text-info"></i> Commented on',
                    'reassign' => '<i class="bi bi-arrow-repeat text-warning"></i> Reassigned',
                    default => '<i class="bi bi-info-circle"></i> Updated'
                  };
                ?>
              </strong>
              Job Card #<?= $action['jobcard_id'] ?>
              <?php if ($action['mechanic_name']): ?>
                - Mechanic: <?= htmlspecialchars($action['mechanic_name']) ?>
              <?php endif; ?>
              <br>
              <?php if ($action['comments']): ?>
                <small class="text-muted"><?= htmlspecialchars($action['comments']) ?></small>
              <?php endif; ?>
            </div>
            <small class="text-muted">
              <?= date('d M, h:i A', strtotime($action['action_date'])) ?>
            </small>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    <?php endif; ?>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>