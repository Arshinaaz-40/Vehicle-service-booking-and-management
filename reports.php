<?php
session_start();

// ✅ Ensure Admin is logged in
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Enable errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// 📊 Fetch counts
$counts = [
    'managers' => 0,
    'supervisors' => 0,
    'mechanics' => 0,
    'vehicles' => 0,
    'jobcards' => 0,
    'completed_jobs' => 0
];

$tables = [
    'managers' => "manager",
    'supervisors' => "supervisor",
    'mechanics' => "mechanic",
    'vehicles' => "vehicle",
    'jobcards' => "jobcard"
];

foreach ($tables as $key => $table) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM $table");
    if ($result && $row = $result->fetch_assoc()) {
        $counts[$key] = $row['total'];
    }
}

// Completed jobs
$res = $conn->query("SELECT COUNT(*) AS completed FROM jobcard WHERE status='Completed'");
if ($res && $r = $res->fetch_assoc()) {
    $counts['completed_jobs'] = $r['completed'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports - Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .dashboard-header { background: linear-gradient(135deg, #0072ff, #00c6ff); color: white; padding: 30px 0; text-align: center; margin-bottom: 30px; }
    .card { border: none; border-radius: 15px; transition: 0.3s; }
    .card:hover { transform: translateY(-5px); box-shadow: 0 6px 18px rgba(0,0,0,0.1); }
    .card i { font-size: 45px; margin-bottom: 10px; }
    .card h5 { font-weight: 600; }
    .btn-back { position: absolute; top: 25px; left: 25px; }
  </style>
</head>
<body>

  <div class="dashboard-header position-relative">
    <a href="admindashboard.php" class="btn btn-light btn-sm btn-back"><i class="bi bi-arrow-left"></i> Back</a>
    <h1 class="fw-bold">Admin Reports</h1>
    <p class="mb-0">System Summary Overview</p>
  </div>

  <div class="container pb-5">
    <div class="row g-4 justify-content-center">

      <div class="col-md-4 col-sm-6">
        <div class="card shadow text-center p-4">
          <i class="bi bi-person-gear text-primary"></i>
          <h5>Managers</h5>
          <h2 class="fw-bold text-primary"><?= $counts['managers'] ?></h2>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="card shadow text-center p-4">
          <i class="bi bi-clipboard-check text-success"></i>
          <h5>Supervisors</h5>
          <h2 class="fw-bold text-success"><?= $counts['supervisors'] ?></h2>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="card shadow text-center p-4">
          <i class="bi bi-tools text-warning"></i>
          <h5>Mechanics</h5>
          <h2 class="fw-bold text-warning"><?= $counts['mechanics'] ?></h2>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="card shadow text-center p-4">
          <i class="bi bi-car-front text-danger"></i>
          <h5>Vehicles Registered</h5>
          <h2 class="fw-bold text-danger"><?= $counts['vehicles'] ?></h2>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="card shadow text-center p-4">
          <i class="bi bi-journal-text text-info"></i>
          <h5>Total Job Cards</h5>
          <h2 class="fw-bold text-info"><?= $counts['jobcards'] ?></h2>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="card shadow text-center p-4">
          <i class="bi bi-check-circle-fill text-success"></i>
          <h5>Completed Services</h5>
          <h2 class="fw-bold text-success"><?= $counts['completed_jobs'] ?></h2>
        </div>
      </div>

    </div>
  </div>

  <footer class="bg-dark text-white text-center py-3 mt-4">
    <p class="mb-0">&copy; 2025 Vehicle Service Management | Admin Reports</p>
  </footer>

</body>
</html>
