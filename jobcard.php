<?php
session_start();
require_once "db.php";

// ✅ Check Manager Login
if (!isset($_SESSION['manager_loggedin']) || $_SESSION['manager_loggedin'] !== true) {
    header("Location: managerlogin.php");
    exit();
}

// ✅ Fetch mechanics (from mechanic table)
$mechanics = $conn->query("SELECT first_name, last_name FROM mechanic");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Job Card - Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <script>
    // 🚗 Dynamic Job Description Logic + "Other" input field toggle
    function updateJobDescriptions() {
      const vehicleType = document.getElementById("vehicleType").value;
      const jobDesc = document.getElementById("jobDescription");
      const otherVehicleDiv = document.getElementById("otherVehicleDiv");

      jobDesc.innerHTML = ""; // clear previous options

      let jobs = [];

      if (vehicleType === "Car") {
        jobs = [
          "General Service", "Engine Tuning", "Brake Replacement", "AC Service",
          "Wheel Alignment & Balancing", "Electrical Work", "Dent & Paint",
          "Battery Check", "Oil Change", "Coolant Refill"
        ];
        otherVehicleDiv.style.display = "none";
      } 
      else if (vehicleType === "Bike") {
        jobs = [
          "General Service", "Engine Oil Change", "Chain Lubrication & Adjustment",
          "Brake Pad Replacement", "Electrical Check", "Tyre & Wheel Alignment",
          "Clutch Adjustment", "Battery Replacement", "Spark Plug Replacement"
        ];
        otherVehicleDiv.style.display = "none";
      } 
      else if (vehicleType === "Scooty") {
        jobs = [
          "General Service", "Oil Change", "Belt Replacement", "Brake Adjustment",
          "Electrical & Light Check", "Air Filter Cleaning", "Battery Check",
          "Tyre Pressure & Alignment", "Body Polishing"
        ];
        otherVehicleDiv.style.display = "none";
      } 
      else if (vehicleType === "Truck") {
        jobs = [
          "Engine Overhaul", "Brake Drum Repair", "Suspension Service", "Gearbox Repair",
          "Diesel System Cleaning", "Hydraulic System Check", "Tyre Rotation",
          "Battery Maintenance", "Heavy Load Check"
        ];
        otherVehicleDiv.style.display = "none";
      } 
      else if (vehicleType === "Other") {
        jobs = [
          "General Inspection", "Oil and Filter Change", "Brake and Tyre Check",
          "Electrical Work", "Body Repair", "Polishing", "Battery Replacement",
          "Performance Tuning", "Custom Service"
        ];
        // ✅ Show the input field for custom vehicle name
        otherVehicleDiv.style.display = "block";
      } else {
        otherVehicleDiv.style.display = "none";
      }

      jobs.forEach(job => {
        const option = document.createElement("option");
        option.value = job;
        option.textContent = job;
        jobDesc.appendChild(option);
      });
    }
  </script>
</head>

<body class="bg-light">
  <div class="container py-5">
    <h2 class="text-center mb-4 fw-bold">🧾 Create Job Card</h2>

    <form method="POST" action="save_jobcard.php" class="p-4 shadow-lg bg-white rounded-3">
      
      <!-- Vehicle Selection -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Select Vehicle Type</label>
        <select name="vehicle_type" class="form-select" id="vehicleType" onchange="updateJobDescriptions()" required>
          <option value="">-- Select Vehicle Type --</option>
          <option value="Car">🚗 Car</option>
          <option value="Bike">🏍️ Bike</option>
          <option value="Scooty">🛵 Scooty</option>
          <option value="Truck">🚚 Truck</option>
          <option value="Other">⚙️ Other</option>
        </select>
      </div>

      <!-- ✅ Input for Other Vehicle Name -->
      <div class="mb-3" id="otherVehicleDiv" style="display:none;">
        <label class="form-label fw-semibold">Enter Other Vehicle Name</label>
        <input type="text" name="other_vehicle_name" class="form-control" placeholder="e.g., Tractor, Auto, JCB, etc.">
      </div>

      <!-- Job Description -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Job Description</label>
        <select name="job_description" id="jobDescription" class="form-select" required>
          <option value="">-- Select Job Description --</option>
        </select>
      </div>

      <!-- Mechanic Selection -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Assign Mechanic</label>
        <select name="mechanic_name" class="form-select" required>
          <option value="">-- Select Mechanic --</option>
          <?php while ($row = $mechanics->fetch_assoc()) { ?>
            <option value="<?= $row['first_name'] . ' ' . $row['last_name']; ?>">
              <?= $row['first_name'] . ' ' . $row['last_name']; ?>
            </option>
          <?php } ?>
        </select>
      </div>

      <!-- Estimated Delivery Date -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Estimated Delivery Date</label>
        <input type="date" name="delivery_date" class="form-control" required>
      </div>

      <!-- Remarks -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Remarks (optional)</label>
        <textarea name="remarks" class="form-control" rows="3" placeholder="Any special instructions..."></textarea>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="btn btn-success w-100">✅ Create Job Card</button>
    </form>
  </div>
</body>
</html>
