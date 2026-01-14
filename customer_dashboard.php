<?php
// customer_dashboard.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];
$success = $error = '';

// Handle booking creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_booking'])) {
    $vehicle = trim($_POST['vehicle'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $number  = trim($_POST['number'] ?? '');
    $chassis = trim($_POST['chassis'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $date    = trim($_POST['date'] ?? '');

    if (!$vehicle || !$company || !$number || !$chassis || !$phone || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$city || !$date) {
        $error = "All fields are required and email should be valid.";
    } else {
        // Insert into booking (your table name is booking)
        $stmt = $conn->prepare("INSERT INTO booking (vehicle, company, number, chassis, phone, email, city, date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssssss", $vehicle, $company, $number, $chassis, $phone, $email, $city, $date);
        if ($stmt->execute()) {
            $success = "Booking created successfully!";
        } else {
            $error = "DB error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch bookings for this customer's email (we use email to link bookings)
$bookings = [];
$st = $conn->prepare("SELECT id, vehicle, company, number, chassis, phone, email, city, date, created_at FROM booking WHERE email = ? ORDER BY id DESC");
$st->bind_param("s", $_SESSION['customer_name']); // WAIT - we don't have customer email in session; better to fetch by email stored with booking form
// We should use customer email; if you want to link by customers table, ensure booking stores customer_id. Simpler: fetch bookings by email passed in booking creation.
// We'll instead fetch bookings by the email the user uses below — but we don't persist it. Safer approach: show all bookings matching customer's email in bookings created by them (we can ask them to enter same email).
// To avoid confusion, we'll query by session-stored email if available, otherwise show bookings where phone matches, or show all bookings and filter on client side — but simplest: fetch bookings where email = the email the customer used last time.
// Let's attempt to fetch by customer's email if it's stored in customers table:
$emm = '';
$qe = $conn->prepare("SELECT email FROM customers WHERE id = ?");
$qe->bind_param("i", $customer_id);
$qe->execute();
$qe->bind_result($emm);
$qe->fetch();
$qe->close();

if ($emm) {
    $stmt2 = $conn->prepare("SELECT id, vehicle, company, number, chassis, phone, email, city, date, created_at FROM booking WHERE email = ? ORDER BY id DESC");
    $stmt2->bind_param("s", $emm);
    $stmt2->execute();
    $res = $stmt2->get_result();
    while ($r = $res->fetch_assoc()) $bookings[] = $r;
    $stmt2->close();
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Customer Dashboard — AutoCare</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#f4f7fb}</style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">AutoCare</a>
      <div class="">
        <span class="navbar-text text-white me-3">Hello, <?= htmlspecialchars($customer_name) ?></span>
        <a href="customer_logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container py-4">
    <div class="row">
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm p-3">
          <h5>Create Booking</h5>

          <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
          <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

          <form method="post">
            <input type="hidden" name="create_booking" value="1">
            <div class="mb-2">
              <label class="form-label">Vehicle Type</label>
              <select name="vehicle" class="form-select" required>
                <option value="">Select</option>
                <option value="Scooter">Scooter</option>
                <option value="Bike">Motorcycle</option>
                <option value="Car">Car</option>
                <option value="SUV">SUV</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="mb-2">
              <label class="form-label">Company</label>
              <input name="company" class="form-control" required>
            </div>

            <div class="mb-2">
              <label class="form-label">Vehicle Number</label>
              <input name="number" class="form-control" required>
            </div>

            <div class="mb-2">
              <label class="form-label">Chassis (last 6 digits)</label>
              <input name="chassis" maxlength="6" class="form-control" required>
            </div>

            <div class="mb-2">
              <label class="form-label">Phone</label>
              <input name="phone" class="form-control" value="" required>
            </div>

            <div class="mb-2">
              <label class="form-label">Email</label>
              <input name="email" class="form-control" value="<?= htmlspecialchars($emm) ?>" required>
            </div>

            <div class="mb-2">
              <label class="form-label">City</label>
              <input name="city" class="form-control" required>
            </div>

            <div class="mb-2">
              <label class="form-label">Preferred Date</label>
              <input name="date" type="date" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">Confirm Booking</button>
          </form>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card shadow-sm p-3">
          <h5>Your Bookings</h5>
          <?php if (empty($bookings)): ?>
            <div class="text-muted">No bookings found for your email (<?= htmlspecialchars($emm) ?>).</div>
          <?php else: ?>
            <div class="list-group">
              <?php foreach ($bookings as $b): ?>
                <div class="list-group-item">
                  <div class="d-flex justify-content-between">
                    <div>
                      <strong>#<?= $b['id'] ?></strong> — <?= htmlspecialchars($b['vehicle']) ?> (<?= htmlspecialchars($b['company']) ?>)<br>
                      <small class="text-muted"><?= htmlspecialchars($b['number']) ?> • <?= htmlspecialchars($b['city']) ?> • <?= htmlspecialchars($b['date']) ?></small>
                    </div>
                    <div class="text-end">
                      <small class="text-muted"><?= date('d M Y', strtotime($b['created_at'])) ?></small><br>
                      <span class="badge bg-info">Pending</span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</body>
</html>
