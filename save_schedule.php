<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../config.php';

/* ======================
   ADMIN ONLY
====================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ======================
   GET POST DATA
====================== */
$barberId  = (int) ($_POST['barber_id'] ?? 0);
$workDate  = $_POST['work_date'] ?? '';
$workDay   = $_POST['work_day'] ?? '';
$startTime = $_POST['start_time'] ?? '';

$endTime = '21:00'; // Force end time 9:00 PM

/* ======================
   VALIDATION
====================== */
if ($barberId <= 0 || !$workDate || !$startTime) {
    die("Missing required data");
}

if ($startTime >= $endTime) {
    die("Start time must be earlier than 9:00 PM");
}

/* ======================
   CHECK IF BARBER EXISTS
====================== */
$res = mysqli_query($conn, "SELECT fullname FROM users WHERE id = $barberId AND role='barber'");
if (!$res || mysqli_num_rows($res) === 0) {
    die("Invalid barber selected");
}

/* ======================
   CHECK IF SCHEDULE EXISTS
====================== */
$check = mysqli_query($conn, "
    SELECT id FROM barber_schedule
    WHERE barber_id = $barberId
    AND work_date = '$workDate'
");

$barberName = mysqli_fetch_assoc($res)['fullname'];

if (mysqli_num_rows($check) > 0) {
    $sql = "
        UPDATE barber_schedule
        SET
            barber_name = '$barberName',
            work_day   = '$workDay',
            start_time = '$startTime',
            end_time   = '$endTime'
        WHERE barber_id = $barberId
        AND work_date = '$workDate'
    ";
} else {
    $sql = "
        INSERT INTO barber_schedule
        (barber_id, barber_name, work_date, work_day, start_time, end_time)
        VALUES
        ($barberId, '$barberName', '$workDate', '$workDay', '$startTime', '$endTime')
    ";
}

/* ======================
   EXECUTE QUERY
====================== */
mysqli_query($conn, $sql) or die("Database error: " . mysqli_error($conn));

/* ======================
   SEND NOTIFICATION
====================== */
$barberName = mysqli_fetch_assoc($res)['fullname'];
$message = "📅 You have a schedule on $workDate ($workDay) from $startTime–$endTime";
$messageEsc = mysqli_real_escape_string($conn, $message);

mysqli_query($conn, "
    INSERT INTO notifications (user_id, message, created_at)
    VALUES ($barberId, '$messageEsc', NOW())
");

/* ======================
   REDIRECT BACK
====================== */
header("Location: calendar.php?success=1");
exit();
?>
