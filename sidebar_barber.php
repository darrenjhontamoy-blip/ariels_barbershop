<?php
// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../config.php';

// Security: barber only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'barber') {
    header("Location: ../login.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
$barberName = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Barber';
?>

<style>
.sidebar {
    width: 270px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    padding: 50px 25px;
    font-family: 'Poppins', sans-serif;
    display: flex;
    flex-direction: column;
    color: #fff;
    position: fixed;

    background: linear-gradient(180deg, 
        #0b3d91 0%, 
        #1e3a8a 40%, 
        #7f1d1d 75%, 
        #c1121f 100%
    );

    box-shadow: 6px 0 25px rgba(0,0,0,0.4);
    border-right: 3px solid rgba(255,255,255,0.3);
}

/* Glass overlay */
.sidebar::before{
    content:'';
    position:absolute;
    inset:0;
    background: rgba(0,0,0,0.25);
    backdrop-filter: blur(10px);
    z-index:-1;
}

/* ===== BRAND (2 LINES) ===== */
.sidebar .brand {
    text-align: center;
    font-size: 22px;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: 1px;
    margin-bottom: 20px;

    background: linear-gradient(90deg, #8ab4ff, #ff9aa2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* ===== BIG WELCOME ===== */
.sidebar .welcome {
    text-align: center;
    font-size: 30px;      /* medyo mas maliit */
    font-weight: 800;
    letter-spacing: 2px;  /* dating 6px, sobrang laki */
    margin-bottom: 15px;  /* mas dikit sa name */

    background: linear-gradient(90deg, #8ab4ff, #ff9aa2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* ===== NAME ===== */
.sidebar .username {
    text-align: center;
    font-size: 20px;
    font-weight: 600;
    color: #e2e8f0;
    margin-bottom: 50px;
}

/* ===== MENU LINKS ===== */
.sidebar a {
    display: block;
    padding: 14px 18px;
    margin-bottom: 14px;
    color: #f1f5f9;
    text-decoration: none;
    border-radius: 14px;
    background: rgba(255,255,255,0.08);
    font-size: 15px;
    font-weight: 500;
    transition: background 0.3s ease, color 0.3s ease;
}

.sidebar a:hover {
    background: linear-gradient(90deg, #2563eb, #dc2626);
}

.sidebar a.active {
    background: linear-gradient(90deg, #1d4ed8, #b91c1c);
    font-weight: 600;
}

/* Logout */
.sidebar a.logout {
    margin-top: auto;
    background: linear-gradient(90deg, #ef4444, #7f1d1d);
    text-align: center;
    font-weight: 600;
}

.sidebar a.logout:hover {
    background: linear-gradient(90deg, #dc2626, #991b1b);
}
</style>

<div class="sidebar">

    <!-- HEADER STYLE LIKE YOUR IMAGE -->
    <div class="brand">
        Ariel's<br>Barbershop
    </div>

    <div class="welcome">WELCOME</div>

    <div class="username">
        <?= htmlspecialchars($barberName) ?>
    </div>

    <!-- MENU -->
    <a href="barber_dashboard.php" class="<?= $current_page == 'barber_dashboard.php' ? 'active' : '' ?>">
        Dashboard
    </a>

    <a href="barber_appointments.php" class="<?= $current_page == 'barber_appointments.php' ? 'active' : '' ?>">
        My Appointments
    </a>

    <a href="barber_schedule.php" class="<?= $current_page == 'barber_schedule.php' ? 'active' : '' ?>">
        My Schedule
    </a>

    <a href="barber_profile.php" class="<?= $current_page == 'barber_profile.php' ? 'active' : '' ?>">
        Profile Settings
    </a>

    <a href="../logout.php" class="logout">Logout</a>

</div>