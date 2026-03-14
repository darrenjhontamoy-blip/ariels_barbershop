<?php
session_start();
include '../config.php';

/* ADMIN ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$fullname || !$username || !$email || !$password) {
        $message = "All fields are required.";
    } else {
        // check duplicate username or email
        $check = mysqli_query($conn, "
            SELECT id FROM barbers 
            WHERE username='$username' OR email='$email'
        ");

        if (mysqli_num_rows($check) > 0) {
            $message = "Username or email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $insert = mysqli_query($conn, "
                INSERT INTO barbers (fullname, username, email, password) 
                VALUES ('$fullname', '$username', '$email', '$hashed')
            ");

            if ($insert) {
                $message = "Barber account successfully created!";
            } else {
                $message = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>
