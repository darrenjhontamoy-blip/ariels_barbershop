<?php
include 'config.php';

// Admin credentials
$fullname = 'Admin Ariel';
$username = 'admin';
$password = 'admin123';
$role = 'admin';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert admin into database
$stmt = $conn->prepare("INSERT INTO users (fullname, username, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $fullname, $username, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "Admin account created successfully!<br>";
    echo "Username: $username<br>";
    echo "Password: $password";
} else {
    echo "Error: " . $stmt->error;
}
?>
