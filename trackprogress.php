<?php
session_start();

// ✅ Check if Supervisor is logged in
if (!isset($_SESSION['supervisor_loggedin']) || $_SESSION['supervisor_loggedin'] !== true) {
    header("Location: supervisorlogin.php");
    exit();
}

// ✅ DB Connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

$supervisor_name = $_SESSION['supervisor_name'];
$filter = $_GET['filter'] ?? 'all';

// ✅ Build query based on filter
$whereClause = "";
switch($filter) {
    case 'pending':
        $whereClause = "WHERE status = 'Pending'";
        break;
    case 'inprogress':
        $whereClause = "WHERE status = 'In Progress'";
        break;
    case 'completed':
        $whereClause = "WHERE status = 'Completed'";
        break;
    case 'verified':
        $whereClause = "WHERE status = 'Verified'";
        break;
    case 'rejected':
        $whereClause = "WHERE status = 'Rejected'";
        break;
    default:
        $whereClause = "";
}

// ✅ Fetch all jobcards with status tracking
$sql = "SELECT id, vehicle_type, other_vehicle_name, job_description, mechanic_name, 
        delivery_date, status, created_at, updated_at, verified_by, supervisor_comments
        FROM jobcards 
        $whereClause
        ORDER BY 
            CASE 
                WHEN status = 'Completed' THEN 1
                WHEN status = 'In Progress' THEN 2
                WHEN status = 'Pending' THEN 3
                WHEN status = 'Rejected' THEN 4
                ELSE 5
            END,
            updated_at DESC";
$result = $conn->query($sql);

// ✅ Get statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'inprogress' => 0,
    'completed' => 0,
    'verified' => 0,
    'rejected' => 0
];

$statsQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as inprogress,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'Verified' THEN 1 ELSE 0 END) as verified,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM jobcards";
$statsResult = $conn->query($statsQuery);
if ($statsRow = $statsResult->fetch_assoc()) {
    $stats = $statsRow;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Track Job Progress</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .stats-card {
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s;
      cursor: pointer;
    }
    .stats-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .stats-number {
      font-size: 2.5rem;
      font-weight: bold;
      margin: 10px 0;
    }
    .stats-label {
      color: #6c757d;
      font-size: 0.9rem;
      text-transform: uppercase;
    }
    .filter-buttons {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin: 20px 0;
    }
    .status-pending { color: #6c757d; }
    .status-inprogress { color: #ffc107; }
    .status-completed { color: #28a745; }
    .status-verified { color: #0d6efd; }
    .status-rejected { color: #dc3545; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="supervisordashboard.php">
        <i class="bi bi-shield-check"></i> Supervisor Dashboard
      </a>
      <div>
        <span class="text-white me-3">👤 <?= htmlspecialchars($supervisor_name) ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold text-primary">
        <i class="bi bi-bar-chart"></i> Track Job Progress
      </h2>
      <a href="supervisordashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-2">
        <div class="stats-card bg-light" onclick="location.href='?filter=all'">
          <i class="bi bi-list-ul" style="font-size: 2rem; color: #495057;"></i>
          <div class="stats-number"><?= $stats['total'] ?></div>
          <div class="stats-label">Total Jobs</div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="stats-card" style="background: #f8f9fa; border: 2px solid #6c757d;" onclick="location.href='?filter=pending'">
          <i class="bi bi-hourglass" style="font-size: 2rem; color: #6c757d;"></i>
          <div class="stats-number status-pending"><?= $stats['pending'] ?></div>
          <div class="stats-label">Pending</div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="stats-card" style="background: #fff8e1; border: 2px solid #ffc107;" onclick="location.href='?filter=inprogress'">
          <i class="bi bi-gear" style="font-size: 2rem; color: #ffc107;"></i>
          <div class="stats-number status-inprogress"><?= $stats['inprogress'] ?></div>
          <div class="stats-label">In Progress</div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="stats-card" style="background: #e8f5e9; border: 2px solid #28a745;" onclick="location.href='?filter=completed'">
          <i class="bi bi-check-circle" style="font-size: 2rem; color: #28a745;"></i>
          <div class="stats-number status-completed"><?= $stats['completed'] ?></div>
          <div class="stats-label">Completed</div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="stats-card" style="background: #e3f2fd; border: 2px solid #0d6efd;" onclick="location.href='?filter=verified'">
          <i class="bi bi-patch-check" style="font-size: 2rem; color: #0d6efd;"></i>
          <div class="stats-number status-verified"><?= $stats['verified'] ?></div>
          <div class="stats-label">Verified</div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="stats-card" style="background: #ffebee; border: 2px solid #dc3545;" onclick="location.href='?filter=rejected'">
          <i class="bi bi-x-circle" style="font-size: 2rem; color: #dc3545;"></i>
          <div class="stats-number status-rejected"><?= $stats['rejected'] ?></div>
          <div class="stats-label">Rejected</div>
        </div>
      </div>
    </div>

    <!-- Active Filter Badge -->
    <?php if ($filter !== 'all'): ?>
      <div class="alert alert-info d-flex justify-content-between align-items-center">
        <span>
          <i class="bi bi-funnel"></i> 
          Showing: <strong><?= ucfirst(str_replace('inprogress', 'In Progress', $filter)) ?></strong> jobs
        </span>
        <a href="trackprogress.php" class="btn btn-sm btn-outline-primary">
          <i class="bi bi-x"></i> Clear Filter
        </a>
      </div>
    <?php endif; ?>

    <!-- Jobs Table -->
    <?php if ($result && $result->num_rows > 0): ?>
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-dark">
                <tr>
                  <th>Job ID</th>
                  <th>Vehicle</th>
                  <th>Job Description</th>
                  <th>Mechanic</th>
                  <th>Delivery Date</th>
                  <th>Status</th>
                  <th>Last Update</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($job = $result->fetch_assoc()): 
                  $statusClass = '';
                  $statusIcon = '';
                  switch($job['status']) {
                    case 'Pending':
                      $statusClass = 'bg-secondary';
                      $statusIcon = 'hourglass';
                      break;
                    case 'In Progress':
                      $statusClass = 'bg-warning text-dark';
                      $statusIcon = 'gear';
                      break;
                    case 'Completed':
                      $statusClass = 'bg-success';
                      $statusIcon = 'check-circle';
                      break;
                    case 'Verified':
                      $statusClass = 'bg-primary';
                      $statusIcon = 'patch-check';
                      break;
                    case 'Rejected':
                      $statusClass = 'bg-danger';
                      $statusIcon = 'x-circle';
                      break;
                  }
                ?>
                  <tr>
                    <td><strong>#<?= $job['id'] ?></strong></td>
                    <td>
                      <?= htmlspecialchars($job['vehicle_type']) ?>
                      <?php if($job['other_vehicle_name']): ?>
                        <br><small class="text-muted"><?= htmlspecialchars($job['other_vehicle_name']) ?></small>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($job['job_description']) ?></td>
                    <td>
                      <i class="bi bi-person-gear"></i> 
                      <?= htmlspecialchars($job['mechanic_name']) ?>
                    </td>
                    <td><?= date('d M Y', strtotime($job['delivery_date'])) ?></td>
                    <td>
                      <span class="badge <?= $statusClass ?>">
                        <i class="bi bi-<?= $statusIcon ?>"></i> 
                        <?= $job['status'] ?>
                      </span>
                    </td>
                    <td>
                      <small class="text-muted">
                        <?= date('d M Y, h:i A', strtotime($job['updated_at'])) ?>
                      </small>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $job['id'] ?>">
                        <i class="bi bi-eye"></i> View
                      </button>
                      <?php if ($job['status'] === 'Completed'): ?>
                        <a href="verifyjobs.php" class="btn btn-sm btn-success">
                          <i class="bi bi-check-circle"></i> Verify
                        </a>
                      <?php endif; ?>
                    </td>
                  </tr>

                  <!-- Detail Modal -->
                  <div class="modal fade" id="detailModal<?= $job['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                          <h5 class="modal-title">
                            <i class="bi bi-info-circle"></i> Job Card Details - #<?= $job['id'] ?>
                          </h5>
                          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <div class="row">
                            <div class="col-md-6">
                              <p><strong>Vehicle Type:</strong> <?= htmlspecialchars($job['vehicle_type']) ?></p>
                              <?php if($job['other_vehicle_name']): ?>
                                <p><strong>Vehicle Name:</strong> <?= htmlspecialchars($job['other_vehicle_name']) ?></p>
                              <?php endif; ?>
                              <p><strong>Job Description:</strong> <?= htmlspecialchars($job['job_description']) ?></p>
                              <p><strong>Mechanic:</strong> <?= htmlspecialchars($job['mechanic_name']) ?></p>
                            </div>
                            <div class="col-md-6">
                              <p><strong>Delivery Date:</strong> <?= date('d M Y', strtotime($job['delivery_date'])) ?></p>
                              <p><strong>Status:</strong> <span class="badge <?= $statusClass ?>"><?= $job['status'] ?></span></p>
                              <p><strong>Created:</strong> <?= date('d M Y, h:i A', strtotime($job['created_at'])) ?></p>
                              <p><strong>Last Updated:</strong> <?= date('d M Y, h:i A', strtotime($job['updated_at'])) ?></p>
                            </div>
                          </div>
                          <?php if ($job['verified_by']): ?>
                            <hr>
                            <div class="alert alert-info">
                              <strong><i class="bi bi-person-check"></i> Verified By:</strong> <?= htmlspecialchars($job['verified_by']) ?>
                            </div>
                          <?php endif; ?>
                          <?php if ($job['supervisor_comments']): ?>
                            <div class="alert alert-warning">
                              <strong><i class="bi bi-chat-left-text"></i> Supervisor Comments:</strong><br>
                              <?= htmlspecialchars($job['supervisor_comments']) ?>
                            </div>
                          <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info text-center">
        <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
        <h4 class="mt-3">No Jobs Found</h4>
        <p>
          <?php if ($filter === 'all'): ?>
            There are no job cards in the system yet.
          <?php else: ?>
            No jobs found with status: <strong><?= ucfirst(str_replace('inprogress', 'In Progress', $filter)) ?></strong>
          <?php endif; ?>
        </p>
        <a href="trackprogress.php" class="btn btn-primary mt-2">View All Jobs</a>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>