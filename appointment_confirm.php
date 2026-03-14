<?php
session_start();
include '../config.php'; // adjust path if needed

if (!isset($_GET['id'])) {
    echo "No appointment ID provided!";
    exit();
}

$appointmentId = intval($_GET['id']);

// Get appointment info
$stmt = $conn->prepare("SELECT * FROM appointments WHERE id=?");
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if (!$appointment) {
    echo "Appointment not found!";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointment Confirmed</title>
</head>
<body>
<h1>✅ Appointment Confirmed!</h1>
<p>Customer: <?= htmlspecialchars($appointment['customer_name']) ?></p>
<p>Service: <?= htmlspecialchars($appointment['service']) ?></p>
<p>Barber: <?= htmlspecialchars($appointment['barber_name']) ?></p>
<p>Date: <?= htmlspecialchars($appointment['appointment_date']) ?></p>
<p>Time: <?= htmlspecialchars($appointment['appointment_time']) ?></p>
<p>Price: ₱<?= htmlspecialchars($appointment['price']) ?></p>
<a href="book_appointment.php">Back to Booking</a>
</body>
</html>
