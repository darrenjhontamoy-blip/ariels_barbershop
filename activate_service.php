<?php
session_start();
include '../config.php';

/* ADMIN ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* CHECK SERVICE NAME */
if (isset($_GET['service'])) {
    $service = mysqli_real_escape_string($conn, $_GET['service']);

    mysqli_query(
        $conn,
        "UPDATE services SET status='Available' WHERE service_name='$service'"
    );
}

/* REDIRECT BACK */
header("Location: services.php");
exit();
