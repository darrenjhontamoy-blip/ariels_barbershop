<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(['success'=>false]);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$name = $_SESSION['fullname'];

if ($id > 0) {
    $stmt = $conn->prepare("
        UPDATE appointments 
        SET customer_notif_deleted = 1
        WHERE id = ? AND customer_name = ?
    ");
    $stmt->bind_param("is", $id, $name);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false]);
}
exit;