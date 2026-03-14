<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
date_default_timezone_set('Asia/Manila');
include '../config.php';

/* ====================== ADMIN AUTH CHECK ====================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ====================== ADMIN NOTIFICATIONS ====================== */
$notifCount = 0;
$notifications = [];

$qNotif = mysqli_query($conn, "
    SELECT id, message, created_at 
    FROM notifications 
    WHERE user_id = 1 AND status = 'unread'
    ORDER BY created_at DESC
");

if ($qNotif && mysqli_num_rows($qNotif) > 0) {
    $notifCount = mysqli_num_rows($qNotif);
    while ($row = mysqli_fetch_assoc($qNotif)) {
        $notifications[] = $row;
    }
}
/* ====================== DEFAULT COUNTERS ====================== */
$totalAppointments     = 0;
$pendingAppointments   = 0;
$completedAppointments = 0;
$cancelledAppointments = 0;
$todaysAppointments    = 0;
$walkInCount           = 0;

/* ====================== APPOINTMENTS COUNTS ====================== */
$q1 = mysqli_query($conn, "SHOW TABLES LIKE 'appointments'");
if ($q1 && mysqli_num_rows($q1) > 0) {

    $totalAppointments = (int)mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT COUNT(*) total FROM appointments")
    )['total'];

    $pendingAppointments = (int)mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT COUNT(*) total FROM appointments WHERE status='Pending'")
    )['total'];

    $completedAppointments = (int)mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT COUNT(*) total FROM appointments WHERE status='Completed'")
    )['total'];

    $cancelledAppointments = (int)mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT COUNT(*) total FROM appointments WHERE status='Cancelled'")
    )['total'];

    $todaysAppointments = (int)mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT COUNT(*) total FROM appointments WHERE appointment_date = CURDATE()")
    )['total'];
}

/* ====================== WALK-IN COUNT ====================== */
$q2 = mysqli_query($conn, "SHOW TABLES LIKE 'walkin_queue'");
if ($q2 && mysqli_num_rows($q2) > 0) {
    $walkInCount = (int)mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT COUNT(*) total FROM walkin_queue WHERE status='Pending'")
    )['total'];
}


