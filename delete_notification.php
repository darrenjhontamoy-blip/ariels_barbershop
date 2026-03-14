<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json; charset=UTF-8');

// Prevent any accidental output
ob_clean();

/* =========================
   BARBER ONLY
========================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'barber') {
    echo json_encode([
        'success' => false,
        'msg' => 'Unauthorized'
    ]);
    exit;
}

/* =========================
   VALIDATE INPUT
========================= */
$barberId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$notifId  = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($barberId <= 0 || $notifId <= 0) {
    echo json_encode([
        'success' => false,
        'msg' => 'Invalid request'
    ]);
    exit;
}

/* =========================
   DELETE NOTIFICATION
========================= */
$stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'msg' => 'Prepare failed'
    ]);
    exit;
}

$stmt->bind_param("ii", $notifId, $barberId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'msg' => 'Notification not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'msg' => 'Execution failed'
    ]);
}

$stmt->close();
$conn->close();
exit;