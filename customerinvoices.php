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

// Get all invoices for this customer
$stmt = $conn->prepare("SELECT * FROM invoices WHERE customer_email=? ORDER BY id DESC");
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$invoices = $stmt->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Invoices - AutoCare</title>
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

    .invoices-container {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .invoice-card {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 20px;
      border-left: 5px solid #28a745;
      transition: all 0.3s ease;
    }

    .invoice-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
    }

    .invoice-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid #dee2e6;
    }

    .invoice-number {
      font-weight: 700;
      color: #28a745;
      font-size: 1.3rem;
    }

    .invoice-date {
      color: #6c757d;
      font-size: 0.9rem;
    }

    .invoice-details {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .detail-box {
      background: white;
      padding: 15px;
      border-radius: 8px;
      text-align: center;
    }

    .detail-label {
      font-size: 0.8rem;
      color: #6c757d;
      margin-bottom: 5px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .detail-value {
      font-weight: 700;
      color: #333;
      font-size: 1.1rem;
    }

    .invoice-total {
      background: linear-gradient(135deg, #28a745, #20c997);
      color: white;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      margin-top: 15px;
    }

    .invoice-total-label {
      font-size: 0.9rem;
      margin-bottom: 5px;
      opacity: 0.9;
    }

    .invoice-total-amount {
      font-size: 2rem;
      font-weight: 700;
    }

    .btn-download {
      background: linear-gradient(135deg, #0072ff, #00c6ff);
      color: white;
      border: none;
      padding: 10px 25px;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-download:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(0, 114, 255, 0.3);
    }

    .no-invoices {
      text-align: center;
      padding: 60px 20px;
    }

    .no-invoices i {
      font-size: 4rem;
      color: #dee2e6;
      margin-bottom: 20px;
    }

    @media (max-width: 768px) {
      .invoice-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .invoice-date {
        margin-top: 5px;
      }

      .invoice-details {
        grid-template-columns: repeat(2, 1fr);
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
            <a class="nav-link text-white" href="customerbookings.php">
              <i class="bi bi-list-check"></i> My Bookings
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white active" href="customerinvoices.php">
              <i class="bi bi-receipt"></i> Invoices
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
      <h2><i class="bi bi-receipt-cutoff"></i> My Service Invoices</h2>
      <p class="mb-0">View and download your service invoices</p>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container pb-5">
    <div class="invoices-container">
      <?php if ($invoices && $invoices->num_rows > 0): ?>
        <?php while ($invoice = $invoices->fetch_assoc()): ?>
        <div class="invoice-card">
          <div class="invoice-header">
            <div>
              <div class="invoice-number">
                <i class="bi bi-file-text"></i> Invoice #INV-<?php echo htmlspecialchars($invoice['id']); ?>
              </div>
              <div class="invoice-date">
                <i class="bi bi-calendar3"></i> Generated on <?php echo date('d M Y', strtotime($invoice['created_at'] ?? 'now')); ?>
              </div>
            </div>
           
          </div>

          <div class="invoice-details">
            <div class="detail-box">
              <div class="detail-label">Labour Cost</div>
              <div class="detail-value">₹<?php echo number_format($invoice['labour_cost'], 2); ?></div>
            </div>
            <div class="detail-box">
              <div class="detail-label">Service Cost</div>
              <div class="detail-value">₹<?php echo number_format($invoice['service_cost'], 2); ?></div>
            </div>
            <div class="detail-box">
              <div class="detail-label">Parts Cost</div>
              <div class="detail-value">₹<?php echo number_format($invoice['parts_cost'], 2); ?></div>
            </div>
            <div class="detail-box">
              <div class="detail-label">Other Charges</div>
              <div class="detail-value">₹<?php echo number_format($invoice['other_charges'], 2); ?></div>
            </div>
          </div>

          <div class="invoice-total">
            <div class="invoice-total-label">Total Amount Paid</div>
            <div class="invoice-total-amount">₹<?php echo number_format($invoice['total'], 2); ?></div>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="no-invoices">
          <i class="bi bi-receipt"></i>
          <h4>No Invoices Found</h4>
          <p class="text-muted">You don't have any invoices yet. Complete a service to receive your invoice.</p>
          <a href="booking.php" class="btn btn-primary mt-3">
            <i class="bi bi-plus-circle"></i> Book a Service
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>