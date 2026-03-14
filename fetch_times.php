<?php
include '../config.php';

$barberId = (int) ($_GET['barber_id'] ?? 0);
$date     = $_GET['date'] ?? '';

if ($barberId <= 0 || !$date) {
    echo json_encode([]);
    exit();
}

/* DEFINE TIME SLOTS (9AM–5PM) */
$slots = [];
for ($h = 9; $h <= 17; $h++) {
    $slots[] = sprintf("%02d:00:00", $h);
}

/* FETCH BOOKED TIMES */
$stmt = $conn->prepare("
    SELECT appointment_time 
    FROM appointments 
    WHERE barber_id = ? 
    AND appointment_date = ?
");
$stmt->bind_param("is", $barberId, $date);
$stmt->execute();
$res = $stmt->get_result();

$booked = [];
while ($r = $res->fetch_assoc()) {
    $booked[] = $r['appointment_time'];
}

/* RESPONSE */
$response = [];
foreach ($slots as $slot) {
    $response[] = [
        'time'   => $slot,
        'booked' => in_array($slot, $booked)
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
