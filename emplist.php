<?php
session_start();

// ✅ Check if manager is logged in
if (!isset($_SESSION['manager_loggedin']) || $_SESSION['manager_loggedin'] !== true) {
    header("Location: managerlogin.php");
    exit();
}

// ✅ Database Connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// ✅ Search Functionality
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $query = "
        SELECT id, first_name, last_name, email, phone, role, office_branch, 'manager' AS table_name FROM manager 
        WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR role LIKE '%$search%' OR office_branch LIKE '%$search%'
        UNION
        SELECT id, first_name, last_name, email, phone, role, office_branch, 'supervisor' AS table_name FROM supervisor 
        WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR role LIKE '%$search%' OR office_branch LIKE '%$search%'
        UNION
        SELECT id, first_name, last_name, email, phone, role, office_branch, 'mechanic' AS table_name FROM mechanic 
        WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR role LIKE '%$search%' OR office_branch LIKE '%$search%'
        ORDER BY first_name ASC
    ";
} else {
    $query = "
        SELECT id, first_name, last_name, email, phone, role, office_branch, 'manager' AS table_name FROM manager
        UNION
        SELECT id, first_name, last_name, email, phone, role, office_branch, 'supervisor' AS table_name FROM supervisor
        UNION
        SELECT id, first_name, last_name, email, phone, role, office_branch, 'mechanic' AS table_name FROM mechanic
        ORDER BY first_name ASC
    ";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee List</title>

  <!-- ✅ Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .container { margin-top: 40px; }
    .table thead { background-color: #007bff; color: white; }
    .card { border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .search-bar { width: 300px; }
  </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container">
  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="text-primary mb-0">Employee List</h4>
      <form class="d-flex" method="GET">
        <input class="form-control me-2 search-bar" type="search" name="search" placeholder="Search employees..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-primary">Search</button>
      </form>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Branch</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['phone']); ?></td>
              <td><?php echo ucfirst(htmlspecialchars($row['role'])); ?></td>
              <td><?php echo htmlspecialchars($row['office_branch']); ?></td>
              <td>
                <a href="view_employee.php?id=<?php echo $row['id']; ?>&table=<?php echo $row['table_name']; ?>" class="btn btn-sm btn-info">View</a>
                <a href="edit_employee.php?id=<?php echo $row['id']; ?>&table=<?php echo $row['table_name']; ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="delete_employee.php?id=<?php echo $row['id']; ?>&table=<?php echo $row['table_name']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this employee?')">Delete</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info">No employees found.</div>
    <?php endif; ?>
  </div>
</div>

<!-- ✅ Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