$q3 = mysqli_query($conn, "SHOW TABLES LIKE 'appointments'");
if ($q3 && mysqli_num_rows($q3) > 0) {
    $walkInCount = (int)mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT COUNT(*) total FROM appointments WHERE appointment_source='walkin' AND status= 'Pending'")
    )['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;margin:0;padding:0}
body{background:#f4f6f9}
.main{margin-left:240px;padding:30px 40px}

/* HEADER */
.top-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#e3e6ff;
    color:#3f51b5;
    padding:20px 30px;
    border-radius:14px;
    box-shadow:0 6px 18px rgba(0,0,0,.12);
    margin-bottom:25px;
}

/* CARDS */
.cards{display:flex;gap:20px;flex-wrap:wrap;margin-bottom:20px}
.card {
    position: relative;
    padding: 20px;
    border-radius: 18px;
    text-align: center;
    flex: 1;
    min-width: 220px;
    transition: .3s;
    text-decoration: none;
    color: #111;

    border: 3px solid transparent; /* Important para sa gradient border */
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(white, white), /* loob ng card */
        linear-gradient(135deg, #3b82f6, #a855f7, #ef4444); /* border gradient */
    box-shadow: 0 6px 18px rgba(0,0,0,.12);
}
.card:hover{transform:translateY(-5px)}
.card h3{text-transform:uppercase;font-size:14px;color:#555}
.card p{font-size:30px;font-weight:900}

/* CHART */
.chart-container{
    background:#0f172a;
    padding:28px;
    border-radius:22px;
    box-shadow:0 12px 30px rgba(0,0,0,.25);
    width:100%;
    margin-top:30px;
}
.chart-container h2{
    color:#fff;
    margin-bottom:18px;
    font-size:20px;
}
.chart-container canvas{max-height:300px}

/* RESPONSIVE */
@media(max-width:768px){
    .main{margin-left:0;padding:20px}
    .cards{flex-direction:column}
}
</style>
<style>
*{
    box-sizing:border-box;
    font-family:'Segoe UI',Tahoma,sans-serif;
}

/* ===== BACKGROUND SAME AS CUSTOMER ===== */
body{
    margin:0;
    background: linear-gradient(135deg, 
        #0b3d91 0%, 
        #1e3a8a 40%, 
        #7f1d1d 70%, 
        #c1121f 100%);
    min-height:100vh;
}

.main{
    margin-left:270px;
    padding:30px 40px 30px 30px;
}

/* ===== HEADER ===== */
.header{0
    display:flex;
    align-items:center;
    gap:16px;
    margin-bottom:22px;
    position:relative;
}

.welcome-card{
    flex:1;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(8px);
    padding:18px 26px;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,.15);
    position:relative;
}

.welcome-card::before{
    content:'';
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:4px;
    border-radius:18px 18px 0 0;
    background:linear-gradient(90deg,#6366f1,#22c55e);
}

.welcome-card h1{
    margin:0;
    font-size:22px;
}

.welcome-card p{
    margin-top:4px;
    font-size:13px;
    color:#6b7280;
}

/* ===== NOTIFICATION BELL ===== */
.bell{
    position:relative;
    font-size:22px;
    background: rgba(255,255,255,0.95);
    padding:14px;
    border-radius:16px;
    box-shadow:0 8px 20px rgba(0,0,0,.15);
    cursor:pointer;
}

.bell .count{
    position:absolute;
    top:-6px;
    right:-6px;
    background:#ef4444;
    color:#fff;
    font-size:12px;
    padding:3px 7px;
    border-radius:50%;
    font-weight:bold;
}

/* ===== STATS CARDS ===== */
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.stat{
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(6px);
    padding:24px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.15);
    position:relative;
    transition:.3s;
    text-decoration:none;
    color:#111;
}

.stat::before{
    content:'';
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:5px;
    border-radius:20px 20px 0 0;
    background:linear-gradient(90deg,#3b82f6,#a855f7,#ef4444);
}

.stat:hover{
    transform:translateY(-5px);
    box-shadow:0 18px 35px rgba(0,0,0,.25);
}

.stat h3{
    margin:0;
    color:#6b7280;
    font-size:13px;
    text-transform:uppercase;
}

.stat h2{
    margin-top:12px;
    font-size:34px;
    font-weight:800;
}

/* ===== CHART CONTAINER ===== */
.chart-container{
    background: rgba(15,23,42,.9);
    padding:28px;
    border-radius:22px;
    box-shadow:0 15px 35px rgba(0,0,0,.35);
}

.chart-container h2{
    color:#fff;
    margin-bottom:18px;
}

canvas{
    max-height:280px;
}
/* ===== NOTIFICATION DROPDOWN ===== */

.notif-box{
    position:absolute;
    right:0;
    top:60px;
    width:280px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,.2);
    display:none;
    padding:10px;
    z-index:999;
}

.notif-item{
    padding:10px;
    font-size:13px;
    border-bottom:1px solid #eee;
    color:#333;
}
.notif-item strong {
    color: #000;       /* black text */
    font-weight: 900;  /* extra bold / makapal */
}
.notif-item:last-child{
    border-bottom:none;
}

.mark-read{
    display:block;
    text-align:center;
    padding:8px;
    font-size:13px;
    text-decoration:none;
    color:#3b82f6;
}
.notif-label{
    font-weight:600;
    color:#555;
}

.notif-value{
    font-weight:800;
    color:#111;
}
.notif-row{
    margin-bottom:8px;
    line-height:1.6;
}
.notif-title{
    text-align:center;
    font-size:16px;
    font-weight:900;
    color:#1e3a8a;
    background:linear-gradient(90deg,#dbeafe,#bfdbfe);
    padding:8px;
    border-radius:8px;
    margin-bottom:10px;
    letter-spacing:.5px;
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="main">

    <div class="top-header">
        <h1>DASHBOARD</h1>
        <div class="bell" onclick="toggleNotif()">
    🔔
    <?php if($notifCount > 0): ?>
        <span class="count"><?= $notifCount ?></span>
    <?php endif; ?>

    <div id="notifBox" class="notif-box">
        <?php if($notifCount > 0): ?>
           <?php foreach($notifications as $n): ?>
    <?php
$msg = $n['message']; // mula sa database

// Convert labels to <strong>
$msg = htmlspecialchars($n['message']);
$lines = explode("\n", $msg);
$msgHtml = '';

foreach ($lines as $line) {

    // Highlight title
    if (stripos($line, 'New Customer Registration') !== false) {
        $msgHtml .= "<div class='notif-title'>{$line}</div>";
        continue;
    }

    if (strpos($line, ':') !== false) {
        list($label, $value) = explode(':', $line, 2);
        $msgHtml .= "<div class='notif-row'>
                        <span class='notif-label'>{$label}:</span> 
                        <span class='notif-value'>" . trim($value) . "</span>
                     </div>";
    } else {
        $msgHtml .= "<div class='notif-row'>{$line}</div>";
    }
}
?>
<div class="notif-item">
    <?= $msgHtml ?>
</div>
<?php endforeach; ?>
            <a href="mark_read.php" class="mark-read">Mark all as read</a>
        <?php else: ?>
            <div class="notif-item">No new notifications</div>
        <?php endif; ?>
    </div>
</div>
    </div>

    <!-- TOP -->
    <div class="cards">
        <a href="admin_appointments.php" class="card">
            <h3>Total Appointments</h3>
            <p><?= $totalAppointments ?></p>
        </a>
        <a href="admin_appointments.php?today=1" class="card">
            <h3>Today's Appointments</h3>
            <p><?= $todaysAppointments ?></p>
        </a>
    </div>

    <!-- MID -->
    <div class="cards">
        <a href="walkins.php" class="card">
            <h3>Walk-Ins</h3>
            <p><?= $walkInCount ?></p>
        </a>
        <a href="admin_appointments.php?status=Pending" class="card">
            <h3>Pending</h3>
            <p><?= $pendingAppointments ?></p>
        </a>
        <a href="admin_appointments.php?status=Completed" class="card">
            <h3>Completed</h3>
            <p><?= $completedAppointments ?></p>
        </a>
        <a href="admin_appointments.php?status=Cancelled" class="card">
            <h3>Cancelled</h3>
            <p><?= $cancelledAppointments ?></p>
        </a>
    </div>

    <!-- CHART -->
    <div class="chart-container">
        <h2>Appointments Overview</h2>
        <canvas id="appointmentsChart"></canvas>
    </div>

</div>

<script>
new Chart(document.getElementById('appointmentsChart'), {
    data:{
        labels:['Pending','Completed','Cancelled','Walk-Ins'],
        datasets:[
            {
                type:'bar',
                label:'Count',
                data:[
                    <?= $pendingAppointments ?>,
                    <?= $completedAppointments ?>,
                    <?= $cancelledAppointments ?>,
                    <?= $walkInCount ?>
                ],
                backgroundColor:'#ffffff',
                borderRadius:10,
                barThickness:45
            },
            {
                type:'line',
                label:'Trend',
                data:[
                    <?= $pendingAppointments ?>,
                    <?= $completedAppointments ?>,
                    <?= $cancelledAppointments ?>,
                    <?= $walkInCount ?>
                ],
                borderColor:'#facc15',
                backgroundColor:'#facc15',
                tension:.4,
                pointRadius:6
            }
        ]
    },
    options:{
        plugins:{
            legend:{labels:{color:'#e5e7eb'}}
        },
        scales:{
            x:{ticks:{color:'#e5e7eb'},grid:{display:false}},
            y:{ticks:{color:'#e5e7eb'},grid:{color:'rgba(255,255,255,.1)'}}
        }
    }
});
</script>
<script>
function toggleNotif(){
    const box = document.getElementById("notifBox");
    box.style.display = box.style.display === "block" ? "none" : "block";
}
</script>
</body>
</html>
