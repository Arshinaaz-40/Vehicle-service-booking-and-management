<?php
session_start();
require_once "db.php";

// ✅ Check Manager Login
if (!isset($_SESSION['manager_loggedin']) || $_SESSION['manager_loggedin'] !== true) {
    header("Location: managerlogin.php");
    exit();
}

// ✅ Create `jobcards` table if not exists
$conn->query("
CREATE TABLE IF NOT EXISTS jobcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_type VARCHAR(50),
    other_vehicle_name VARCHAR(100),
    job_description VARCHAR(255),
    mechanic_name VARCHAR(100),
    delivery_date DATE,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

// ✅ Get form data
$vehicleType = $_POST['vehicle_type'];
$otherVehicleName = isset($_POST['other_vehicle_name']) ? trim($_POST['other_vehicle_name']) : '';
$jobDescription = $_POST['job_description'];
$mechanicName = $_POST['mechanic_name'];
$deliveryDate = $_POST['delivery_date'];
$remarks = $_POST['remarks'];

// ✅ If "Other" selected but input empty
if ($vehicleType === "Other" && empty($otherVehicleName)) {
    echo "<script>alert('⚠️ Please enter the other vehicle name!'); window.history.back();</script>";
    exit();
}

// ✅ Insert record
$stmt = $conn->prepare("INSERT INTO jobcards (vehicle_type, other_vehicle_name, job_description, mechanic_name, delivery_date, remarks)
                        VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $vehicleType, $otherVehicleName, $jobDescription, $mechanicName, $deliveryDate, $remarks);

if ($stmt->execute()) {
    echo "<script>
        alert('✅ Job Card Created Successfully! 🎉');
        window.location.href='managerdashboard.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Error saving job card. Please try again.');
        window.history.back();
    </script>";
}

$stmt->close();
$conn->close();
?>
