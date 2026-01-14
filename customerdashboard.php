<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// ✅ Check login
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header("Location: customerlogin.php");
    exit();
}

// ✅ Session timeout 30 mins
$timeout_duration = 1800;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: customerlogin.php?timeout=true");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// ✅ Database connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ✅ Session Data
$customer_id = $_SESSION['customer_id'] ?? 0;
$customer_name = $_SESSION['customer_name'] ?? 'Guest';
$customer_email = $_SESSION['customer_email'] ?? '';

if (empty($customer_email)) {
    die("Error: Session expired. Please login again.");
}

// ✅ Ensure required columns exist
$conn->query("CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jobcard_id INT,
    customer_email VARCHAR(255),
    labour_cost DECIMAL(10,2) DEFAULT 0,
    service_cost DECIMAL(10,2) DEFAULT 0,
    parts_cost DECIMAL(10,2) DEFAULT 0,
    other_charges DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(customer_email)
)");

$conn->query("CREATE TABLE IF NOT EXISTS booking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    vehicle VARCHAR(255),
    company VARCHAR(255),
    number VARCHAR(50),
    date DATE,
    city VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// ✅ Ensure created_at columns exist
foreach (['invoices', 'booking'] as $table) {
    $col_check = $conn->query("SHOW COLUMNS FROM $table LIKE 'created_at'");
    if ($col_check->num_rows == 0) {
        $conn->query("ALTER TABLE $table ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }
}

// ✅ Get statistics
$stats = [
    'total_bookings' => 0,
    'completed_services' => 0,
    'pending_services' => 0
];

// Total Bookings
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM booking WHERE email=?");
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_bookings'] = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Completed Services (invoices)
$stmt = $conn->prepare("SELECT COUNT(*) as completed FROM invoices WHERE customer_email=?");
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$result = $stmt->get_result();
$stats['completed_services'] = $result->fetch_assoc()['completed'] ?? 0;
$stmt->close();

// Pending = Total - Completed
$stats['pending_services'] = max(0, $stats['total_bookings'] - $stats['completed_services']);

// ✅ Recent Bookings (last 5)
$stmt = $conn->prepare("SELECT * FROM booking WHERE email=? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$recent_bookings = $stmt->get_result();
$stmt->close();

// ✅ Recent Invoices (last 3)
$stmt = $conn->prepare("SELECT * FROM invoices WHERE customer_email=? ORDER BY created_at DESC LIMIT 3");
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$recent_invoices = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Customer Dashboard - AutoCare</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
body{font-family:'Poppins',sans-serif;background:#f8f9fa;}
.navbar{background:linear-gradient(135deg,#0072ff,#00c6ff);}
.navbar-brand{font-weight:700;color:#fff!important;font-size:1.5rem;}
.welcome-section{background:linear-gradient(135deg,#0072ff,#00c6ff);color:white;padding:40px 0;margin-bottom:30px;border-radius:0 0 30px 30px;}
.stats-card{background:white;border-radius:15px;padding:25px;text-align:center;
box-shadow:0 4px 15px rgba(0,0,0,0.08);margin-bottom:20px;border-left:5px solid;}
.stats-card.blue{border-left-color:#0072ff}.stats-card.green{border-left-color:#28a745}.stats-card.orange{border-left-color:#fd7e14}
.stats-number{font-size:2.5rem;font-weight:700;margin:10px 0;}
.action-card{background:white;border-radius:15px;padding:30px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.08);transition:.3s;height:100%;}
.action-card:hover{transform:translateY(-8px);box-shadow:0 10px 30px rgba(0,114,255,0.2);}
.btn-action{background:linear-gradient(135deg,#0072ff,#00c6ff);color:#fff;border:none;padding:10px 30px;border-radius:25px;font-weight:600;}
.recent-bookings,.recent-invoices{background:white;border-radius:15px;padding:30px;box-shadow:0 4px 15px rgba(0,0,0,0.08);margin-top:30px;}
.booking-item,.invoice-item{padding:15px;background:#f8f9fa;border-radius:10px;margin-bottom:15px;border-left:4px solid #0072ff;}
.empty-state{text-align:center;padding:40px;color:#6c757d;}
.empty-state i{font-size:4rem;margin-bottom:20px;opacity:0.3;}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
<div class="container">
<a class="navbar-brand" href="#"><i class="bi bi-car-front"></i> AutoCare</a>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav ms-auto">
<li class="nav-item"><a class="nav-link text-white active" href="customerdashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
<li class="nav-item"><a class="nav-link text-white" href="customerbookings.php"><i class="bi bi-list-check"></i> My Bookings</a></li>
<li class="nav-item"><a class="nav-link text-white" href="customerinvoices.php"><i class="bi bi-receipt"></i> Invoices</a></li>
<li class="nav-item"><a class="nav-link text-white" href="customerprofile.php"><i class="bi bi-person-gear"></i> Profile</a></li>
<li class="nav-item"><a class="nav-link text-white" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
</ul>
</div>
</div>
</nav>

<div class="welcome-section text-center">
<h2>Welcome back, <?php echo htmlspecialchars($customer_name); ?>!</h2>
<p>Manage your bookings and invoices with ease</p>
</div>

<div class="container pb-5">

<!-- Stats -->
<div class="row text-center mb-4">
<div class="col-md-4"><div class="stats-card blue"><i class="bi bi-calendar-check fs-2"></i><div class="stats-number"><?php echo $stats['total_bookings']; ?></div><div>Total Bookings</div></div></div>
<div class="col-md-4"><div class="stats-card orange"><i class="bi bi-hourglass-split fs-2"></i><div class="stats-number"><?php echo $stats['pending_services']; ?></div><div>Pending</div></div></div>
<div class="col-md-4"><div class="stats-card green"><i class="bi bi-check-circle fs-2"></i><div class="stats-number"><?php echo $stats['completed_services']; ?></div><div>Completed</div></div></div>
</div>

<!-- Quick Actions -->
<div class="row mt-3">
<div class="col-md-3 mb-4"><div class="action-card"><i class="bi bi-plus-circle fs-1"></i><h5>Book Service</h5><a href="booking.php" class="btn btn-action">Book Now</a></div></div>
<div class="col-md-3 mb-4"><div class="action-card"><i class="bi bi-list-check fs-1"></i><h5>My Bookings</h5><a href="customerbookings.php" class="btn btn-action">View</a></div></div>
<div class="col-md-3 mb-4"><div class="action-card"><i class="bi bi-receipt fs-1"></i><h5>Invoices</h5><a href="customerinvoices.php" class="btn btn-action">Check</a></div></div>
<div class="col-md-3 mb-4"><div class="action-card"><i class="bi bi-person-gear fs-1"></i><h5>Profile</h5><a href="customerprofile.php" class="btn btn-action">Edit</a></div></div>
</div>

<!-- Recent Bookings -->
<div class="recent-bookings">
<h4><i class="bi bi-clock-history"></i> Recent Bookings</h4>
<?php if ($recent_bookings->num_rows > 0): ?>
<?php while ($b = $recent_bookings->fetch_assoc()): ?>
<div class="booking-item">
<h6><i class="bi bi-car-front text-primary"></i> <?php echo htmlspecialchars($b['vehicle']); ?> - <?php echo htmlspecialchars($b['company']); ?></h6>
<p><strong>Number:</strong> <?php echo htmlspecialchars($b['number']); ?> | <strong>Date:</strong> <?php echo htmlspecialchars($b['date']); ?></p>
<p><strong>City:</strong> <?php echo htmlspecialchars($b['city']); ?> | <strong>Booked:</strong> <?php echo date('d M Y', strtotime($b['created_at'])); ?></p>
</div>
<?php endwhile; ?>
<a href="customerbookings.php" class="btn btn-outline-primary">View All</a>
<?php else: ?>
<div class="empty-state"><i class="bi bi-calendar-x"></i><h5>No Bookings Yet</h5><a href="booking.php" class="btn btn-action mt-3">Book Service</a></div>
<?php endif; ?>
</div>

<!-- Recent Invoices -->
<div class="recent-invoices">
<h4><i class="bi bi-receipt-cutoff"></i> Recent Invoices</h4>
<?php if ($recent_invoices->num_rows > 0): ?>
<?php while ($i = $recent_invoices->fetch_assoc()): ?>
<div class="invoice-item">
<h6><i class="bi bi-file-text text-success"></i> Invoice #INV-<?php echo $i['id']; ?></h6>
<p><strong>Total:</strong> ₹<?php echo number_format($i['total'], 2); ?> | <strong>Date:</strong> <?php echo date('d M Y', strtotime($i['created_at'])); ?></p>
</div>
<?php endwhile; ?>
<a href="customerinvoices.php" class="btn btn-outline-primary">View All</a>
<?php else: ?>
<div class="empty-state"><i class="bi bi-receipt"></i><h5>No Invoices Yet</h5><p>Invoices will appear once services are completed.</p></div>
<?php endif; ?>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
