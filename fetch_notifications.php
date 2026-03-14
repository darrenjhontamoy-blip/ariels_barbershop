<?php
session_start();
include '../config.php';
header('Content-Type: application/json');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'barber'){
    echo json_encode(['success'=>false,'msg'=>'Unauthorized']);
    exit;
}

$barberId = (int)($_SESSION['user_id'] ?? 0);

// Fetch latest 10 notifications
$res = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id=$barberId ORDER BY created_at DESC LIMIT 10");
$notifications = [];
while($row = mysqli_fetch_assoc($res)){
    $notifications[] = $row;
}

// Count unread
$resCount = mysqli_query($conn, "SELECT COUNT(*) AS total FROM notifications WHERE user_id=$barberId AND status='unread'");
$notifCount = (int)mysqli_fetch_assoc($resCount)['total'];

echo json_encode(['success'=>true, 'notifications'=>$notifications, 'notifCount'=>$notifCount]);
