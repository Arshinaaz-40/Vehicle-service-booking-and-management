<?php
session_start();
include("db.php");

// ✅ Ensure mechanic is logged in
if (!isset($_SESSION['mechanic_loggedin']) || $_SESSION['mechanic_loggedin'] !== true) {
    header("Location: mechaniclogin.php");
    exit();
}

$mechanic_name = $_SESSION['mechanic_name'];
$message = "";
$job_data = null;

// ✅ Get job_id from URL if provided
$preselect_job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

// ✅ If job_id is provided, fetch job data
if ($preselect_job_id > 0) {
    $fetch_sql = "SELECT * FROM jobcards WHERE id = ? AND mechanic_name = ?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param("is", $preselect_job_id, $mechanic_name);
    $fetch_stmt->execute();
    $job_result = $fetch_stmt->get_result();
    if ($job_result->num_rows > 0) {
        $job_data = $job_result->fetch_assoc();
    }
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = intval($_POST['job_id']);
    $new_status = $_POST['status'];
    $mechanic_notes = trim($_POST['mechanic_notes'] ?? '');

    // ✅ Validate status change
    $valid_transitions = [
        'Pending' => ['In Progress'],
        'In Progress' => ['Completed'],
        'Rejected' => ['In Progress'], // Allow resubmission after rejection
        'Completed' => [], // Can't change once completed
        'Verified' => [] // Can't change once verified
    ];

    // Get current status
    $check_sql = "SELECT status FROM jobcards WHERE id = ? AND mechanic_name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $job_id, $mechanic_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $current = $check_result->fetch_assoc();
        $current_status = $current['status'];

        // Check if transition is valid
        if (in_array($new_status, $valid_transitions[$current_status] ?? [])) {
            // ✅ Update status
            $update_sql = "UPDATE jobcards SET status = ?, remarks = CONCAT(remarks, '\n[Mechanic Update] ', ?), updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $new_status, $mechanic_notes, $job_id);
            
            if ($update_stmt->execute()) {
                // ✅ Notify supervisor when status changes to Completed
                if ($new_status === 'Completed') {
                    $notif_msg = "Job Card #$job_id has been marked as COMPLETED by mechanic $mechanic_name and is ready for verification.";
                    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_type, message, jobcard_id) VALUES ('supervisor', ?, ?)");
                    $notif_stmt->bind_param("si", $notif_msg, $job_id);
                    $notif_stmt->execute();
                }
                
                $message = "<div class='alert alert-success'>✅ Status updated from <strong>$current_status</strong> to <strong>$new_status</strong> successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>❌ Failed to update status. Try again.</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>⚠️ Invalid status transition. Current status: <strong>$current_status</strong>. You can only change to: " . implode(', ', $valid_transitions[$current_status] ?? ['None']) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>⚠️ Job not found or not assigned to you.</div>";
    }
}

