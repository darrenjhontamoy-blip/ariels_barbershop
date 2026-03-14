<?php
session_start();
include '../config.php';

/* ADMIN ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* CHECK SERVICE */
if (!isset($_GET['service'])) {
    header("Location: services.php");
    exit();
}

$service_name = mysqli_real_escape_string($conn, $_GET['service']);

/* FETCH SERVICE DATA */
$result = mysqli_query($conn, "SELECT * FROM services WHERE service_name='$service_name'");
$service = mysqli_fetch_assoc($result);

if (!$service) {
    header("Location: services.php");
    exit();
}

/* UPDATE SERVICE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = mysqli_real_escape_string($conn, $_POST['service_name']);
    $duration = (int)$_POST['duration'];
    $price = (float)$_POST['price'];

    mysqli_query($conn, "
        UPDATE services SET
            service_name = '$new_name',
            duration = $duration,
            price = $price
        WHERE service_name = '$service_name'
    ");

    header("Location: services.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Service | Ariel's Barbershop</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif}
body{
    margin:0;
    background:#f4f6f9;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}
.card{
    background:#fff;
    border-radius:14px;
    box-shadow:0 6px 20px rgba(0,0,0,.12);
    width:400px;
    padding:30px;
}
.card h2{
    margin:0 0 20px;
    font-size:24px;
    text-align:center;
}
label{
    display:block;
    margin-bottom:6px;
    font-weight:600;
    font-size:14px;
}
input{
    width:100%;
    padding:12px 14px;
    margin-bottom:15px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:14px;
}
input:focus{
    border-color:#4f46e5;
    outline:none;
}
button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#4f46e5;
    color:#fff;
    font-size:16px;
    cursor:pointer;
}
button:hover{
    background:#4338ca;
}
.back-link{
    display:block;
    margin-bottom:15px;
    font-size:13px;
    color:#4f46e5;
    text-decoration:none;
}
.back-link:hover{text-decoration:underline}
</style>
</head>
<body>

<div class="card">
    <a href="services.php" class="back-link">← Back to Services</a>
    <h2>Edit Service</h2>

    <form method="POST">
        <label>Service Name</label>
        <input type="text" name="service_name"
               value="<?= htmlspecialchars($service['service_name']) ?>" required>

        <label>Duration (minutes)</label>
        <input type="number" name="duration"
               value="<?= htmlspecialchars($service['duration']) ?>" required>

        <label>Price (₱)</label>
        <input type="number" step="0.01" name="price"
               value="<?= htmlspecialchars($service['price']) ?>" required>

        <button type="submit">Update Service</button>
    </form>
</div>

</body>
</html>
