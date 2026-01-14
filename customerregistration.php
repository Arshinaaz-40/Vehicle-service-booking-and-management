<?php
session_start();

// Database connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create customers table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    address TEXT,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$message = "";
$error = "";

// Handle registration submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $pass = trim($_POST['password']);
    $confirm_pass = trim($_POST['confirm_password']);

    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($pass) || empty($confirm_pass)) {
        $error = "All fields are required!";
    } elseif ($pass !== $confirm_pass) {
        $error = "Passwords do not match!";
    } elseif (strlen($pass) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // Hash password and insert
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $address, $hashed_password);

            if ($stmt->execute()) {
                $message = "Registration successful! Redirecting to login...";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'customerlogin.php';
                    }, 2000);
                </script>";
            } else {
                $error = "Registration failed! Please try again.";
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Registration - Vehicle Service Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: rgba(0, 114, 255, 0.9);
      --secondary: rgba(0, 198, 255, 0.9);
      --dark: #142850;
      --light: #f1f6f9;
    }

    body {
      background: linear-gradient(135deg, var(--secondary), var(--primary));
      font-family: 'Poppins', sans-serif;
      color: #333;
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding: 40px 20px;
    }

    .registration-container {
      max-width: 550px;
      margin: 0 auto;
      width: 100%;
    }

    .registration-card {
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      border: none;
      overflow: hidden;
      margin: 20px 0;
    }

    .registration-header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      padding: 25px;
      text-align: center;
    }

    .registration-icon {
      width: 80px;
      height: 80px;
      background: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .registration-icon i {
      font-size: 40px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .registration-body {
      padding: 30px;
      background: white;
    }

    .form-label {
      font-weight: 600;
      color: #444;
      margin-bottom: 8px;
      font-size: 0.9rem;
    }

    .form-control, .form-select {
      border-radius: 10px;
      padding: 12px 15px;
      border: 2px solid #e1e5eb;
      transition: all 0.3s;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.25rem rgba(0, 114, 255, 0.25);
    }

    .input-group-text {
      background: white;
      border: 2px solid #e1e5eb;
      border-right: none;
      border-radius: 10px 0 0 10px;
    }

    .input-group .form-control {
      border-left: none;
    }

    .btn-register {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: #fff;
      font-weight: bold;
      border-radius: 30px;
      padding: 12px 30px;
      transition: 0.3s;
      border: none;
      width: 100%;
      margin-top: 20px;
    }

    .btn-register:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(0, 114, 255, 0.2);
    }

    .registration-footer {
      text-align: center;
      margin-top: 20px;
      font-size: 0.9rem;
      color: #6c757d;
    }

    .registration-footer a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }

    .registration-footer a:hover {
      text-decoration: underline;
    }

    .alert {
      border-radius: 10px;
    }

    .back-home {
      position: absolute;
      top: 20px;
      left: 20px;
      color: white;
      font-size: 1.2rem;
      transition: all 0.3s;
    }

    .back-home:hover {
      transform: translateX(-5px);
    }

    .password-strength {
      height: 5px;
      margin-top: 5px;
      border-radius: 3px;
      transition: all 0.3s;
    }

    .strength-weak { background: #dc3545; width: 33%; }
    .strength-medium { background: #ffc107; width: 66%; }
    .strength-strong { background: #28a745; width: 100%; }

    @media (max-width: 576px) {
      .registration-container {
        padding: 0 10px;
      }
      
      body {
        padding: 20px 10px;
      }
    }
  </style>
</head>
<body>
  <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i></a>
  
  <div class="registration-container">
    <div class="registration-card">
      <div class="registration-header">
        <div class="registration-icon">
          <i class="fas fa-user-plus"></i>
        </div>
        <h2>Create Account</h2>
        <p class="mb-0">Join us for premium vehicle service</p>
      </div>
      <div class="registration-body">
        <?php if (!empty($message)): ?>
          <div class="alert alert-success text-center"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="registrationForm">
          <div class="mb-3">
            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-user"></i></span>
              <input type="text" class="form-control" name="name" id="name" placeholder="Enter your full name" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-envelope"></i></span>
              <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-phone"></i></span>
              <input type="tel" class="form-control" name="phone" id="phone" placeholder="Enter your phone number" maxlength="15" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
              <textarea class="form-control" name="address" id="address" rows="2" placeholder="Enter your address"></textarea>
            </div>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-lock"></i></span>
              <input type="password" class="form-control" name="password" id="password" placeholder="Create a password" required>
              <span class="input-group-text" style="cursor: pointer; border-left: none;" id="togglePassword">
                <i class="fas fa-eye"></i>
              </span>
            </div>
            <div class="password-strength" id="passwordStrength"></div>
            <small class="text-muted">Password must be at least 6 characters</small>
          </div>

          <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-lock"></i></span>
              <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
              <span class="input-group-text" style="cursor: pointer; border-left: none;" id="toggleConfirmPassword">
                <i class="fas fa-eye"></i>
              </span>
            </div>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="terms" required>
            <label class="form-check-label" for="terms">
              I agree to the <a href="#" target="_blank">Terms & Conditions</a>
            </label>
          </div>

          <button type="submit" class="btn-register">Register <i class="fas fa-user-check"></i></button>
        </form>

        <div class="registration-footer">
          <p>Already have an account? <a href="customerlogin.php">Login Here</a></p>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const icon = this.querySelector('i');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
      const confirmPasswordInput = document.getElementById('confirm_password');
      const icon = this.querySelector('i');
      if (confirmPasswordInput.type === 'password') {
        confirmPasswordInput.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        confirmPasswordInput.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    });

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
      const password = this.value;
      const strengthBar = document.getElementById('passwordStrength');
      
      if (password.length === 0) {
        strengthBar.className = 'password-strength';
      } else if (password.length < 6) {
        strengthBar.className = 'password-strength strength-weak';
      } else if (password.length < 10) {
        strengthBar.className = 'password-strength strength-medium';
      } else {
        strengthBar.className = 'password-strength strength-strong';
      }
    });

    // Form validation
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
      }
    });
  </script>
</body>
</html>