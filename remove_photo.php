<?php
session_start();
include '../config.php';

// CUSTOMER AUTH CHECK
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer'){
    header("Location: ../login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

// Fetch current photo filename
$result = mysqli_query($conn, "SELECT profile_photo FROM users WHERE id = $userId");
$user = mysqli_fetch_assoc($result);
$currentPhoto = $user['profile_photo'] ?? null;

// Delete the current photo file if exists and not default
$uploadDir = "../uploads/";
if($currentPhoto && $currentPhoto !== 'default.png' && file_exists($uploadDir.$currentPhoto)){
    unlink($uploadDir.$currentPhoto);
}

// Update database to remove photo (set NULL or default.png)
$photoColumnCheck = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_photo'");
if(mysqli_num_rows($photoColumnCheck) > 0){
    mysqli_query($conn, "UPDATE users SET profile_photo = 'default.png' WHERE id = $userId");
}

// Redirect back to profile page
header("Location: profile.php?tab=profile");
exit();
?>
