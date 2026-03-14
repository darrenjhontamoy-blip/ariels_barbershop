<?php
include '../config.php';

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("
    SELECT appointment_time
    FROM appointments
    WHERE appointment_date = ?
    AND barber_name = ?
");
$stmt->bind_param("ss", $data['date'], $data['barber']);
$stmt->execute();
$result = $stmt->get_result();

$times=[];
while($r=$result->fetch_assoc()){
    $times[] = substr($r['appointment_time'],0,5);
}
echo json_encode($times);
