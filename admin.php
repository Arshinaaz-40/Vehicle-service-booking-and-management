<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Vehicle Details Search</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow p-4">
    <h3 class="text-center mb-4">🔍 Vehicle Details Search</h3>

    <form id="searchForm" class="row g-3">
      <!-- Vehicle Type -->
      <div class="col-md-3">
        <label for="vehicleType" class="form-label">Vehicle Type</label>
        <select id="vehicleType" class="form-select">
          <option value="">Select Vehicle</option>
          <option value="Scooter">Scooter</option>
          <option value="Bike">Bike</option>
          <option value="Car">Car</option>
        </select>
      </div>

      <!-- Vehicle Number -->
      <div class="col-md-3">
        <label for="vehicleNumber" class="form-label">Vehicle Number</label>
        <input type="text" id="vehicleNumber" class="form-control" placeholder="Enter Vehicle No">
      </div>

      <!-- Chassis Number -->
      <div class="col-md-3">
        <label for="chassisNumber" class="form-label">Chassis Number</label>
        <input type="text" id="chassisNumber" class="form-control" placeholder="Enter Chassis No">
      </div>

      <!-- City / Location -->
      <div class="col-md-3">
        <label for="city" class="form-label">Location (City)</label>
        <select id="city" class="form-select">
          <option value="">Select City</option>
          <option>Hyderabad</option>
          <option>Warangal</option>
          <option>Karimnagar</option>
          <option>Nizamabad</option>
          <option>Khammam</option>
          <option>Adilabad</option>
          <option>Mahbubnagar</option>
          <option>Nalgonda</option>
          <option>Siddipet</option>
          <option>Ramagundam</option>
        </select>
      </div>

      <!-- Buttons -->
      <div class="col-12 text-center mt-3">
        <button type="button" class="btn btn-primary me-2" id="searchBtn">Search</button>
        <button type="button" class="btn btn-secondary" id="showAllBtn">Show All</button>
      </div>
    </form>

    <!-- Results Table -->
    <div class="table-responsive mt-4">
      <table class="table table-bordered table-striped">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th>
            <th>Vehicle</th>
            <th>Company</th>
            <th>Number</th>
            <th>Chassis</th>
            <th>Phone</th>
            <th>Email</th>
            <th>City</th>
            <th>Date</th>
            <th>Created At</th>
          </tr>
        </thead>
        <tbody id="vehicleTableBody" class="text-center">
          <!-- Dynamic rows will appear here -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Dummy frontend-only example
  document.getElementById('searchBtn').addEventListener('click', () => {
    alert('Search triggered — will send criteria to backend dynamically!');
  });

  document.getElementById('showAllBtn').addEventListener('click', () => {
    alert('Show All button clicked — will display all vehicle records!');
  });
</script>

</body>
</html>