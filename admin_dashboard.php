<?php
session_start();
include '../config.php';

// ================= ADMIN ONLY =================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ================= DATABASE COUNTS ================= */
$totalAppointments = 0;
$pendingAppointments = 0;
$activeBarbers = 0;
$walkInQueue = 0;   
$totalCustomers = 0;
$totalServices = 0;

// Tables
$appointmentsTable = "appointments";
$usersTable = "users"; // Barbers are users with role='barber'
$walkInTable = "walkin_queue";
$customersTable = "customers";
$servicesTable = "services";

// TOTAL APPOINTMENTS
$check = mysqli_query($conn, "SHOW TABLES LIKE '$appointmentsTable'");
if (mysqli_num_rows($check) > 0) {
    $totalAppointments = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) total FROM $appointmentsTable")
    )['total'] ?? 0;

    $pendingAppointments = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) total FROM $appointmentsTable WHERE status='Pending'")
    )['total'] ?? 0;
}

// ACTIVE BARBERS (role='barber')
$checkBarbers = mysqli_query($conn, "SHOW TABLES LIKE '$usersTable'");
if (mysqli_num_rows($checkBarbers) > 0) {
    $activeBarbers = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) total FROM $usersTable WHERE role='barber'")
    )['total'] ?? 0;
}

// WALK-IN QUEUE
$checkWalkIn = mysqli_query($conn, "SHOW TABLES LIKE '$walkInTable'");
if (mysqli_num_rows($checkWalkIn) > 0) {
    $walkInQueue = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) total FROM $walkInTable WHERE status='Waiting'")
    )['total'] ?? 0;
}

// CUSTOMERS
$checkCustomers = mysqli_query($conn, "SHOW TABLES LIKE '$customersTable'");
if (mysqli_num_rows($checkCustomers) > 0) {
    $totalCustomers = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) total FROM $customersTable")
    )['total'] ?? 0;
}

// SERVICES
$checkServices = mysqli_query($conn, "SHOW TABLES LIKE '$servicesTable'");
if (mysqli_num_rows($checkServices) > 0) {
    $totalServices = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) total FROM $servicesTable")
    )['total'] ?? 0;
}

/* ================= CREATE BARBER ACCOUNT ================= */
$barberError = '';
$barberSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_barber'])) {

    $fullname = trim($_POST['barber_fullname']);
    $username = trim($_POST['barber_username']);
    $password = trim($_POST['barber_password']);

    if (empty($fullname) || empty($username) || empty($password)) {
        $barberError = "Please fill in all fields.";
    } if else {
        // Check if username exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $barberError = "Username already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (fullname, username, password, role) VALUES (?, ?, ?, 'barber')");
            $stmt->bind_param("sss", $fullname, $username, $hashedPassword);

            if ($stmt->execute()) {
                $barberSuccess = "Barber account created successfully!";
            } else {
                $barberError = "Failed to create barber account.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | SNIPER Barbershop</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;font-family:Arial,Helvetica,sans-serif;}
body{margin:0;background:#f4f6f8;}


/* MAIN */
.main{margin-left:240px;padding:25px;}
.main h1{margin:0;font-size:26px;}
.subtitle{color:#666;margin-bottom:25px;}

/* CARDS */
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:25px;}
.card-link{text-decoration:none;}
.card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 5px 15px rgba(0,0,0,.08);transition:.2s;}
.card:hover{transform:translateY(-3px);}
.card h3{margin:0 0 10px;font-size:14px;color:#777;}
.card p{margin:0;font-size:28px;font-weight:bold;color:#222;}

/* SECTIONS */
.sections{display:grid;grid-template-columns:repeat(auto-fit,minmax(350px,1fr));gap:20px;}
.box-link{text-decoration:none;}
.box{background:#fff;height:280px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,.08);display:flex;align-items:center;justify-content:center;color:#555;font-size:18px;font-weight:600;transition:.2s;}
.box:hover{background:#f0f3f6;}

/* CREATE BARBER FORM */
.card form{display:grid;gap:10px;margin-top:10px;}
.card form input{padding:10px;border-radius:6px;border:1px solid #ddd;font-size:14px;width:100%;}
.card form button{padding:10px;background:#f4b400;border:none;border-radius:6px;cursor:pointer;font-weight:bold;}
.card form button:hover{background:#e0a800;color:#fff;}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<!-- MAIN -->
<div class="main">
    <h1>Dashboard Overview</h1>
    <p class="subtitle">Welcome back, Admin!</p>

    <!-- CARDS -->
    <div class="cards">
        <a class="card-link" href="appointments.php">
            <div class="card"><h3>Total Appointments</h3><p><?= $totalAppointments ?></p></div>
        </a>
        <a class="card-link" href="appointments.php?status=pending">
            <div class="card"><h3>Pending Appointments</h3><p><?= $pendingAppointments ?></p></div>
        </a>
        <a class="card-link" href="walkin_queue.php">
            <div class="card"><h3>Walk-In Queue</h3><p><?= $walkInQueue ?></p></div>
        </a>
        <a class="card-link" href="barbers.php">
            <div class="card"><h3>Active Barbers</h3><p><?= $activeBarbers ?></p></div>
        </a>
        <a class="card-link" href="customers.php">
            <div class="card"><h3>Total Customers</h3><p><?= $totalCustomers ?></p></div>
        </a>
        <a class="card-link" href="services.php">
            <div class="card"><h3>Total Services</h3><p><?= $totalServices ?></p></div>
        </a>
    </div>

    <!-- SECTIONS -->
    <div class="sections">
        <a class="box-link" href="appointments.php?today=1">
            <div class="box">Today's Appointments</div>
        </a>
        <a class="box-link" href="reports.php">
            <div class="box">System Overview</div>
        </a>
    </div>

    <!-- CREATE BARBER ACCOUNT -->
    <div class="card" style="margin-top:30px;">
        <h3>Create Barber Account</h3>

        <?php if($barberError): ?>
            <div style="color:red;margin-bottom:10px;"><?= $barberError ?></div>
        <?php endif; ?>
        <?php if($barberSuccess): ?>
            <div style="color:green;margin-bottom:10px;"><?= $barberSuccess ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="barber_fullname" placeholder="Full Name" required>
            <input type="text" name="barber_username" placeholder="Username" required>
            <input type="password" name="barber_password" placeholder="Password" required>
            <button type="submit" name="create_barber">Create Barber</button>
        </form>
    </div>
</div>

</body>
</html>
