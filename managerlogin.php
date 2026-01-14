<?php
session_start();

// --- Replace with real DB check later if needed ---
$valid_email = "manager@example.com";
$valid_password = "12345";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($email === $valid_email && $password === $valid_password) {
        $_SESSION['manager_loggedin'] = true;
        $_SESSION['manager_email'] = $email;
        header("Location: managerdashboard.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manager Login - Vehicle Service Tracker</title>
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
      max-width: 400px;
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

    .alert-danger {
      text-align: center;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-icon">
          <i class="fas fa-user-tie"></i>
        </div>
        <h2>Manager Login</h2>
        <p class="mb-0">Access managerial tools & reports</p>
      </div>
      <div class="login-body">
        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
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
            </div>
          </div>

          <button type="submit" class="btn-login">Login <i class="fas fa-sign-in-alt"></i></button>
        </form>

        <div class="login-footer">
          <p><a href="#">Forgot your password?</a></p>
          <p>Contact admin for account issues</p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
