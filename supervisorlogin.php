<?php
session_start();
$message = "";

// Database connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// When form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST['email']);
  $pwd = trim($_POST['password']);

  if (!empty($email) && !empty($pwd)) {
    $stmt = $conn->prepare("SELECT id, first_name, password, status FROM supervisor WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $stmt->bind_result($id, $first_name, $hashed_password, $status);
      $stmt->fetch();

      if ($status != 'Active') {
        $message = "Your account is inactive. Contact admin.";
      } elseif (password_verify($pwd, $hashed_password)) {
        $_SESSION['supervisor_loggedin'] = true;
        $_SESSION['supervisor_id'] = $id;
        $_SESSION['supervisor_name'] = $first_name;
        header("Location: supervisordashboard.php");
        exit();      
      } else {
        $message = "Invalid password!";
      }
    } else {
      $message = "No account found with that email!";
    }

    $stmt->close();
  } else {
    $message = "Please fill in all fields.";
  }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Supervisor Login - Vehicle Service Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    /* === Your same CSS as before === */
    :root {
      --primary: rgba(0, 114, 255, 0.9);
      --secondary: rgba(0, 198, 255, 0.9);
      --dark: #142850;
      --light: #f1f6f9;
    }
    body {
      background: linear-gradient(135deg, var(--secondary), var(--primary));
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding: 20px;
    }
    .login-container { max-width: 400px; margin: 0 auto; width: 100%; }
    .login-card { border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: none; overflow: hidden; }
    .login-header { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; padding: 25px; text-align: center; }
    .login-icon { width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .login-body { padding: 30px; background: white; }
    .btn-login { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; font-weight: bold; border-radius: 30px; padding: 12px; width: 100%; border: none; }
    .btn-login:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 114, 255, 0.2); }
    .error-message { color: #dc3545; text-align: center; margin-bottom: 10px; }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-icon"><i class="fas fa-user-tie"></i></div>
        <h2>Supervisor Login</h2>
        <p class="mb-0">Access the vehicle service management system</p>
      </div>
      <div class="login-body">
        <?php if (!empty($message)) echo '<div class="alert alert-danger">'.$message.'</div>'; ?>
        <form method="POST" action="">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-envelope"></i></span>
              <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-key"></i></span>
              <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
              <span class="input-group-text password-toggle" id="togglePassword"><i class="fas fa-eye"></i></span>
            </div>
          </div>

          <button type="submit" class="btn-login">Login <i class="fas fa-sign-in-alt"></i></button>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
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
