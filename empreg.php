<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";        //fixed typo  
$dbname     = "if0_39907230_mydb40";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name      = $_POST['first_name'];
    $last_name       = $_POST['last_name'];
    $phone           = $_POST['phone'];
    $email           = $_POST['email'];
    $aadhar          = $_POST['aadhar'];
    $date_of_joining = $_POST['date_of_joining'];
    $address         = $_POST['address'];
    $role            = $_POST['role'];
    $office_branch   = $_POST['office_branch'];
    $bank_name       = $_POST['bank_name'];
    $account_number  = $_POST['account_number'];
    $ifsc_code       = $_POST['ifsc_code'];
    $bank_branch     = $_POST['bank_branch'];
    $password_input  = $_POST['password']; // password from form

    // File upload
    $photo = "";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $photo = $upload_dir . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photo);
    }

    // Hash the password
    $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

    // Determine table name based on role
    $role_table = "";
    if (strtolower($role) == "mechanic") {
        $role_table = "mechanic";
    } elseif (strtolower($role) == "supervisor") {
        $role_table = "supervisor";
    } elseif (strtolower($role) == "manager") {
        $role_table = "manager";
    } else {
        die("Invalid role selected!");
    }

    // Prepare insert statement
    $stmt = $conn->prepare("INSERT INTO $role_table 
        (first_name, last_name, phone, email, aadhar, date_of_joining, address, role, office_branch, photo, bank_name, account_number, ifsc_code, bank_branch, password, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $status = "Active";
    $stmt->bind_param("ssssssssssssssss",
        $first_name, $last_name, $phone, $email, $aadhar, $date_of_joining, $address,
        $role, $office_branch, $photo, $bank_name, $account_number, $ifsc_code, $bank_branch,
        $hashed_password, $status
    );

    if ($stmt->execute()) {
        echo "<script>alert('✅ $role Registered Successfully!'); window.location.href='empreg.php';</script>";
        exit();
    } else {
        echo "❌ Error: " . $stmt->error;
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
  <title>Register Employee - AutoCare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: rgba(0, 114, 255, 0.85);
      --secondary: rgba(0, 198, 255, 0.85);
      --dark: #142850;
      --light: #f1f6f9;
    }
    body { background: linear-gradient(135deg, var(--secondary), var(--primary)); font-family: 'Poppins', sans-serif; min-height: 100vh; padding: 15px; }
    .registration-container { max-width: 800px; margin: 0 auto; }
    .registration-card { border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border: none; overflow: hidden; margin: 20px 0; }
    .registration-header { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; padding: 20px 15px; text-align: center; }
    .registration-body { padding: 20px 15px; background: white; }
    .form-label { font-weight: 600; color: #444; margin-bottom: 5px; }
    .form-control, .form-select { border-radius: 8px; padding: 10px 12px; border: 2px solid #e1e5eb; }
    .btn-register { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; font-weight: bold; border-radius: 25px; padding: 12px 25px; border: none; width: 100%; margin-top: 15px; }
    .btn-register:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,114,255,0.2); }
    .photo-preview { width: 100px; height: 100px; border-radius: 8px; object-fit: cover; border: 2px dashed #ddd; display: none; margin-top: 8px; }
    .section-title { font-size: 1.1rem; font-weight: 600; color: var(--primary); margin: 20px 0 12px; padding-bottom: 6px; border-bottom: 2px solid #e9ecef; }
  </style>
</head>
<body>
  <div class="registration-container">
    <div class="registration-card">
      <div class="registration-header">
        <h2><i class="fas fa-user-plus"></i> Register Employee</h2>
        <p>Add a new employee to AutoCare</p>
      </div>
      <div class="registration-body">
        <form action="empreg.php" method="POST" enctype="multipart/form-data" id="employeeForm">

          <!-- Personal Information -->
          <div class="section-title"><i class="fas fa-user-circle me-2"></i>Personal Information</div>
          <div class="row g-2">
            <div class="col-md-6"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" required></div>
          </div>
          <div class="row g-2 mt-2">
            <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" name="phone" maxlength="10" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
          </div>
          <div class="row g-2 mt-2">
            <div class="col-md-6"><label class="form-label">Aadhar</label><input type="text" name="aadhar" maxlength="12" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Date of Joining</label><input type="date" name="date_of_joining" class="form-control" required></div>
          </div>
          <div class="mt-2"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2" required></textarea></div>

          <!-- Employment -->
          <div class="section-title"><i class="fas fa-briefcase me-2"></i>Employment Info</div>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Role</label>
              <select name="role" class="form-select" required>
                <option value="">--Select Role--</option>
                <option value="Mechanic">Mechanic</option>
                <option value="Supervisor">Supervisor</option>
                <option value="Manager">Manager</option>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Office Branch</label><input type="text" name="office_branch" class="form-control" required></div>
          </div>
          <div class="row g-2 mt-2">
            <div class="col-md-6">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" placeholder="Set a password" required>
            </div>
            <div class="col-md-6"><label class="form-label">Photo</label><input type="file" name="photo" class="form-control" accept="image/*" onchange="previewPhoto(this)"><img id="photoPreview" class="photo-preview"></div>
          </div>

          <!-- Bank Info -->
          <div class="section-title"><i class="fas fa-university me-2"></i>Bank Info</div>
          <div class="row g-2">
            <div class="col-md-6"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Account Number</label><input type="text" name="account_number" class="form-control" required></div>
          </div>
          <div class="row g-2 mt-2">
            <div class="col-md-6"><label class="form-label">IFSC Code</label><input type="text" name="ifsc_code" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Bank Branch</label><input type="text" name="bank_branch" class="form-control"></div>
          </div>

          <button type="submit" class="btn-register mt-3"><i class="fas fa-user-plus me-2"></i>Register Employee</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function previewPhoto(input) {
      const preview = document.getElementById('photoPreview');
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>
</body>
</html>
