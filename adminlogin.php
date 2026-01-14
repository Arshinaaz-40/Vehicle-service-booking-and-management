<?php
session_start();

// Database credentials (InfinityFree)
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";       

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['username']);
    $password = trim($_POST['password']);

    // ✅ Prepare & check if admin exists
    $stmt = $conn->prepare("SELECT password FROM admin WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_password);
        $stmt->fetch();

        // ✅ Compare passwords (plain text)
        if ($password === $db_password) {
            $_SESSION['admin_loggedin'] = true;
            $_SESSION['admin_name'] = $name;

            // ✅ Redirect to admin dashboard
            header("Location: admindashboard.php");
            exit();
        } else {
            $error = "❌ Invalid Password!";
        }
    } else {
        $error = "❌ Admin User Not Found!";
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Vehicle Service Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #4e54c8, #8f94fb);
      font-family: 'Segoe UI', sans-serif;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    }
    .btn-primary {
      background: linear-gradient(135deg, #4e54c8, #8f94fb);
      border: none;
    }
    .btn-primary:hover {
      background: #3b40a4;
    }
  </style>
</head>
<body>
  <div class="card p-4" style="max-width:400px; width:100%;">
    <h3 class="text-center text-primary mb-3">Admin Login</h3>

    <!-- Show error if login fails -->
    <?php if (!empty($error)) { ?>
      <div class="alert alert-danger text-center"><?= $error ?></div>
    <?php } ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="username" class="form-label">Admin Username</label>
        <input type="text" class="form-control" id="username" name="username" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
  </div>
</body>
</html>
