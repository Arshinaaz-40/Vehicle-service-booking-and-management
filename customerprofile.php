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

$customer_id = $_SESSION['customer_id'];
$message = "";
$error = "";

// Get current customer data
$stmt = $conn->prepare("SELECT name, email, phone, address FROM customers WHERE id=?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $stmt = $conn->prepare("UPDATE customers SET name=?, phone=?, address=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $phone, $address, $customer_id);
    
    if ($stmt->execute()) {
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_phone'] = $phone;
        $message = "Profile updated successfully!";
        // Refresh customer data
        $customer['name'] = $name;
        $customer['phone'] = $phone;
        $customer['address'] = $address;
    } else {
        $error = "Failed to update profile!";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Get current hashed password
    $stmt = $conn->prepare("SELECT password FROM customers WHERE id=?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pwd_data = $result->fetch_assoc();

    if (!password_verify($current_password, $pwd_data['password'])) {
        $error = "Current password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE customers SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed_password, $customer_id);
        
        if ($stmt->execute()) {
            $message = "Password changed successfully!";
        } else {
            $error = "Failed to change password!";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Settings - AutoCare</title>
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

    .profile-container {
      max-width: 800px;
      margin: 0 auto;
    }

    .profile-card {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      margin-bottom: 30px;
    }

    .profile-card h4 {
      color: #0072ff;
      font-weight: 700;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f1f3f5;
    }

    .form-label {
      font-weight: 600;
      color: #444;
      margin-bottom: 8px;
    }

    .form-control, .form-select {
      border-radius: 10px;
      padding: 12px 15px;
      border: 2px solid #e9ecef;
      transition: all 0.3s;
    }

    .form-control:focus, .form-select:focus {
      border-color: #0072ff;
      box-shadow: 0 0 0 0.25rem rgba(0, 114, 255, 0.25);
    }

    .btn-update {
      background: linear-gradient(135deg, #0072ff, #00c6ff);
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-update:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 114, 255, 0.3);
    }

    .input-group-text {
      background: #f8f9fa;
      border: 2px solid #e9ecef;
      border-right: none;
    }

    .input-group .form-control {
      border-left: none;
    }

    .alert {
      border-radius: 10px;
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
            <a class="nav-link text-white active" href="customerprofile.php">
              <i class="bi bi-person-gear"></i> Profile
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
      <h2><i class="bi bi-gear"></i> Account Settings</h2>
      <p class="mb-0">Manage your profile and security settings</p>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container pb-5">
    <div class="profile-container">
      
      <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle"></i> <?php echo $message; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Profile Information -->
      <div class="profile-card">
        <h4><i class="bi bi-person-circle"></i> Profile Information</h4>
        <form method="POST" action="">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="name" class="form-label">Full Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" name="name" id="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($customer['email']); ?>" disabled>
              </div>
              <small class="text-muted">Email cannot be changed</small>
            </div>

            <div class="col-md-6 mb-3">
              <label for="phone" class="form-label">Phone Number</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                <input type="tel" class="form-control" name="phone" id="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" maxlength="15" required>
              </div>
            </div>

            <div class="col-md-12 mb-3">
              <label for="address" class="form-label">Address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                <textarea class="form-control" name="address" id="address" rows="3"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
              </div>
            </div>
          </div>

          <div class="text-end">
            <button type="submit" name="update_profile" class="btn btn-update">
              <i class="bi bi-save"></i> Update Profile
            </button>
          </div>
        </form>
      </div>

      <!-- Change Password -->
      <div class="profile-card">
        <h4><i class="bi bi-shield-lock"></i> Change Password</h4>
        <form method="POST" action="" id="passwordForm">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="current_password" class="form-label">Current Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" name="current_password" id="current_password" required>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="new_password" class="form-label">New Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-key"></i></span>
                <input type="password" class="form-control" name="new_password" id="new_password" required>
              </div>
              <small class="text-muted">Minimum 6 characters</small>
            </div>

            <div class="col-md-6 mb-3">
              <label for="confirm_password" class="form-label">Confirm New Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-key"></i></span>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
              </div>
            </div>
          </div>

          <div class="text-end">
            <button type="submit" name="change_password" class="btn btn-update">
              <i class="bi bi-shield-check"></i> Change Password
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password confirmation validation
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New passwords do not match!');
      }
    });
  </script>
</body>
</html>