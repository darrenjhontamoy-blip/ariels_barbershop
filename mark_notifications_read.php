<?php
session_start();
include '../config.php';
header('Content-Type: application/json');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'barber'){
    echo json_encode(['success'=>false,'msg'=>'Unauthorized']);
    exit;
}

$barberId = (int)($_SESSION['user_id'] ?? 0);
mysqli_query($conn, "UPDATE notifications SET status='read' WHERE user_id=$barberId AND status='unread'");

echo json_encode(['success'=>true]);
