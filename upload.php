<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

// Session check
if(!isset($_SESSION['mechanic_loggedin']) || $_SESSION['mechanic_loggedin'] !== true){
    die("Unauthorized access. <a href='mechaniclogin.php'>Login here</a>");
}

// Composer autoload
require_once __DIR__.'/vendor/autoload.php';

use TCPDF;

// Directories
$pdfDir = __DIR__.'/pdf/';
$uploadDir = __DIR__.'/uploads/';
if(!is_dir($pdfDir)) mkdir($pdfDir,0777,true);
if(!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $titles = $_POST['titles'] ?? [];
    $descs  = $_POST['descs'] ?? [];
    $files  = $_FILES['images'] ?? null;

    $labour  = (float)($_POST['labour'] ?? 0);
    $service = (float)($_POST['service'] ?? 0);
    $parts   = (float)($_POST['parts'] ?? 0);
    $other   = (float)($_POST['other'] ?? 0);
    $total   = $labour + $service + $parts + $other;

    if(!$files || empty($files['name'])) die("No files uploaded.");

    $savedFiles = [];
    foreach($files['tmp_name'] as $i => $tmpName){
        if(!is_uploaded_file($tmpName)) continue;
        $name = basename($files['name'][$i]);
        $safeName = uniqid().'_'.preg_replace("/[^a-zA-Z0-9\._-]/","_",$name);
        $target = $uploadDir.$safeName;
        if(move_uploaded_file($tmpName,$target)){
            $savedFiles[] = [
                'path' => $target,
                'title'=> trim($titles[$i] ?? ''),
                'desc' => trim($descs[$i] ?? '')
            ];
        }
    }

    if(empty($savedFiles)) die("No valid files saved.");

    // Extend TCPDF
    class MyPDF extends TCPDF{
        public function Header(){
            $this->SetFillColor(245,245,220);
            $this->Rect(0,0,$this->w,$this->h,'F');
        }
        public function Footer(){
            $this->SetY(-15);
            $this->SetFont('dejavusans','I',8);
            $this->Cell(0,10,'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(),0,0,'C');
        }
    }

    $pdf = new MyPDF();
    $pdf->SetMargins(15,20,15);
    $pdf->AddPage();
    $pdf->SetFont('dejavusans','B',20);
    $pdf->Cell(0,15,"AutoCare",0,1,'C');
    $pdf->SetFont('dejavusans','',14);
    $pdf->Cell(0,8,"Bill (Inclusive of all taxes)",0,1,'C');

    // Billing Table
    $pdf->SetFont('dejavusans','B',12);
    $pdf->Cell(100,7,'Description',1);
    $pdf->Cell(0,7,'Cost (₹)',1,1);
    $pdf->SetFont('dejavusans','',12);
    $pdf->Cell(100,7,'Labour Cost',1);
    $pdf->Cell(0,7,"₹ $labour",1,1);
    $pdf->Cell(100,7,'Service Cost',1);
    $pdf->Cell(0,7,"₹ $service",1,1);
    $pdf->Cell(100,7,'Parts Cost',1);
    $pdf->Cell(0,7,"₹ $parts",1,1);
    $pdf->Cell(100,7,'Other Charges',1);
    $pdf->Cell(0,7,"₹ $other",1,1);
    $pdf->SetFont('dejavusans','B',12);
    $pdf->Cell(100,7,'Total',1);
    $pdf->Cell(0,7,"₹ $total",1,1);

    // Images
    $count = 0;
    foreach($savedFiles as $file){
        if($count%4==0) $pdf->AddPage();
        $x = ($count%2)*95 + 20;
        $y = (floor(($count%4)/2))*120 + 20;
        $pdf->Image($file['path'],$x,$y,85,60,'','','',true);
        $pdf->SetXY($x,$y+65);
        $pdf->SetFont('dejavusans','B',12);
        $pdf->MultiCell(85,6,$file['title'],0,'L');
        $pdf->SetXY($x,$y+80);
        $pdf->SetFont('dejavusans','',10);
        $pdf->MultiCell(85,5,$file['desc'],0,'L');
        $count++;
    }

    $pdfName = "bill_".date("Ymd_His").".pdf";
    $pdfPath = $pdfDir.$pdfName;
    $pdf->Output($pdfPath,'F');

    // Cleanup uploads
    foreach($savedFiles as $file) @unlink($file['path']);

    echo "✅ Bill PDF created: <a href='pdf/$pdfName' target='_blank'>$pdfName</a>";
}else{
    echo "Invalid request.";
}
?>
