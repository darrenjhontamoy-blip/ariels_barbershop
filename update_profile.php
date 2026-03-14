<?php
session_start();
include '../config.php';

// CUSTOMER AUTH CHECK
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer'){
    header("Location: ../login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? ''); // match your DB column
$phone = trim($_POST['phone'] ?? '');
$dob = trim($_POST['dob'] ?? '');

// Optional: phone validation
if(!preg_match('/^09\d{9}$/', $phone)){
    $_SESSION['error'] = "Invalid phone number. Must start with 09 and be 11 digits.";
    header("Location: profile_settings.php?tab=profile");
    exit();
}

// Handle profile photo upload
$uploadDir = "../uploads/";
if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$profile_photo = null;
if(!empty($_FILES['photo']['name'])){
    $fileName = time().'_'.basename($_FILES['photo']['name']);
    $targetFile = $uploadDir.$fileName;
    if(move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)){
        $profile_photo = $fileName;
    }
}

// Build query dynamically depending on uploaded photo
if($profile_photo){
    $stmt = $conn->prepare("
        UPDATE users
        SET fullname = ?, email_address = ?, phone = ?, dob = ?, profile_photo = ?
        WHERE id = ?
    ");
    $stmt->bind_param("sssssi", $fullname, $email, $phone, $dob, $profile_photo, $userId);
} else {
    $stmt = $conn->prepare("
        UPDATE users
        SET fullname = ?, email_address = ?, phone = ?, dob = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssssi", $fullname, $email, $phone, $dob, $userId);
}

$stmt->execute();
$stmt->close();

// Update session
$_SESSION['fullname'] = $fullname;

// Redirect back
header("Location: profile_settings.php?tab=profile");
exit();
?>
