<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = (int)$_GET['id'];
$q = mysqli_query($conn,"SELECT * FROM appointments WHERE id=$id");
$row = mysqli_fetch_assoc($q);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    mysqli_query($conn,"
        UPDATE appointments SET
        customer_name='".mysqli_real_escape_string($conn, $_POST['customer'])."',
        service='".mysqli_real_escape_string($conn, $_POST['service'])."',
        barber_name='".mysqli_real_escape_string($conn, $_POST['barber'])."',
        appointment_date='".mysqli_real_escape_string($conn, $_POST['date'])."',
        appointment_time='".mysqli_real_escape_string($conn, $_POST['time'])."',
        status='".mysqli_real_escape_string($conn, $_POST['status'])."'
        WHERE id=$id
    ");
    header("Location: appointments.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Edit Appointment</title>
<style>
    body {
        margin: 0;
        background: #f4f6f9;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .container {
        background: white;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        width: 360px;
        box-sizing: border-box;
        text-align: center;
    }
    h2 {
        margin-bottom: 24px;
        color: #222;
        font-weight: 700;
    }
    form input[type="text"],
    form input[type="date"],
    form input[type="time"],
    form select {
        width: 100%;
        padding: 10px 12px;
        margin: 10px 0 20px 0;
        border: 1.8px solid #ccc;
        border-radius: 8px;
        font-size: 15px;
        transition: border-color 0.3s ease;
    }
    form input[type="text"]:focus,
    form input[type="date"]:focus,
    form input[type="time"]:focus,
    form select:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 6px #2563ebaa;
    }
    button {
        background-color: #2563eb;
        color: white;
        border: none;
        padding: 12px 0;
        width: 100%;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #1e40af;
    }
</style>
</head>
<body>

<div class="container">
    <h2>Edit Appointment</h2>
    <form method="POST" autocomplete="off">
        <input type="text" name="customer" placeholder="Customer Name" value="<?= htmlspecialchars($row['customer_name']) ?>" required>
        <input type="text" name="service" placeholder="Service" value="<?= htmlspecialchars($row['service']) ?>" required>
        <input type="text" name="barber" placeholder="Barber Name" value="<?= htmlspecialchars($row['barber_name']) ?>" required>
        <input type="date" name="date" value="<?= htmlspecialchars($row['appointment_date']) ?>" required>
        <input type="time" name="time" value="<?= htmlspecialchars($row['appointment_time']) ?>" required>

        <select name="status" required>
            <option value="Pending" <?= $row['status']=='Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Completed" <?= $row['status']=='Completed' ? 'selected' : '' ?>>Completed</option>
            <option value="Cancelled" <?= $row['status']=='Cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <button type="submit">Update</button>
    </form>
</div>

</body>
</html>
