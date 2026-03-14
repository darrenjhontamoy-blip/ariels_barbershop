<?php
session_start();
include '../config.php';

if (!isset($_SESSION['email_address'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$appointment_id = $_POST['appointment_id'];
$amount = $_POST['amount'];
$payment_method = $_POST['payment_method'];
$customer_email = $_SESSION['email_address'];

/* SAVE PAYMENT */
$stmt = $conn->prepare("
    INSERT INTO payments
    (appointment_id, customer_email, amount, payment_method, payment_status)
    VALUES (?, ?, ?, ?, 'Paid')
");
$stmt->bind_param("isds", $appointment_id, $customer_email, $amount, $payment_method);
$stmt->execute();

/* UPDATE APPOINTMENT PAYMENT STATUS */
$update = $conn->prepare("
    UPDATE appointments
    SET payment_status = 'Paid'
    WHERE id = ?
");
$update->bind_param("i", $appointment_id);
$update->execute();

/* SUCCESS */
header("Location: payment_success.php");
exit();
