<?php
session_start();
require_once __DIR__ . '/db.php'; // make sure db.php exists in same folder

// Initialize variables for success/error messages
$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle  = $_POST['vehicle'] ?? '';
    $company  = $_POST['company'] ?? '';
    $number   = $_POST['number'] ?? '';
    $chassis  = $_POST['chassis'] ?? '';
    $phone    = $_POST['phone'] ?? '';
    $email    = $_POST['email'] ?? '';
    $city     = $_POST['city'] ?? '';
    $date     = $_POST['date'] ?? '';

    // Simple server-side validation
    if ($vehicle && $company && $number && $chassis && $phone && $email && $city && $date) {
        $stmt = $conn->prepare("INSERT INTO booking (vehicle, company, number, chassis, phone, email, city, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $vehicle, $company, $number, $chassis, $phone, $email, $city, $date);

        if ($stmt->execute()) {
            $success = "Booking confirmed successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error = "All fields are required!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Vehicle Service</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root { --primary: rgba(0, 114, 255, 0.9); --secondary: rgba(0, 198, 255, 0.9); }
    body { background: linear-gradient(135deg, var(--secondary), var(--primary)); font-family: 'Poppins', sans-serif; color: #333; padding-top: 40px; min-height: 100vh; display: flex; align-items: center; }
    .card { border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: none; overflow: hidden; }
    .card-header { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border-radius: 20px 20px 0 0 !important; padding: 20px; text-align: center; }
    .card-body { padding: 30px; }
    .form-label { font-weight: 600; color: #444; margin-bottom: 8px; }
    .form-control, .form-select { border-radius: 10px; padding: 12px 15px; border: 2px solid #e1e5eb; transition: all 0.3s; }
    .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 0.25rem rgba(0,114,255,0.25); }
    .btn-custom { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; font-weight: bold; border-radius: 30px; padding: 12px 30px; transition: 0.3s; border: none; width: 100%; margin-top: 20px; }
    .btn-custom:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,114,255,0.2); }
  </style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 col-md-10">
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0"><i class="fas fa-car"></i> Book Your Vehicle Service</h2>
            <p class="mb-0">Complete the form below to schedule your service</p>
          </div>
          <div class="card-body">
            <?php if($success): ?>
              <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
              <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
              <div class="mb-3">
                <label class="form-label">Vehicle Type *</label>
                <select name="vehicle" class="form-select" required>
                  <option value="" disabled selected>-- Select Vehicle Type --</option>
                  <option value="scooty">Scooter</option>
                  <option value="bike">Motorcycle</option>
                  <option value="car">Car</option>
                  <option value="suv">SUV</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Vehicle Company *</label>
                <select name="company" class="form-select" required>
                  <option value="" disabled selected>-- Select Company --</option>
                  <option value="honda">Honda</option>
                  <option value="yamaha">Yamaha</option>
                  <option value="suzuki">Suzuki</option>
                  <option value="tvs">TVS</option>
                  <option value="hero">Hero</option>
                  <option value="bajaj">Bajaj</option>
                  <option value="mahindra">Mahindra</option>
                  <option value="maruti">Maruti Suzuki</option>
                  <option value="hyundai">Hyundai</option>
                  <option value="tata">Tata</option>
                  <option value="toyota">Toyota</option>
                  <option value="ford">Ford</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Vehicle Number *</label>
                <input type="text" name="number" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Chassis Number (last 6 digits) *</label>
                <input type="text" name="chassis" maxlength="6" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Phone Number *</label>
                <input type="tel" name="phone" maxlength="10" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Select Your City *</label>
                <select name="city" class="form-select" required>
                  <option value="" disabled selected>-- Select City --</option>
                  <option value="hyderabad">Hyderabad</option>
                  <option value="warangal">Warangal</option>
                  <option value="nizamabad">Nizamabad</option>
                  <option value="khammam">Khammam</option>
                  <option value="karimnagar">Karimnagar</option>
                  <option value="ramagundam">Ramagundam</option>
                  <option value="mahbubnagar">Mahbubnagar</option>
                  <option value="nalgonda">Nalgonda</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Service Date *</label>
                <input type="date" name="date" class="form-control" required>
              </div>

              <button type="submit" class="btn btn-custom w-100">Confirm Booking</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
