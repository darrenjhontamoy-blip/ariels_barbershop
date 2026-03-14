<?php
include '../config.php';
session_start();

/* ADMIN ONLY */
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit();
}

$appointment_id = $_POST['appointment_id'];
$payment_method = $_POST['payment_method'];

// Validate
if(empty($appointment_id) || empty($payment_method)){
    die("Invalid request");
}

// Get appointment details
$res = $conn->query("SELECT customer_name, service, barber_name FROM appointments WHERE id='$appointment_id'");
$row = $res->fetch_assoc();

if(!$row){
    die("Appointment not found");
}

// Insert payment record
$conn->query("INSERT INTO payments 
(customer_name, service, barber_name, payment_method, payment_status, created_at, appointment_id, payment_date) 
VALUES (
    '".mysqli_real_escape_string($conn, $row['customer_name'])."',
    '".mysqli_real_escape_string($conn, $row['service'])."',
    '".mysqli_real_escape_string($conn, $row['barber_name'])."',
    '".mysqli_real_escape_string($conn, $payment_method)."',
    'Paid',
    NOW(),
    '$appointment_id',
    NOW()
)");

// Update appointment status
$conn->query("UPDATE appointments SET status='Completed' WHERE id='$appointment_id'");

header("Location: payments.php");
?>
