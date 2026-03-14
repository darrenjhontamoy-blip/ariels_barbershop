<?php
session_start();
include 'config.php';

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// Inputs
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['error'] = "Please fill in all fields.";
    header("Location: login.php");
    exit();
}

// Fetch user
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

        // 🔥 ROLE-BASED DASHBOARD
        switch ($role) {
            case 'admin':
                header("Location: admin/dashboard.php");
                break;

            case 'barber':
                header("Location: barber/dashboard.php");
                break;

            case 'customer':
                header("Location: customer/dashboard.php");
                break;

            default:
                session_destroy();
                session_start();
                $_SESSION['error'] = "Invalid role.";
                header("Location: login.php");
                break;
        }

        exit();

    } else {
        $_SESSION['error'] = "Invalid password.";
    }

} else {
    $_SESSION['error'] = "User not found.";
}

header("Location: login.php");
exit();
