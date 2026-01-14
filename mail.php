<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

// 🧠 Database Connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

$success = $error = "";

// ✉️ Handle Email Sending
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_mail'])) {
    $to = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $mail = new PHPMailer(true);

    try {
        // ⚙️ SMTP Setup
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'autocareproject11@gmail.com'; // replace with your Gmail
        $mail->Password = 'niju dcfv lsxk iahd';  // 👉 Replace with App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // 👤 Sender & Receiver
        $mail->setFrom('yourgmail@gmail.com', '🚗 Vehicle Service Management');
        $mail->addAddress($to);

        // 📄 Email Content
        $mail->isHTML(true);
        $mail->Subject = "📧 " . $subject;
        $mail->Body    = nl2br($message);

        $mail->send();
        $success = "✅ Email sent successfully to $to!";
    } catch (Exception $e) {
        $error = "⚠️ Message could not be sent. Error: {$mail->ErrorInfo}";
    }
}

// 📋 Fetch Booking Records
$sql = "SELECT id, vehicle, company, number, chassis, phone, email, city, date FROM booking";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>📧 Send Quotation | Vehicle Service Portal</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
.card { border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
.table th { background-color: #007bff; color: white; }
.btn-primary { border-radius: 30px; }
h3 { color: #007bff; font-weight: 600; }
</style>

<script>
function autofill(email, vehicle, company) {
    document.getElementById('email').value = email;
    document.getElementById('subject').value = "💰 Quotation for " + vehicle + " - " + company;
    document.getElementById('message').value = 
`👋 Dear Customer,

Thank you for choosing our Vehicle Service Center 🧰.

Here is your quotation for the ${vehicle} (${company}) 🚗:
- Vehicle No: [Enter Here]
- Chassis No: [Enter Here]

We assure you of top-quality service and timely delivery 🕒.

Warm regards,
🚘 Vehicle Service Management Team`;
}
</script>
</head>

<body>

<div class="container mt-4">
    <div class="card p-4">
        <h3 class="text-center mb-4">📧 Send Quotation to Customers</h3>

        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label class="form-label">📨 Customer Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter customer email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">📝 Subject</label>
                <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter mail subject" required>
            </div>
            <div class="mb-3">
                <label class="form-label">💬 Message</label>
                <textarea name="message" id="message" class="form-control" rows="5" placeholder="Write your message here..." required></textarea>
            </div>
            <button type="submit" name="send_mail" class="btn btn-primary w-100 fs-5">📤 Send Email</button>
        </form>

        <h5 class="text-center mb-3">📋 Customer Booking Records</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>🆔 ID</th>
                        <th>🚗 Vehicle</th>
                        <th>🏢 Company</th>
                        <th>🔢 Number</th>
                        <th>🔩 Chassis</th>
                        <th>📞 Phone</th>
                        <th>📧 Email</th>
                        <th>🌆 City</th>
                        <th>📅 Date</th>
                        <th>⚙️ Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['vehicle']}</td>
                                <td>{$row['company']}</td>
                                <td>{$row['number']}</td>
                                <td>{$row['chassis']}</td>
                                <td>{$row['phone']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['city']}</td>
                                <td>{$row['date']}</td>
                                <td><button class='btn btn-success btn-sm' onclick=\"autofill('{$row['email']}', '{$row['vehicle']}', '{$row['company']}')\">📨 Send Quotation</button></td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>⚠️ No bookings found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ✅ SweetAlert Popup -->
<?php if (!empty($success)) : ?>
<script>
Swal.fire({
    icon: 'success',
    title: '✅ Email Sent!',
    text: '<?php echo $success; ?>',
    timer: 3000,
    showConfirmButton: false,
    background: '#e7f9ee'
});
</script>
<?php elseif (!empty($error)) : ?>
<script>
Swal.fire({
    icon: 'error',
    title: '❌ Failed to Send!',
    text: '<?php echo $error; ?>',
    confirmButtonColor: '#d33'
});
</script>
<?php endif; ?>

</body>
</html>
