<?php
session_start();
include '../config.php';
require('fpdf/fpdf.php');

/* ==========================
   ADMIN ONLY
========================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized');
}

/* ==========================
   CREATE PDF
========================== */
$pdf = new FPDF('L','mm','A4');
$pdf->SetMargins(10,10,10);
$pdf->SetAutoPageBreak(true,10);
$pdf->AddPage();

/* ==========================
   TITLE
========================== */
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'ARIELS BARBERSHOP - FULL SYSTEM REPORT',0,1,'C');
$pdf->Ln(5);

/* ============================================================
   CUSTOMERS
============================================================ */
$pdf->SetFont('Arial','B',13);
$pdf->Cell(0,8,'Customers',0,1);

$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(20,8,'ID',1,0,'C',true);
$pdf->Cell(80,8,'Full Name',1,0,'C',true);
$pdf->Cell(100,8,'Email',1,0,'C',true);
$pdf->Cell(40,8,'Status',1,1,'C',true);

$pdf->SetFont('Arial','',10);

$q = mysqli_query($conn,"SELECT id, fullname, email_address, status 
                         FROM users 
                         WHERE role='customer'");

while($row = mysqli_fetch_assoc($q)){
    $pdf->Cell(20,8,$row['id'],1);
    $pdf->Cell(80,8,$row['fullname'],1);
    $pdf->Cell(100,8,$row['email_address'],1);
    $pdf->Cell(40,8,$row['status'],1);
    $pdf->Ln();
}

$pdf->Ln(8);


/* ============================================================
   BARBERS
============================================================ */
$pdf->SetFont('Arial','B',13);
$pdf->Cell(0,8,'Barbers',0,1);

$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(20,8,'ID',1,0,'C',true);
$pdf->Cell(80,8,'Full Name',1,0,'C',true);
$pdf->Cell(80,8,'Username',1,0,'C',true);
$pdf->Cell(50,8,'Status',1,1,'C',true);

$pdf->SetFont('Arial','',10);

$q = mysqli_query($conn,"SELECT id, fullname, username, status 
                         FROM users 
                         WHERE role='barber'");

while($row = mysqli_fetch_assoc($q)){
    $pdf->Cell(20,8,$row['id'],1);
    $pdf->Cell(80,8,$row['fullname'],1);
    $pdf->Cell(80,8,$row['username'],1);
    $pdf->Cell(50,8,$row['status'],1);
    $pdf->Ln();
}

$pdf->Ln(8);


/* ============================================================
   APPOINTMENTS
============================================================ */
$pdf->SetFont('Arial','B',13);
$pdf->Cell(0,8,'Appointments',0,1);

$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(15,8,'ID',1,0,'C',true);
$pdf->Cell(60,8,'Customer',1,0,'C',true);
$pdf->Cell(60,8,'Barber',1,0,'C',true);
$pdf->Cell(55,8,'Service',1,0,'C',true);
$pdf->Cell(35,8,'Date',1,0,'C',true);
$pdf->Cell(25,8,'Status',1,1,'C',true);

$pdf->SetFont('Arial','',10);

$q = mysqli_query($conn,"
    SELECT id, customer_name, barber_name, service, appointment_date, status
    FROM appointments
    ORDER BY appointment_date DESC, appointment_time DESC
");

while($row = mysqli_fetch_assoc($q)){
    $pdf->Cell(15,8,$row['id'],1);
    $pdf->Cell(60,8,$row['customer_name'],1);
    $pdf->Cell(60,8,$row['barber_name'],1);
    $pdf->Cell(55,8,$row['service'],1);
    $pdf->Cell(35,8,$row['appointment_date'],1);
    $pdf->Cell(25,8,$row['status'],1);
    $pdf->Ln();
}

$pdf->Ln(8);


/* ============================================================
   PAYMENTS
============================================================ */
$pdf->SetFont('Arial','B',13);
$pdf->Cell(0,8,'Payments',0,1);

$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(15,8,'ID',1,0,'C',true);
$pdf->Cell(20,8,'App ID',1,0,'C',true);
$pdf->Cell(70,8,'Customer',1,0,'C',true);
$pdf->Cell(35,8,'Amount',1,0,'C',true);
$pdf->Cell(35,8,'Status',1,0,'C',true);
$pdf->Cell(45,8,'Payment Date',1,1,'C',true);

$pdf->SetFont('Arial','',10);

$q = mysqli_query($conn,"
    SELECT id, customer_name, price, payment_status, payment_date
    FROM appointments
    ORDER BY payment_date DESC
");

while($row = mysqli_fetch_assoc($q)){
    $pdf->Cell(15,8,$row['id'],1);
    $pdf->Cell(20,8,$row['id'],1);
    $pdf->Cell(70,8,$row['customer_name'],1);
    $pdf->Cell(35,8,number_format($row['price'],2),1);
    $pdf->Cell(35,8,$row['payment_status'],1);
    $pdf->Cell(45,8,$row['payment_date'] ?? '-',1);
    $pdf->Ln();
}

/* ==========================
   OUTPUT PDF
========================== */
$pdf->Output('D','ariels_barbershop_full_report.pdf');
exit;
?>