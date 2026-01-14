<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
session_start();

// Initialize variables for success/error messages
$success = "";
$error = "";

// Check if jobcard_id is provided via URL
$selected_jobcard = isset($_GET['jobcard_id']) ? intval($_GET['jobcard_id']) : 0;
$jobcard_details = null;
$customer_email_prefill = "";

// If jobcard is selected, fetch its details and customer email
if ($selected_jobcard > 0) {
    $detail_query = "SELECT j.*, b.email 
                     FROM jobcards j 
                     LEFT JOIN booking b ON (
                         b.vehicle = j.vehicle_type OR 
                         b.vehicle LIKE CONCAT('%', j.other_vehicle_name, '%')
                     )
                     WHERE j.id = ? 
                     LIMIT 1";
    $stmt = $conn->prepare($detail_query);
    $stmt->bind_param("i", $selected_jobcard);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $jobcard_details = $result->fetch_assoc();
        $customer_email_prefill = $jobcard_details['email'] ?? '';
    }
    $stmt->close();
}

// ✅ Fetch jobcards (only Verified status)
$jobcards = $conn->query("
  SELECT id, vehicle_type, other_vehicle_name, mechanic_name, delivery_date, status
  FROM jobcards
  WHERE status = 'Verified'
  ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Invoice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .card { border-radius: 12px; }
    .card-header { border-top-left-radius: 12px; border-top-right-radius: 12px; }
    label { font-weight: 600; }
    .image-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .image-row input[type="file"] { flex: 1; }
    .image-row input[type="text"] { flex: 2; }
    .jobcard-info {
      background: #e3f2fd;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid #2196f3;
    }
  </style>
</head>
<body>
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-receipt"></i> Create Invoice</h2>
    <a href="managerdashboard.php" class="btn btn-secondary">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>
  
  <div class="card shadow-lg">
    <div class="card-header bg-dark text-white text-center">
      <h3>🧾 Create Invoice</h3>
    </div>
    <div class="card-body">
      <form action="save_invoice.php" method="POST" enctype="multipart/form-data">

        <!-- Jobcard Selection -->
        <div class="mb-3">
          <label class="form-label">Select Verified Jobcard</label>
          <select name="jobcard_id" class="form-select" required onchange="window.location.href='invoice.php?jobcard_id='+this.value">
            <option value="">-- Select Verified Jobcard --</option>
            <?php if ($jobcards && $jobcards->num_rows > 0): ?>
              <?php while($row = $jobcards->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['id']) ?>" 
                        <?= ($selected_jobcard == $row['id']) ? 'selected' : '' ?>>
                  #<?= htmlspecialchars($row['id']) ?> - <?= htmlspecialchars($row['vehicle_type']) ?>
                  <?= $row['other_vehicle_name'] ? '(' . htmlspecialchars($row['other_vehicle_name']) . ')' : '' ?>
                  - Mechanic: <?= htmlspecialchars($row['mechanic_name']) ?>
                  - Status: <?= htmlspecialchars($row['status']) ?>
                </option>
              <?php endwhile; ?>
            <?php else: ?>
              <option value="">⚠️ No Verified Jobcards Found</option>
            <?php endif; ?>
          </select>
        </div>

        <!-- Display Jobcard Details if selected -->
        <?php if ($jobcard_details): ?>
        <div class="jobcard-info">
          <h5 class="mb-3"><i class="bi bi-info-circle"></i> Jobcard Details</h5>
          <div class="row">
            <div class="col-md-6">
              <p><strong>Vehicle:</strong> <?= htmlspecialchars($jobcard_details['vehicle_type']) ?>
                <?= $jobcard_details['other_vehicle_name'] ? ' - ' . htmlspecialchars($jobcard_details['other_vehicle_name']) : '' ?>
              </p>
              <p><strong>Job:</strong> <?= htmlspecialchars($jobcard_details['job_description']) ?></p>
            </div>
            <div class="col-md-6">
              <p><strong>Mechanic:</strong> <?= htmlspecialchars($jobcard_details['mechanic_name']) ?></p>
              <p><strong>Delivery Date:</strong> <?= date('d M Y', strtotime($jobcard_details['delivery_date'])) ?></p>
            </div>
          </div>
          <?php if ($jobcard_details['supervisor_comments']): ?>
          <div class="alert alert-success mb-0 mt-2">
            <strong>Supervisor Comments:</strong> <?= htmlspecialchars($jobcard_details['supervisor_comments']) ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Customer Email -->
        <div class="mb-3">
          <label class="form-label">Customer Email</label>
          <input type="email" name="customer_email" class="form-control" 
                 value="<?= htmlspecialchars($customer_email_prefill) ?>"
                 placeholder="customer@example.com" required>
          <small class="text-muted">Invoice will be emailed to this address</small>
        </div>

        <!-- Cost Inputs -->
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Labour Cost (₹)</label>
            <input type="number" step="0.01" name="labour_cost" id="labour_cost" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Service Cost (₹)</label>
            <input type="number" step="0.01" name="service_cost" id="service_cost" class="form-control" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Parts Cost (₹)</label>
            <input type="number" step="0.01" name="parts_cost" id="parts_cost" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Other Charges (₹)</label>
            <input type="number" step="0.01" name="other_charges" id="other_charges" class="form-control" required>
          </div>
        </div>

        <!-- Total -->
        <div class="mb-3">
          <label class="form-label">Total Amount (₹)</label>
          <input type="number" step="0.01" name="total" id="total" class="form-control bg-light" readonly>
        </div>

        <!-- Image Upload with Description -->
        <div class="mb-3">
          <label class="form-label">Upload Service Images with Description (Optional)</label>
          <small class="d-block text-muted mb-2">Add before/after images or service completion photos</small>

          <div id="imageContainer">
            <div class="image-row">
              <input type="file" name="images[]" class="form-control" accept="image/*">
              <input type="text" name="image_descriptions[]" class="form-control" placeholder="Description (e.g. Before Service)">
            </div>
          </div>

          <button type="button" class="btn btn-outline-primary mt-2" id="addImageBtn">
            <i class="bi bi-plus-circle"></i> Add More Images
          </button>
        </div>

        <div class="alert alert-info">
          <i class="bi bi-info-circle"></i> 
          <strong>Note:</strong> After submission, the invoice PDF will be generated and automatically emailed to the customer.
        </div>

        <button type="submit" class="btn btn-success w-100 fw-bold">
          <i class="bi bi-send"></i> Generate & Email Invoice
        </button>
      </form>
    </div>
  </div>
</div>

<script>
// Auto-calculate total
document.querySelectorAll("#labour_cost, #service_cost, #parts_cost, #other_charges").forEach(input => {
  input.addEventListener("input", () => {
    const total = 
      (parseFloat(document.getElementById("labour_cost").value) || 0) +
      (parseFloat(document.getElementById("service_cost").value) || 0) +
      (parseFloat(document.getElementById("parts_cost").value) || 0) +
      (parseFloat(document.getElementById("other_charges").value) || 0);
    document.getElementById("total").value = total.toFixed(2);
  });
});

// ✅ Add new image + description rows dynamically
document.getElementById("addImageBtn").addEventListener("click", () => {
  const container = document.getElementById("imageContainer");
  const newRow = document.createElement("div");
  newRow.classList.add("image-row");
  newRow.innerHTML = `
    <input type="file" name="images[]" class="form-control" accept="image/*">
    <input type="text" name="image_descriptions[]" class="form-control" placeholder="Description (e.g. After Repair)">
    <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
      <i class="bi bi-trash"></i>
    </button>
  `;
  container.appendChild(newRow);
});
</script>
</body>
</html>