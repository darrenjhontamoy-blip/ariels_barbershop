<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

/* =========================
   SET BARBER ONLINE_STATUS TO OFFLINE ON LOGOUT
========================= */
if (
    isset($_SESSION['user_id'], $_SESSION['role']) &&
    $_SESSION['role'] === 'barber'
) {
    $userId = (int) $_SESSION['user_id'];

    $stmt = $conn->prepare(
        "UPDATE users SET online_status = 'Offline' WHERE id = ?"
    );
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }
}

/* =========================
   PREVENT BACK BUTTON (CACHE)
========================= */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

/* =========================
   DESTROY SESSION
========================= */
session_regenerate_id(true); //
$_SESSION = [];
session_unset();
session_destroy();

/* =========================
   REDIRECT TO LOGIN PAGE
========================= */
header("Location: /ariels_barbershop/login.php");
exit();