// ✅ Fetch all jobs for this mechanic for dropdown
$jobs_sql = "SELECT id, vehicle_type, job_description, status FROM jobcards WHERE mechanic_name = ? AND status NOT IN ('Verified') ORDER BY id DESC";
$jobs_stmt = $conn->prepare($jobs_sql);
$jobs_stmt->bind_param("s", $mechanic_name);
$jobs_stmt->execute();
$jobs_result = $jobs_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Job Status</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .status-flow {
      display: flex;
      justify-content: space-between;
      margin: 20px 0;
      position: relative;
    }
    .status-step {
      flex: 1;
      text-align: center;
      padding: 15px;
      background: #e9ecef;
      border-radius: 8px;
      margin: 0 5px;
      position: relative;
    }
    .status-step.active {
      background: linear-gradient(135deg, #0d6efd, #0dcaf0);
      color: white;
      font-weight: bold;
    }
    .status-step.completed {
      background: #28a745;
      color: white;
    }
    .status-arrow {
      position: absolute;
      right: -10px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1.5rem;
      color: #6c757d;
    }
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

  <div class="container mt-5">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0">
          <i class="bi bi-pencil-square"></i> Update Job Status
        </h3>
      </div>
      <div class="card-body">
        
        <div class="alert alert-info">
          <strong><i class="bi bi-info-circle"></i> Status Flow:</strong>
          <div class="status-flow mt-3">
            <div class="status-step">
              <i class="bi bi-hourglass"></i><br>Pending
              <span class="status-arrow">→</span>
            </div>
            <div class="status-step">
              <i class="bi bi-gear"></i><br>In Progress
              <span class="status-arrow">→</span>
            </div>
            <div class="status-step">
              <i class="bi bi-check-circle"></i><br>Completed
              <span class="status-arrow">→</span>
            </div>
            <div class="status-step">
              <i class="bi bi-patch-check"></i><br>Verified
            </div>
          </div>
          <small class="text-muted">
            * Once Completed, the job will be sent to supervisor for verification.<br>
            * Rejected jobs can be resubmitted after fixing issues.
          </small>
        </div>

        <?= $message ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label fw-bold">
              <i class="bi bi-list-ol"></i> Select Job Card:
            </label>
            <select name="job_id" class="form-select" id="jobSelect" required onchange="this.form.submit()">
              <option value="">-- Select Job Card --</option>
              <?php while ($job = $jobs_result->fetch_assoc()): ?>
                <option value="<?= $job['id'] ?>" 
                        <?= ($preselect_job_id == $job['id']) ? 'selected' : '' ?>>
                  #<?= $job['id'] ?> - <?= htmlspecialchars($job['vehicle_type']) ?> 
                  (<?= htmlspecialchars($job['job_description']) ?>) 
                  - Current: <?= $job['status'] ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <?php if ($job_data): ?>
            <div class="card mb-3" style="background: #f8f9fa;">
              <div class="card-body">
                <h5 class="card-title">Job Card Details</h5>
                <div class="row">
                  <div class="col-md-6">
                    <p><strong>Vehicle:</strong> <?= htmlspecialchars($job_data['vehicle_type']) ?></p>
                    <p><strong>Job:</strong> <?= htmlspecialchars($job_data['job_description']) ?></p>
                  </div>
                  <div class="col-md-6">
                    <p><strong>Delivery Date:</strong> <?= date('d M Y', strtotime($job_data['delivery_date'])) ?></p>
                    <p><strong>Current Status:</strong> 
                      <span class="badge 
                        <?php 
                          echo match($job_data['status']) {
                            'Pending' => 'bg-secondary',
                            'In Progress' => 'bg-warning text-dark',
                            'Completed' => 'bg-success',
                            'Rejected' => 'bg-danger',
                            default => 'bg-info'
                          };
                        ?>">
                        <?= $job_data['status'] ?>
                      </span>
                    </p>
                  </div>
                </div>
                <?php if ($job_data['supervisor_comments']): ?>
                  <div class="alert alert-warning mt-2">
                    <strong><i class="bi bi-chat-left-text"></i> Supervisor Feedback:</strong><br>
                    <?= htmlspecialchars($job_data['supervisor_comments']) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">
                <i class="bi bi-arrow-right-circle"></i> Update Status To:
              </label>
              <select name="status" class="form-select" required>
                <option value="">-- Select New Status --</option>
                <?php
                  $current = $job_data['status'];
                  if ($current === 'Pending') {
                    echo '<option value="In Progress">In Progress</option>';
                  } elseif ($current === 'In Progress') {
                    echo '<option value="Completed">Completed</option>';
                  } elseif ($current === 'Rejected') {
                    echo '<option value="In Progress">In Progress (Resubmit)</option>';
                  }
                ?>
              </select>
              <small class="text-muted">
                Available transitions based on current status: <strong><?= $current ?></strong>
              </small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">
                <i class="bi bi-journal-text"></i> Add Notes (Optional):
              </label>
              <textarea name="mechanic_notes" class="form-control" rows="3" 
                        placeholder="Add any notes about the work done, parts replaced, or issues encountered..."></textarea>
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save"></i> Update Status
              </button>
            </div>
          <?php endif; ?>
        </form>

        <div class="text-center mt-3">
          <a href="myjobcards.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to My Jobs
          </a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>