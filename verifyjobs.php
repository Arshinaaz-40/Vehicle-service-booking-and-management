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

// Create manager_notifications table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS manager_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jobcard_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(is_read),
    INDEX(created_at)
)");

$supervisor_id = $_SESSION['supervisor_id'];
$supervisor_name = $_SESSION['supervisor_name'];
$message = "";

// ✅ Handle Job Verification
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    $jobcard_id = intval($_POST['jobcard_id']);
    $comments = trim($_POST['comments'] ?? '');
    
    if ($action === 'verify') {
        // Verify the job
        $stmt = $conn->prepare("UPDATE jobcards SET status='Verified', verified_by=?, verified_at=NOW(), supervisor_comments=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param("ssi", $supervisor_name, $comments, $jobcard_id);
        
        if ($stmt->execute()) {
            // Get jobcard details
            $job_query = "SELECT vehicle_type, other_vehicle_name, job_description, mechanic_name, delivery_date FROM jobcards WHERE id=?";
            $job_stmt = $conn->prepare($job_query);
            $job_stmt->bind_param("i", $jobcard_id);
            $job_stmt->execute();
            $job_result = $job_stmt->get_result();
            $job = $job_result->fetch_assoc();
            $mechanic_name = $job['mechanic_name'];
            $vehicle_info = $job['vehicle_type'] . ($job['other_vehicle_name'] ? ' - ' . $job['other_vehicle_name'] : '');
            
            // Log supervisor action
            $log = $conn->prepare("INSERT INTO supervisor_actions (supervisor_id, supervisor_name, action_type, jobcard_id, mechanic_name, previous_status, new_status, comments) VALUES (?, ?, 'verify', ?, ?, 'Completed', 'Verified', ?)");
            $log->bind_param("isiss", $supervisor_id, $supervisor_name, $jobcard_id, $mechanic_name, $comments);
            $log->execute();
            
            // ✅ NOTIFY MANAGER - Job is ready for invoicing
            $manager_msg = "Job Card #$jobcard_id has been VERIFIED by Supervisor $supervisor_name and is ready for invoice generation. Vehicle: $vehicle_info, Mechanic: $mechanic_name. " . ($comments ? "Comments: $comments" : "");
            $notify_manager = $conn->prepare("INSERT INTO manager_notifications (jobcard_id, message) VALUES (?, ?)");
            $notify_manager->bind_param("is", $jobcard_id, $manager_msg);
            $notify_manager->execute();
            
            // Notify mechanic
            $notification_msg = "Your job card #$jobcard_id has been VERIFIED by Supervisor $supervisor_name. " . ($comments ? "Comment: $comments" : "");
            $notify = $conn->prepare("INSERT INTO notifications (user_type, user_name, message, jobcard_id) VALUES ('mechanic', ?, ?, ?)");
            $notify->bind_param("ssi", $mechanic_name, $notification_msg, $jobcard_id);
            $notify->execute();
            
            $message = "<div class='alert alert-success'>✅ Job Card #$jobcard_id verified successfully! Manager has been notified for invoice generation.</div>";
        }
        $stmt->close();
        
    } elseif ($action === 'reject') {
        // Reject and send back to mechanic
        $stmt = $conn->prepare("UPDATE jobcards SET status='Rejected', supervisor_comments=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param("si", $comments, $jobcard_id);
        
        if ($stmt->execute()) {
            // Get mechanic name
            $job = $conn->query("SELECT mechanic_name FROM jobcards WHERE id=$jobcard_id")->fetch_assoc();
            $mechanic_name = $job['mechanic_name'];
            
            // Log action
            $log = $conn->prepare("INSERT INTO supervisor_actions (supervisor_id, supervisor_name, action_type, jobcard_id, mechanic_name, previous_status, new_status, comments) VALUES (?, ?, 'reject', ?, ?, 'Completed', 'Rejected', ?)");
            $log->bind_param("isiss", $supervisor_id, $supervisor_name, $jobcard_id, $mechanic_name, $comments);
            $log->execute();
            
            // Notify mechanic
            $notification_msg = "Job card #$jobcard_id was REJECTED by Supervisor $supervisor_name. Reason: $comments. Please review and resubmit.";
            $notify = $conn->prepare("INSERT INTO notifications (user_type, user_name, message, jobcard_id) VALUES ('mechanic', ?, ?, ?)");
            $notify->bind_param("ssi", $mechanic_name, $notification_msg, $jobcard_id);
            $notify->execute();
            
            $message = "<div class='alert alert-warning'>⚠️ Job Card #$jobcard_id rejected and sent back to mechanic.</div>";
        }
        $stmt->close();
    }
}

// ✅ Fetch completed jobs awaiting verification
$sql = "SELECT j.id, j.vehicle_type, j.other_vehicle_name, j.job_description, j.mechanic_name, 
        j.delivery_date, j.remarks, j.status, j.created_at, j.updated_at
        FROM jobcards j 
        WHERE j.status='Completed'
        ORDER BY j.updated_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Job Cards</title>
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
      transition: transform 0.2s;
    }
    .job-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .job-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #e9ecef;
      padding-bottom: 15px;
      margin-bottom: 15px;
    }
    .job-id {
      font-size: 1.5rem;
      font-weight: bold;
      color: #0d6efd;
    }
    .status-badge {
      font-size: 1rem;
      padding: 8px 15px;
    }
    .detail-row {
      display: flex;
      margin-bottom: 10px;
    }
    .detail-label {
      font-weight: 600;
      color: #495057;
      width: 150px;
    }
    .detail-value {
      color: #212529;
    }
    .action-buttons {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }
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
      <h2 class="fw-bold text-success">
        <i class="bi bi-check-circle"></i> Verify Completed Jobs
      </h2>
      <a href="supervisordashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <?= $message ?>

    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($job = $result->fetch_assoc()): ?>
        <div class="job-card">
          <div class="job-header">
            <div>
              <span class="job-id">Job #<?= $job['id'] ?></span>
              <div class="text-muted" style="font-size: 0.9rem;">
                <i class="bi bi-clock"></i> Submitted: <?= date('d M Y, h:i A', strtotime($job['updated_at'])) ?>
              </div>
            </div>
            <span class="badge bg-warning text-dark status-badge">
              <i class="bi bi-hourglass-split"></i> Awaiting Verification
            </span>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-car-front"></i> Vehicle Type:</span>
                <span class="detail-value"><?= htmlspecialchars($job['vehicle_type']) ?></span>
              </div>
              <?php if (!empty($job['other_vehicle_name'])): ?>
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-info-circle"></i> Vehicle Name:</span>
                <span class="detail-value"><?= htmlspecialchars($job['other_vehicle_name']) ?></span>
              </div>
              <?php endif; ?>
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-clipboard-check"></i> Job Description:</span>
                <span class="detail-value"><?= htmlspecialchars($job['job_description']) ?></span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-person-gear"></i> Mechanic:</span>
                <span class="detail-value"><?= htmlspecialchars($job['mechanic_name']) ?></span>
              </div>
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-calendar-event"></i> Delivery Date:</span>
                <span class="detail-value"><?= date('d M Y', strtotime($job['delivery_date'])) ?></span>
              </div>
              <div class="detail-row">
                <span class="detail-label"><i class="bi bi-calendar-plus"></i> Created:</span>
                <span class="detail-value"><?= date('d M Y', strtotime($job['created_at'])) ?></span>
              </div>
            </div>
          </div>

          <?php if (!empty($job['remarks'])): ?>
          <div class="mt-3">
            <strong><i class="bi bi-chat-left-text"></i> Remarks:</strong>
            <p class="mb-0 ms-3"><?= nl2br(htmlspecialchars($job['remarks'])) ?></p>
          </div>
          <?php endif; ?>

          <hr>
          
          <form method="POST" class="mt-3">
            <input type="hidden" name="jobcard_id" value="<?= $job['id'] ?>">
            
            <div class="mb-3">
              <label class="form-label fw-bold">
                <i class="bi bi-pencil-square"></i> Supervisor Comments / Feedback:
              </label>
              <textarea name="comments" class="form-control" rows="3" 
                        placeholder="Add your feedback or comments about this job..."></textarea>
            </div>

            <div class="action-buttons">
              <button type="submit" name="action" value="verify" 
                      class="btn btn-success"
                      onclick="return confirm('Are you sure you want to VERIFY this job? Manager will be notified for invoice generation.')">
                <i class="bi bi-check-circle-fill"></i> Verify & Approve
              </button>
              <button type="submit" name="action" value="reject" 
                      class="btn btn-danger"
                      onclick="return confirm('Are you sure you want to REJECT this job? It will be sent back to the mechanic.')">
                <i class="bi bi-x-circle-fill"></i> Reject & Return
              </button>
            </div>
          </form>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="alert alert-info text-center">
        <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
        <h4 class="mt-3">No Completed Jobs Awaiting Verification</h4>
        <p>All completed jobs have been verified or there are no completed jobs yet.</p>
        <a href="trackprogress.php" class="btn btn-primary mt-3">
          <i class="bi bi-bar-chart"></i> View All Jobs Progress
        </a>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>