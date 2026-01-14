<?php
session_start();

// 🔒 Check if Manager is logged in
if (!isset($_SESSION['manager_loggedin']) || $_SESSION['manager_loggedin'] !== true) {
    header("Location: managerlogin.php");
    exit();
}

// --- Database Connection ---
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Logged-in Manager Info ---
$email = $_SESSION['manager_email']; // stored during login
$userCity = "";

// Get office_branch (location) of logged-in manager
$userQuery = "SELECT office_branch FROM manager WHERE email = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($userCity);
$stmt->fetch();
$stmt->close();

// --- Handle Search Filters ---
$whereClauses = [];
$params = [];
$types = "";

// Vehicle type
if (!empty($_GET['vehicleType']) && $_GET['vehicleType'] != "all") {
  $whereClauses[] = "LOWER(vehicle) = ?";
  $params[] = strtolower($_GET['vehicleType']);
  $types .= "s";
}

// Vehicle number
if (!empty($_GET['vehicleNumber'])) {
  $whereClauses[] = "LOWER(number) LIKE ?";
  $params[] = "%" . strtolower($_GET['vehicleNumber']) . "%";
  $types .= "s";
}

// Chassis number
if (!empty($_GET['chassisNumber'])) {
  $whereClauses[] = "LOWER(chassis) LIKE ?";
  $params[] = "%" . strtolower($_GET['chassisNumber']) . "%";
  $types .= "s";
}

// Filter by city (manager's branch)
if (!empty($userCity)) {
  $whereClauses[] = "LOWER(city) = ?";
  $params[] = strtolower($userCity);
  $types .= "s";
}

$sql = "SELECT * FROM booking";
if (count($whereClauses) > 0) {
  $sql .= " WHERE " . implode(" AND ", $whereClauses);
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Vehicle Bookings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .container { margin-top: 40px; }
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .card-header {
      background-color: #0d6efd;
      color: white;
      font-size: 1.25rem;
      font-weight: 600;
    }
    @media print {
      .no-print { display: none; }
      body { background: white; }
      .card { box-shadow: none; }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="card">
    <div class="card-header text-center">
      Vehicle Search & Details
    </div>
    <div class="card-body">

      <!-- 🔙 Back Button -->
      <div class="text-end mb-3 no-print">
        <a href="managerdashboard.php" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
      </div>

      <!-- 🔍 Search Filters -->
      <form method="GET" class="no-print">
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <label for="vehicleType" class="form-label fw-bold">Vehicle Type</label>
            <select class="form-select" id="vehicleType" name="vehicleType">
              <option value="all">All</option>
              <option value="scooter" <?= (isset($_GET['vehicleType']) && $_GET['vehicleType']=='scooter')?'selected':''; ?>>Scooter</option>
              <option value="bike" <?= (isset($_GET['vehicleType']) && $_GET['vehicleType']=='bike')?'selected':''; ?>>Bike</option>
              <option value="car" <?= (isset($_GET['vehicleType']) && $_GET['vehicleType']=='car')?'selected':''; ?>>Car</option>
            </select>
          </div>
          <div class="col-md-3">
            <label for="vehicleNumber" class="form-label fw-bold">Vehicle Number</label>
            <input type="text" id="vehicleNumber" name="vehicleNumber" class="form-control"
                   value="<?= isset($_GET['vehicleNumber']) ? htmlspecialchars($_GET['vehicleNumber']) : '' ?>"
                   placeholder="e.g. AP09 AB 1234">
          </div>
          <div class="col-md-3">
            <label for="chassisNumber" class="form-label fw-bold">Chassis Number</label>
            <input type="text" id="chassisNumber" name="chassisNumber" class="form-control"
                   value="<?= isset($_GET['chassisNumber']) ? htmlspecialchars($_GET['chassisNumber']) : '' ?>"
                   placeholder="e.g. CH12345678">
          </div>
          <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary w-50">Search</button>
            <a href="viewbookings.php" class="btn btn-secondary w-50">Show All</a>
          </div>
        </div>
      </form>

      <!-- Display City -->
      <div class="alert alert-info text-center fw-semibold">
        Showing results for: <span class="text-primary"><?= htmlspecialchars($userCity ?: "No branch location found") ?></span>
      </div>

      <!-- Vehicle Table -->
      <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-primary">
          <tr>
            <th>ID</th>
            <th>Vehicle</th>
            <th>Company</th>
            <th>Number</th>
            <th>Chassis</th>
            <th>Phone</th>
            <th>Email</th>
            <th>City</th>
            <th>Date</th>
            <th>Created At</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['vehicle']) ?></td>
                <td><?= htmlspecialchars($row['company']) ?></td>
                <td><?= htmlspecialchars($row['number']) ?></td>
                <td><?= htmlspecialchars($row['chassis']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['city']) ?></td>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" class="text-danger fw-bold">No records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Print Button -->
      <div class="text-center mt-3 no-print">
        <button class="btn btn-success" onclick="window.print()">
          <i class="bi bi-printer"></i> Print Details
        </button>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
