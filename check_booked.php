<?php
session_start();
include '../config.php';

// CUSTOMER ONLY
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer'){
    echo json_encode([]);
    exit;
}

$barber = $_GET['barber'] ?? '';
$date   = $_GET['date'] ?? '';

if(!$barber || !$date){
    echo json_encode([]);
    exit;
}

$email = $_SESSION['email_address'] ?? '';

// =========================
// GET BOOKED TIMES
// =========================
$booked = [];

// 1️⃣ Times booked by ANYONE for this barber on this date
$stmt = $conn->prepare("
    SELECT appointment_time 
    FROM appointments 
    WHERE barber_name = ? 
    AND appointment_date = ? 
    AND status = 'Pending'
");
$stmt->bind_param("ss", $barber, $date);
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()){
    $booked[] = date('H:i', strtotime($row['appointment_time']));
}

// 2️⃣ Optional: block same customer from double booking at same time with ANY barber
$stmt2 = $conn->prepare("
    SELECT appointment_time 
    FROM appointments 
    WHERE email_address = ? 
    AND appointment_date = ? 
    AND status = 'Pending'
");
$stmt2->bind_param("ss", $email, $date);
$stmt2->execute();
$result2 = $stmt2->get_result();
while($row = $result2->fetch_assoc()){
    $time = date('H:i', strtotime($row['appointment_time']));
    if(!in_array($time, $booked)){
        $booked[] = $time;
    }
}

// RETURN JSON
echo json_encode($booked);
