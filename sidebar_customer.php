<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../config.php'; // Database connection

// Get customer name
$customerName = $_SESSION['fullname'] ?? 'Customer';
$customerName = ucwords(strtolower($customerName));
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
/* ===== STABLE SIDEBAR ===== */
.sidebar-customer {
    position: fixed;
    top: 0;
    left: 0;
    width: 270px;
    height: 100%;
    background: linear-gradient(180deg, #0b3d91 0%, #1e3a8a 40%, #7f1d1d 75%, #c1121f 100%);
    color: #fff;
    padding: 40px 25px;
    font-family: 'Poppins','Segoe UI', Arial, sans-serif;
    box-shadow: none;
    z-index: 999;

    /* Border to separate sidebar from main content */
    border-right: 3px solid rgba(255, 255, 255, 0.3);
}

/* Glass effect overlay */
.sidebar-customer::before{
    content:'';
    position:absolute;
    inset:0;
    background: rgba(0,0,0,0.25);
    backdrop-filter: blur(10px);
    z-index:-1;
}
/* ===== BRAND TITLE ===== */
.sidebar-customer h2 {
    text-align: center;
    font-size: 22px;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 15px;

    background: linear-gradient(90deg, #60a5fa, #f87171);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
/* ===== WELCOME TEXT ===== */
.sidebar-customer .welcome {
    font-size: 25px;
    font-weight: 800;
    text-transform: none;
    letter-spacing: 3px;
    text-align: center;
    background: linear-gradient(90deg, #60a5fa, #f87171);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 8px;
}

.sidebar-customer .username {
    font-size: 18px;
    font-weight: 600;
    text-align: center;
    opacity: 0.95;
}

.sidebar-customer .decorative-line {
    width: 70px;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #ef4444);
    margin: 18px auto 30px auto;
    border-radius: 10px;
}

/* ===== MENU ===== */
.sidebar-customer ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-customer li {
    margin-bottom: 14px;
}

.sidebar-customer a {
    display: block;
    padding: 14px 18px;
    color: #f1f5f9;
    text-decoration: none;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.08);
    font-size: 15px;
    font-weight: 500;
    transition: background 0.3s ease, color 0.3s ease;
    transform: none; /* Ensure no scaling or zooming */
    box-shadow: none; /* Ensure no shadow effect */
}

/* Sidebar hover effect */
.sidebar-customer a:hover {
    background: linear-gradient(90deg, #2563eb, #dc2626);
    box-shadow: none; /* No shadow effect */
    transform: none; /* No zoom or movement */
}

/* Active page link */
.sidebar-customer a.active { 
    background: linear-gradient(90deg, #1d4ed8, #b91c1c);
    font-weight: 600;
    box-shadow: none; /* No shadow effect */
    transform: none; /* No scaling or zooming */
}
/* Logout Button */
.sidebar-customer a.logout {
    background: linear-gradient(90deg, #ef4444, #7f1d1d);
    color: #fff;
    text-align: center;
    font-weight: 600;
    margin-top: 40px;
}

.sidebar-customer a.logout:hover {
    background: linear-gradient(90deg, #dc2626, #991b1b);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .sidebar-customer {
        width: 200px;
        padding: 30px 20px;
    }
}
</style>

<!-- Sidebar -->
<div class="sidebar-customer">
    
    <h2>Ariel's Barbershop</h2>
    <div class="welcome">Welcome</div>
    <div class="username"><?= htmlspecialchars($customerName) ?></div>
    <div class="decorative-line"></div>

    <ul>
        <li>
            <a href="book_appointment.php" class="<?= $current_page=='book_appointment.php'?'active':'' ?>">
                Book Appointment
            </a>
        </li>
        <li>
            <a href="appointments.php" class="<?= $current_page=='appointments.php'?'active':'' ?>">
                My Appointments
            </a>
        </li>
        <li>
            <a href="profile_settings.php" class="<?= $current_page=='profile_settings.php'?'active':'' ?>">
                Profile Settings
            </a>
        </li>
        <li>
            <a href="../logout.php" class="logout">Logout</a>
        </li>
    </ul>
</div>
