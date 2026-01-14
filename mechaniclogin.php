<?php
session_start();
$message = "";

// ✅ Database connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    if (!empty($user) && !empty($pass)) {
        // Fetch both first and last name
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password, status FROM mechanic WHERE email=? OR phone=?");
        $stmt->bind_param("ss", $user, $user);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $first_name, $last_name, $hashed_password, $status);
            $stmt->fetch();

            if ($status != 'Active') {
                $message = "Your account is inactive. Contact admin.";
            } elseif (password_verify($pass, $hashed_password)) {
                // Combine first and last name
                $full_name = trim($first_name . " " . $last_name);

                // Set session variables
                $_SESSION['mechanic_loggedin'] = true;
                $_SESSION['mechanic_id'] = $id;
                $_SESSION['mechanic_name'] = $full_name;
                $_SESSION['mechanic_email_or_phone'] = $user;
                $_SESSION['LAST_ACTIVITY'] = time();

                header("Location: mechanicdashboard.php");
                exit();
            } else {
                $message = "Invalid credentials!";
            }
        } else {
            $message = "No account found with this username!";
        }

        $stmt->close();
    } else {
        $message = "Please enter username and password!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mechanic Login - Vehicle Service Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #00c6ff, #0072ff);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .login-card {
      width: 400px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      overflow: hidden;
    }
    .login-header {
      background: linear-gradient(135deg, #0072ff, #00c6ff);
      color: white;
      padding: 25px;
      text-align: center;
    }
    .login-body {
      padding: 30px;
      background: white;
    }
    .btn-login {
      width: 100%;
      background: linear-gradient(135deg, #0072ff, #00c6ff);
      color: #fff;
      border-radius: 30px;
      padding: 12px 0;
      border: none;
    }
    .password-toggle {
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-header">
      <h2>Mechanic Login</h2>
      <p>Access your dashboard</p>
    </div>
    <div class="login-body">
      <?php if($message != ""): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="mb-3">
          <label for="username" class="form-label">Email or Phone</label>
          <input type="text" class="form-control" id="username" name="username" placeholder="Enter email or phone" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
            <span class="input-group-text password-toggle" id="togglePassword">
              <i class="fas fa-eye"></i>
            </span>
          </div>
        </div>
        <button type="submit" class="btn btn-login">Login <i class="fas fa-sign-in-alt"></i></button>
      </form>
    </div>
  </div>

  <script>
    // 👁 Toggle password visibility
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
