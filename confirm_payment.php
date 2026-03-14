<?php
session_start();
include '../config.php';

/* =========================
   CUSTOMER ONLY
========================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

/* =========================
   GET FORM DATA
========================= */
$appointment_id = $_POST['appointment_id'] ?? null;
$barber_name    = $_POST['barber_name'] ?? null;
$amount         = $_POST['amount'] ?? null;

if (!$appointment_id || !$barber_name || !$amount) {
    die("Invalid payment data.");
}

/* =========================
   VERIFY APPOINTMENT BELONGS TO CUSTOMER
========================= */
$stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND email_address = ?");
$stmt->bind_param("is", $appointment_id, $_SESSION['email_address']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Appointment not found or unauthorized.");
}

/* =========================
   INSERT PAYMENT RECORD
========================= */
$stmt = $conn->prepare("
    INSERT INTO payments (appointment_id, customer_name, barber_name, amount, status, payment_date)
    VALUES (?, ?, ?, ?, 'Paid', NOW())
");
$stmt->bind_param("isss", $appointment_id, $_SESSION['fullname'], $barber_name, $amount);
$stmt->execute();

/* =========================
   UPDATE APPOINTMENT STATUS
========================= */
$stmt = $conn->prepare("UPDATE appointments SET payment_status='Paid', status='Paid' WHERE id=?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();

/* =========================
   REDIRECT TO SUCCESS PAGE
========================= */
header("Location: payment_success.php?appointment_id=" . $appointment_id);
exit();
?>
