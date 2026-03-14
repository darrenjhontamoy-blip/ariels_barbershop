<?php
session_start();
include '../config.php';

if (
    isset($_SESSION['user_id'], $_SESSION['role']) &&
    $_SESSION['role'] === 'barber'
) {
    $id = (int) $_SESSION['user_id'];

    $stmt = $conn->prepare(
        "UPDATE users 
         SET last_activity = NOW(), status='active' 
         WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}
