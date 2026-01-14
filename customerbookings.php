<?php
session_start();

// Check if customer is logged in
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header("Location: customerlogin.php");
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

$customer_email = $_SESSION['customer_email'];

// Get all bookings for this customer
$stmt = $conn->prepare("SELECT * FROM booking WHERE email=? ORDER BY created_at DESC");
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$bookings = $stmt->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings - AutoCare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
    }

    .navbar {
      background: linear-gradient(135deg, #0072ff, #00c6ff);
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }

    .page-header {
      background: linear-gradient(135deg, #0072ff, #00c6ff);
      color: white;
      padding: 40px 0;
      margin-bottom: 30px;
      border-radius: 0 0 30px 30px;
    }

    .bookings-container {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .booking-card {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      border-left: 5px solid #0072ff;
      transition: all 0.3s ease;
    }

    .booking-card:hover {
      transform: translateX(5px);
      box-shadow: 0 5px 15px rgba(0, 114, 255, 0.2);
    }

    .booking-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .booking-id {
      font-weight: 700;
      color: #0072ff;
      font-size: 1.2rem;
    }

    .badge-status {
      padding: 6px 15px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }

    .booking-details {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
    }

    .detail-item {
      display: flex;
      align-items: center;
    }

    .detail-item i {
      color: #0072ff;
      margin-right: 10px;
      font-size: 1.2rem;
    }

    .detail-label {
      font-size: 0.85rem;
      color: #6c757d;
    }

    .detail-value {
      font-weight: 600;
      color: #333;
    }

    .no-bookings {
      text-align: center;
      padding: 60px 20px;
    }

    .no-bookings i {
      font-size: 4rem;
      color: #dee2e6;
      margin-bottom: 20px;
    }

    @media (max-width: 768px) {
      .booking-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .badge-status {
        margin-top: 10px;
      }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="customerdashboard.php">
        <i class="bi bi-car-front"></i> AutoCare
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link text-white" href="customerdashboard.php">
              <i class="bi bi-house-door"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white active" href="customerbookings.php">
              <i class="bi bi-list-check"></i> My Bookings
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="logout.php">
              <i class="bi bi-box-arrow-right"></i> Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Header -->
  <div class="page-header">
    <div class="container">
      <h2><i class="bi bi-calendar-check"></i> My Service Bookings</h2>
      <p class="mb-0">View and manage all your vehicle service appointments</p>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container pb-5">
    <div class="bookings-container">
      <?php if ($bookings && $bookings->num_rows > 0): ?>
        <?php while ($booking = $bookings->fetch_assoc()): ?>
        <div class="booking-card">
          <div class="booking-header">
            <div class="booking-id">
              <i class="bi bi-tag"></i> Booking #<?php echo htmlspecialchars($booking['id']); ?>
            </div>
            <span class="badge badge-status bg-warning text-dark">Pending</span>
          </div>
          <div class="booking-details">
            <div class="detail-item">
              <i class="bi bi-car-front-fill"></i>
              <div>
                <div class="detail-label">Vehicle</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['vehicle']); ?></div>
              </div>
            </div>
            <div class="detail-item">
              <i class="bi bi-building"></i>
              <div>
                <div class="detail-label">Company</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['company']); ?></div>
              </div>
            </div>
            <div class="detail-item">
              <i class="bi bi-credit-card"></i>
              <div>
                <div class="detail-label">Vehicle Number</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['number']); ?></div>
              </div>
            </div>
            <div class="detail-item">
              <i class="bi bi-hash"></i>
              <div>
                <div class="detail-label">Chassis Number</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['chassis']); ?></div>
              </div>
            </div>
            <div class="detail-item">
              <i class="bi bi-geo-alt"></i>
              <div>
                <div class="detail-label">Service City</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['city']); ?></div>
              </div>
            </div>
            <div class="detail-item">
              <i class="bi bi-calendar-event"></i>
              <div>
                <div class="detail-label">Service Date</div>
                <div class="detail-value"><?php echo date('d M Y', strtotime($booking['date'])); ?></div>
              </div>
            </div>
            <div class="detail-item">
              <i class="bi bi-clock"></i>
              <div>
                <div class="detail-label">Booked On</div>
                <div class="detail-value"><?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?></div>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="no-bookings">
          <i class="bi bi-calendar-x"></i>
          <h4>No Bookings Found</h4>
          <p class="text-muted">You haven't made any service bookings yet.</p>
          <a href="booking.php" class="btn btn-primary mt-3">
            <i class="bi bi-plus-circle"></i> Book Your First Service
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>