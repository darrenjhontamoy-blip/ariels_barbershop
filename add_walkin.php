<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../config.php';

/* ==========================
   ADMIN ONLY
========================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ==========================
   POST ONLY
========================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_walkin_form.php"); // form page
    exit();
}

/* ==========================
   GET DATA & SANITIZE
========================== */
$customer_name    = mysqli_real_escape_string($conn, $_POST['customer_name']);
$service          = mysqli_real_escape_string($conn, $_POST['service']);
$barber_name      = mysqli_real_escape_string($conn, $_POST['barber_name']);
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];

/* ==========================
   INSERT WALK-IN
========================== */
$insert = mysqli_query($conn, "
    INSERT INTO walkin_queue
    (customer_name, service, barber_name, appointment_date, appointment_time, status)
    VALUES
    ('$customer_name', '$service', '$barber_name', '$appointment_date', '$appointment_time', 'Pending')
");

if (!$insert) {
    die("Database Error: " . mysqli_error($conn));
}

/* ==========================
   SUCCESS → WALK-IN LIST
========================== */
header("Location: walkin_list.php?success=1"); // redirect sa list page
exit();
