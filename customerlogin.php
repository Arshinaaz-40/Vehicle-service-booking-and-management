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

$message = "";

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    if (!empty($email) && !empty($pass)) {
        $stmt = $conn->prepare("SELECT id, name, email, password, phone FROM customers WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $email, $hashed_password, $phone);
            $stmt->fetch();

            if (password_verify($pass, $hashed_password)) {
                // Successful login
                $_SESSION['customer_loggedin'] = true;
                $_SESSION['customer_id'] = $id;
                $_SESSION['customer_name'] = $name;
                $_SESSION['customer_email'] = $email;
                $_SESSION['customer_phone'] = $phone;
                $_SESSION['LAST_ACTIVITY'] = time();

                header("Location: customerdashboard.php");
                exit();
            } else {
                $message = "Invalid password!";
            }
        } else {
            $message = "No account found with this email!";
        }
        $stmt->close();
    } else {
        $message = "Please enter email and password!";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Login - Vehicle Service Tracker</title>
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
      padding: 20px;
    }

    .login-container {
      max-width: 450px;
      margin: 0 auto;
      width: 100%;
    }

    .login-card {
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      border: none;
      overflow: hidden;
    }

    .login-header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      border-radius: 20px 20px 0 0 !important;
      padding: 25px;
      text-align: center;
      position: relative;
    }

    .login-icon {
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

    .login-icon i {
      font-size: 40px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .login-body {
      padding: 30px;
      background: white;
    }

    .form-label {
      font-weight: 600;
      color: #444;
      margin-bottom: 8px;
    }

    .form-control {
      border-radius: 10px;
      padding: 12px 15px;
      border: 2px solid #e1e5eb;
      transition: all 0.3s;
    }

    .form-control:focus {
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

    .btn-login {
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

    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(0, 114, 255, 0.2);
    }

    .login-footer {
      text-align: center;
      margin-top: 20px;
      font-size: 0.9rem;
      color: #6c757d;
    }

    .login-footer a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }

    .login-footer a:hover {
      text-decoration: underline;
    }

    .alert {
      border-radius: 10px;
    }

    .divider {
      text-align: center;
      margin: 20px 0;
      position: relative;
    }

    .divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      width: 45%;
      height: 1px;
      background: #ddd;
    }

    .divider::after {
      content: '';
      position: absolute;
      top: 50%;
      right: 0;
      width: 45%;
      height: 1px;
      background: #ddd;
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

    @media (max-width: 576px) {
      .login-container {
        padding: 0 10px;
      }
    }
  </style>
</head>
<body>
  <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i></a>
  
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-icon">
          <i class="fas fa-user-circle"></i>
        </div>
        <h2>Welcome Back!</h2>
        <p class="mb-0">Login to book your vehicle service</p>
      </div>
      <div class="login-body">
        <?php if (!empty($message)): ?>
          <div class="alert alert-danger text-center"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-envelope"></i></span>
              <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-key"></i></span>
              <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
              <span class="input-group-text" style="cursor: pointer; border-left: none;" id="togglePassword">
                <i class="fas fa-eye"></i>
              </span>
            </div>
          </div>

          <button type="submit" class="btn-login">Login <i class="fas fa-sign-in-alt"></i></button>
        </form>

        <div class="divider">OR</div>

        <div class="login-footer">
          <p>Don't have an account? <a href="customerregistration.php">Register Now</a></p>
          <p><a href="#">Forgot your password?</a></p>
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
  </script>
</body>
</html>