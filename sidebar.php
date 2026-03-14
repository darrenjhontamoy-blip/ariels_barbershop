<?php
$current_page = basename($_SERVER['PHP_SELF']);
$adminName = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Admin';
?>
<style>
body {
    margin: 0;
    font-family: 'Poppins', Arial, sans-serif;
}

/* ==================== SIDEBAR BASE ==================== */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 270px;
    height: 100vh;
    background: linear-gradient(180deg, #0b3d91 0%, #1e3a8a 40%, #7f1d1d 75%, #c1121f 100%);
    color: #fff;
    padding: 45px 25px;
    box-shadow: 6px 0 25px rgba(0,0,0,0.4);
    border-right: 3px solid rgba(255,255,255,0.3);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

/* Glass effect */
.sidebar::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.25);
    backdrop-filter: blur(10px);
    z-index: -1;
}

/* ==================== HEADER STYLE ==================== */

/* Brand */
.sidebar .brand {
    text-align: center;
    font-size: 22px;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: 2px;
    margin-bottom: 25px;

    background: linear-gradient(90deg, #60a5fa, #f87171);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Welcome */
.sidebar .welcome {
    text-align: center;
    font-size: 28px;
    font-weight: 900;
    letter-spacing: 2px;
    margin-bottom: 8px;

    background: linear-gradient(90deg, #60a5fa, #f87171);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Username */
.sidebar .username {
    text-align: center;
    font-size: 16px;
    font-weight: 600;
    color: #e2e8f0;
    margin-bottom: 35px;
}

/* ==================== LINKS ==================== */
.sidebar a {
    display: block;
    padding: 14px 18px;
    border-radius: 14px;
    color: #f1f5f9;
    text-decoration: none;
    font-weight: 500;
    background: rgba(255,255,255,0.08);
    transition: background 0.3s ease, color 0.3s ease;
}

.sidebar a:hover {
    background: linear-gradient(90deg, #2563eb, #dc2626);
}

.sidebar a.active {
    background: linear-gradient(90deg, #1d4ed8, #b91c1c);
    font-weight: 600;
}

.sidebar a.logout {
    margin-top: 30px;
    background: linear-gradient(90deg, #ef4444, #7f1d1d);
    text-align: center;
    font-weight: 600;
}

.sidebar a.logout:hover {
    background: linear-gradient(90deg, #dc2626, #991b1b);
}

/* Responsive */
@media(max-width:768px){
    .sidebar {
        width: 200px;
        padding: 30px 20px;
        left: -220px;
        transition: 0.3s ease;
    }
    .sidebar.active {
        left: 0;
    }
}
</style>

<div class="sidebar">

    <!-- HEADER SAME AS CUSTOMER -->
    <div class="brand">
        Ariel's<br>Barbershop
    </div>

    <div class="welcome">WELCOME</div>

    <div class="username">
        <?= htmlspecialchars($adminName) ?>
    </div>

    <!-- MENU -->
    <a href="dashboard.php" class="<?= $current_page=='dashboard.php'?'active':'' ?>">Dashboard</a>
    <a href="calendar.php" class="<?= $current_page=='calendar.php'?'active':'' ?>">Calendar</a>
    <a href="online_appointments.php" class="<?= $current_page=='online_appointments.php'?'active':'' ?>">Online Appointments</a>
    <a href="walkin_queue.php" class="<?= $current_page=='walkin_queue.php'?'active':'' ?>">Walk-in Appointments</a>
    <a href="customers.php" class="<?= $current_page=='customers.php'?'active':'' ?>">Customers</a>
    <a href="barbers.php" class="<?= $current_page=='barbers.php'?'active':'' ?>">Barbers</a>
    <a href="users.php" class="<?= $current_page=='users.php'?'active':'' ?>">Users</a>
    <a href="services.php" class="<?= $current_page=='services.php'?'active':'' ?>">Services</a>
    <a href="payments.php" class="<?= $current_page=='payments.php'?'active':'' ?>">Payments</a>
    <a href="reports.php" class="<?= $current_page=='reports.php'?'active':'' ?>">Reports</a>
    <a href="archive.php" class="<?= $current_page=='archive.php'?'active':'' ?>">Archive</a>
    <a href="../logout.php" class="logout">Logout</a>

</div>