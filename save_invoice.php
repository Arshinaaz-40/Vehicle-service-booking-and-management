<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';
require('fpdf/fpdf.php'); 

session_start();

// ✅ Database connection
$servername = "sql207.infinityfree.com";  
$username   = "if0_39907230";             
$password   = "Arshinaaz28";          
$dbname     = "if0_39907230_mydb40";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// ✅ Get form data
$jobcard_id      = $_POST['jobcard_id'];
$customer_email  = $_POST['customer_email'];
$labour_cost     = $_POST['labour_cost'];
$service_cost    = $_POST['service_cost'];
$parts_cost      = $_POST['parts_cost'];
$other_charges   = $_POST['other_charges'];
$total           = $labour_cost + $service_cost + $parts_cost + $other_charges;

// ✅ Insert invoice record
$stmt = $conn->prepare("INSERT INTO invoices (jobcard_id, customer_email, labour_cost, service_cost, parts_cost, other_charges, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isddddd", $jobcard_id, $customer_email, $labour_cost, $service_cost, $parts_cost, $other_charges, $total);
$stmt->execute();
$invoice_id = $stmt->insert_id;
$stmt->close();

// ✅ Handle optional image uploads with descriptions
$upload_dir = __DIR__ . "/uploads/invoice_images/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if (!empty($_FILES['images']['name'][0])) {
    $descriptions = $_POST['image_descriptions'] ?? [];

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === 0) {
            $filename = basename($_FILES['images']['name'][$key]);
            $target = $upload_dir . time() . "_" . $filename;
            $desc = $conn->real_escape_string($descriptions[$key] ?? 'No description provided');

            if (move_uploaded_file($tmp_name, $target)) {
                $rel_path = "uploads/invoice_images/" . basename($target);
                $conn->query("INSERT INTO invoice_images (invoice_id, image_path, description) VALUES ($invoice_id, '$rel_path', '$desc')");
            }
        }
    }
}

// ✅ Fetch jobcard details
$jobcard = $conn->query("SELECT * FROM jobcards WHERE id=$jobcard_id")->fetch_assoc();

// ✅ Fetch uploaded images + descriptions
$images = [];
$res = $conn->query("SELECT image_path, description FROM invoice_images WHERE invoice_id=$invoice_id");
while ($row = $res->fetch_assoc()) {
    $images[] = [
        'path' => __DIR__ . '/' . $row['image_path'],
        'desc' => $row['description']
    ];
}

// ✅ PDF CLASS
class PDF extends FPDF {
    function Header() {
        $logo = __DIR__ . '/uploads/logo/car-logo.png';
        if (file_exists($logo)) {
            $this->Image($logo, 10, 8, 25);
        }
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'VEHICLE SERVICE CENTER', 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 8, 'Trusted Auto Care Solutions', 0, 1, 'C');
        $this->Ln(5);
        $this->SetDrawColor(0, 102, 204);
        $this->SetLineWidth(0.8);
        $this->Line(10, 35, 200, 35);
        $this->Ln(10);
    }
    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Thank you for choosing Vehicle Service Center!', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 8, 'Contact us: autocareproject11@gmail.com | +91-90000-12345', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();

// ✅ Invoice Header
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(100, 8, "Invoice No: INV-$invoice_id", 0, 0);
$pdf->Cell(0, 8, "Date: " . date('d M Y'), 0, 1, 'R');
$pdf->Ln(5);

// ✅ Customer & Vehicle Details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, "Customer & Vehicle Details", 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(100, 8, "Vehicle Type: " . ($jobcard['vehicle_type'] ?? 'N/A'), 0, 1);
$pdf->Cell(100, 8, "Mechanic: " . ($jobcard['mechanic_name'] ?? 'N/A'), 0, 1);
$pdf->Cell(100, 8, "Delivery Date: " . ($jobcard['delivery_date'] ?? 'N/A'), 0, 1);
$pdf->Cell(100, 8, "Customer Email: " . $customer_email, 0, 1);
$pdf->Ln(8);

// ✅ Cost Breakdown Table
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(220, 230, 255);
$pdf->Cell(90, 10, "Description", 1, 0, 'C', true);
$pdf->Cell(60, 10, "Cost (₹)", 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(90, 10, "Labour Cost", 1);
$pdf->Cell(60, 10, number_format($labour_cost, 2), 1, 1, 'R');
$pdf->Cell(90, 10, "Service Cost", 1);
$pdf->Cell(60, 10, number_format($service_cost, 2), 1, 1, 'R');
$pdf->Cell(90, 10, "Parts Cost", 1);
$pdf->Cell(60, 10, number_format($parts_cost, 2), 1, 1, 'R');
$pdf->Cell(90, 10, "Other Charges", 1);
$pdf->Cell(60, 10, number_format($other_charges, 2), 1, 1, 'R');

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 255, 200);
$pdf->Cell(90, 10, "TOTAL", 1, 0, 'C', true);
$pdf->Cell(60, 10, "₹" . number_format($total, 2), 1, 1, 'R', true);
$pdf->Ln(10);

// ✅ Attach Uploaded Images + Descriptions
if (!empty($images)) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, "Attached Service Images", 0, 1);

    foreach ($images as $imgData) {
        $path = $imgData['path'];
        $desc = $imgData['desc'];

        if (file_exists($path)) {
            $pdf->Image($path, 20, $pdf->GetY(), 60);
            $pdf->SetY($pdf->GetY() + 65);
            $pdf->SetFont('Arial', 'I', 11);
            $pdf->MultiCell(0, 8, "🔹 " . $desc, 0, 'L');
            $pdf->Ln(5);
        }
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, "No images uploaded for this invoice.", 0, 1);
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 11);
$pdf->Cell(0, 10, "We appreciate your trust in our service center!", 0, 1, 'C');

// ✅ Save PDF
$pdf_dir = __DIR__ . "/uploads/invoices/";
if (!is_dir($pdf_dir)) mkdir($pdf_dir, 0777, true);
$pdf_file = $pdf_dir . "invoice_$invoice_id.pdf";
$pdf->Output('F', $pdf_file);

// ✅ Email with PDF
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'autocareproject11@gmail.com';
    $mail->Password   = 'niju dcfv lsxk iahd'; // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('autocareproject11@gmail.com', 'Vehicle Service Center');
    $mail->addAddress($customer_email);
    $mail->Subject = "Invoice INV-$invoice_id - Vehicle Service Center";
    $mail->Body    = "Dear Customer,<br><br>Your invoice (INV-$invoice_id) is attached below.<br><br>Thank you for choosing us!<br><br>– Vehicle Service Center";
    $mail->isHTML(true);
    $mail->addAttachment($pdf_file);

    $mail->send();
    echo "<script>alert('✅ Invoice generated and sent successfully!');window.location.href='managerdashboard.php';</script>";
} catch (Exception $e) {
    echo "<script>alert('⚠️ Invoice saved but email not sent. Error: {$mail->ErrorInfo}');window.location.href='managerdashboard.php';</script>";
}
?>
