<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['error'] = "Please fill in all fields.";
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT id, fullname, password, role
    FROM users
    WHERE username = ?
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {

    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {

        $role = strtolower(trim($user['role']));

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role']     = $role;

        // ✅ ADMIN REDIRECT (FIXED)
        if ($role === 'admin') {
            header("Location: /ariels_barbershop/admin_dashboard.php");
            exit();
        }

        // ✅ OWNER REDIRECT
        if ($role === 'owner') {
            header("Location: /ariels_barbershop/home.php");
            exit();
        }

        // ❌ UNKNOWN ROLE
        session_destroy();
        session_start();
        $_SESSION['error'] = "Access denied.";
        header("Location: login.php");
        exit();

    } else {
        $_SESSION['error'] = "Invalid password.";
    }

} else {
    $_SESSION['error'] = "User not found.";
}

header("Location: login.php");
exit();
