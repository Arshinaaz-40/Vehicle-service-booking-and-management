<?php
session_start();

// Check which user is logged in (role-based)
if (isset($_SESSION['admin_loggedin'])) {
    $redirect = "adminlogin.php";
} elseif (isset($_SESSION['manager_loggedin'])) {
    $redirect = "managerlogin.php";
} elseif (isset($_SESSION['supervisor_loggedin'])) {
    $redirect = "supervisorlogin.php";
} elseif (isset($_SESSION['mechanic_loggedin'])) {
    $redirect = "mechaniclogin.php";
} elseif (isset($_SESSION['customer_loggedin'])) {
    $redirect = "customerlogin.php";
} else {
    $redirect = "index.php"; // default fallback
}

// Destroy the session
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to the correct login page
header("Location: $redirect?loggedout=true");
exit();
?>