<?php
session_start();
include '../config.php';

/* ======================
   BARBER ONLY
===================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'barber') {
    header("Location: ../login.php");
    exit();
}

$barberName = $_SESSION['fullname'] ?? 'Barber';
$barberNameEsc = mysqli_real_escape_string($conn, $barberName);
$barberId = (int)($_SESSION['user_id'] ?? 0);

/* ======================
   FETCH NOTIFICATIONS
===================== */
$notifCount = 0;
$notifications = [];

if ($barberId) {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM notifications WHERE user_id = $barberId AND status='unread'");
    $notifCount = (int)mysqli_fetch_assoc($res)['total'];

    $res = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $barberId ORDER BY created_at DESC LIMIT 10");
    while ($row = mysqli_fetch_assoc($res)) {
        $notifications[] = $row;
    }
}

/* ======================
   DASHBOARD COUNTS
===================== */
$totalAppointments = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM appointments WHERE barber_name = '$barberNameEsc'"))['total'] ?? 0;
$pendingAppointments = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM appointments WHERE barber_name = '$barberNameEsc' AND status='Pending'"))['total'] ?? 0;
$completedAppointments = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM appointments WHERE barber_name = '$barberNameEsc' AND status='Completed'"))['total'] ?? 0;
$cancelledAppointments = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM appointments WHERE barber_name = '$barberNameEsc' AND status='Cancelled'"))['total'] ?? 0;

/* ======================
   LAST 7 DAYS APPOINTMENTS FOR CHART
===================== */
$dayTotals = [];
$res = mysqli_query($conn, "
    SELECT DATE(appointment_date) AS day, COUNT(*) AS total
    FROM appointments
    WHERE barber_name = '$barberNameEsc'
    AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(appointment_date)
    ORDER BY day ASC
");
while ($row = mysqli_fetch_assoc($res)) {
    $dayTotals[$row['day']] = (int)$row['total'];
}

$allDays = [];
$allValues = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $allDays[] = date('D', strtotime($day));
    $allValues[] = $dayTotals[$day] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Barber Dashboard</title>
<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif}
body{margin:0;background:#f4f6f9}
.main{margin-left:270px;padding:30px 40px 30px 30px}
.header{display:flex;align-items:center;gap:16px;margin-bottom:22px;position:relative}
.welcome-card{
    flex:1;
    padding:18px 26px;
    border-radius:18px;
    position:relative;

    background:#ffffff;
    border:2px solid transparent;

    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(#ffffff,#ffffff),
        linear-gradient(135deg,#3b82f6,#a855f7,#ef4444);

    box-shadow:0 10px 25px rgba(0,0,0,.15);
}

/* REMOVE old top line */
.welcome-card::before{
    display:none;
}

.welcome-card::before{content:'';position:absolute;top:0;left:0;width:100%;height:4px;background:linear-gradient(90deg,#6366f1,#22c55e)}
.welcome-card h1{margin:0;font-size:22px}
.welcome-card p{margin-top:4px;font-size:13px;color:#6b7280}
.bell{position:relative;font-size:24px;text-decoration:none;background:#fff;padding:14px;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.08);cursor:pointer}
.bell .count{position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;font-size:12px;padding:2px 6px;border-radius:50%;font-weight:bold}
#notifDropdown{display:none;position:absolute;top:60px;right:0; z-index: 2000;background:#fff;color:#111;width:320px;border-radius:12px;box-shadow:0 6px 16px rgba(0,0,0,.18);z-index:10;max-height:400px;overflow-y:auto}
#notifDropdown ul{list-style:none;padding:0;margin:0}
#notifDropdown li{padding:12px 16px;border-bottom:1px solid #f1f1f1;font-size:14px;position:relative}
#notifDropdown li:last-child{border-bottom:none}
#notifDropdown li.unread{font-weight:bold;background:#f0fdf4}
.deleteNotif{position:absolute;bottom:12px;right:12px;background:#ef4444;color:#fff;border:none;border-radius:6px;padding:4px 8px;font-size:12px;cursor:pointer}
.stats{display:grid;z-index: 1; grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:30px}
.stat{
    padding:24px;
    border-radius:20px;
    position:relative;
    transition:.3s;
    text-decoration:none;
    color:#111;
    text-align:center;

    background:#ffffff;
    border:2px solid transparent;

    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(#ffffff,#ffffff),
        linear-gradient(135deg,#3b82f6,#a855f7,#ef4444);

    box-shadow:0 10px 30px rgba(0,0,0,.15);
}

/* remove old top line */
.stat::before{
    display:none;
}

.stat:hover{
    transform:translateY(-5px);
    box-shadow:0 18px 35px rgba(0,0,0,.25);
}

.stat::before{content:'';position:absolute;top:0;left:0;width:100%;height:5px;background:linear-gradient(90deg,#4f46e5,#22c55e)}
.stat:hover{transform:translateY(-4px);box-shadow:0 14px 30px rgba(0,0,0,.18)}
.stat:active{transform:scale(.98)}
.stat h3{margin:0;color:#6b7280;font-size:13px;text-transform:uppercase}
.stat h2{margin-top:10px;font-size:34px;font-weight:800}
.chart-container{background:#0f172a;padding:26px;border-radius:22px;box-shadow:0 10px 25px rgba(0,0,0,.25);width:100%}
.chart-container h2{color:#fff;margin-bottom:18px}
.chart-container canvas{max-height:280px}

/* Confirmation Modal Styles */
#confirmModal {
    display: none;
    position: fixed;
    z-index: 100;
    left: 0; top: 0; width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
}
#confirmModalContent {
    background: #fff;
    padding: 20px 30px;
    border-radius: 10px;
    max-width: 320px;
    text-align: center;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}
#confirmModalContent p {
    margin-bottom: 20px;
    font-size: 16px;
}
#confirmModalContent button {
    padding: 8px 16px;
    margin: 0 10px;
    font-size: 14px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: background-color 0.25s ease;
}
#confirmYes {
    background-color: #ef4444;
    color: white;
}
#confirmYes:hover {
    background-color: #dc2626;
}
#confirmNo {
    background-color: #ddd;
}
#confirmNo:hover {
    background-color: #bbb;
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
.header{
    display:flex;
    align-items:center;
    gap:16px;
    margin-bottom:22px;
    position:relative;
}

.welcome-card{
    flex:1;
    padding:18px 26px;
    border-radius:18px;
    position:relative;

    background:#ffffff;
    border:2px solid transparent;

    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(#ffffff,#ffffff),
        linear-gradient(135deg,#3b82f6,#a855f7,#ef4444);

    box-shadow:0 10px 25px rgba(0,0,0,.15);
}

/* REMOVE old top line */
.welcome-card::before{
    display:none;
}

/* ===== NOTIFICATION BELL ===== */
.bell{
    position:relative;
    font-size:22px;
    padding:14px;
    border-radius:16px;
    cursor:pointer;

    background:#ffffff;
    border:2px solid transparent;

    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(#ffffff,#ffffff),
        linear-gradient(135deg,#3b82f6,#a855f7,#ef4444);

    box-shadow:0 8px 20px rgba(0,0,0,.15);
}

/* ===== STATS CARDS ===== */
.stat{
    padding:24px;
    border-radius:20px;
    position:relative;
    transition:.3s;
    text-decoration:none;
    color:#111;

    background:#ffffff;
    border:2px solid transparent;

    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(#ffffff,#ffffff),
        linear-gradient(135deg,#3b82f6,#a855f7,#ef4444);

    box-shadow:0 10px 30px rgba(0,0,0,.15);
}

/* remove old top line */
.stat::before{
    display:none;
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
/* ===== CENTERED DASHBOARD HEADER ===== */
.page-header{
    position: relative;
    z-index: 50; /* para ma-position ang bell sa absolute */
    text-align: center;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(12px);
    padding:30px;
    border-radius:24px;
    margin-bottom:25px;
    box-shadow:0 20px 40px rgba(0,0,0,.35);
}

.header-text{
    display: inline-block; /* para ma-center sa page-header */
}

.bell{
    position: absolute;
    z-index: 50;
    top: 30px;  /* adjust depende sa padding header */
    right: 30px; /* adjust depende sa gusto mo */
    font-size: 22px;
    cursor: pointer;
    color: #ffffff;
    background: none;
    border: none;
    box-shadow: none;
}
.header-text h1{
    color: #ffffff;  /* siguraduhing white */
    margin: 0;
    font-size: 30px;
    font-weight: 800;
}

.header-text p{
    color: #ffffff;  /* siguraduhing white */
    margin-top: 6px;
    font-size: 15px;
}
</style>
</head>
<body>

<?php include 'sidebar_barber.php'; ?>

<div class="main">

    <!-- HEADER -->
    <div class="page-header">
    <div class="header-text">
        <h1>DASHBOARD</h1>
        <p>Overview of your appointments</p>
    </div>

    <div class="bell" id="bellIcon">
        🔔
        <?php if ($notifCount > 0): ?>
            <span class="count" id="notifCount"><?= $notifCount ?></span>
        <?php endif; ?>

        <div id="notifDropdown">
            <ul id="notifList">
                <?php if(count($notifications) > 0): ?>
                    <?php foreach($notifications as $n): ?>
                        <li data-id="<?= htmlspecialchars($n['id']) ?>" class="<?= $n['status']=='unread'?'unread':'' ?>">
                            <?= nl2br(htmlspecialchars($n['message'])) ?>
                            <br><small style="color:#888"><?= date('M d, Y h:i A', strtotime($n['created_at'])) ?></small>
                            <button class="deleteNotif">Delete</button>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No notifications</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

    <!-- CLICKABLE STATS -->
    <div class="stats">
        <a href="barber_appointments.php" class="stat">
            <h3>Total Appointments</h3>
            <h2><?= $totalAppointments ?></h2>
        </a>

        <a href="barber_appointments.php?status=Pending" class="stat">
            <h3>Pending</h3>
            <h2><?= $pendingAppointments ?></h2>
        </a>

        <a href="barber_appointments.php?status=Completed" class="stat">
            <h3>Completed</h3>
            <h2><?= $completedAppointments ?></h2>
        </a>

        <a href="barber_cancelled.php" class="stat">
            <h3>Cancelled</h3>
            <h2><?= $cancelledAppointments ?></h2>
        </a>
    </div>

    <!-- CHART -->
    <div class="chart-container">
        <h2>Appointments Overview (Last 7 Days)</h2>
        <canvas id="appointmentsChart"></canvas>
    </div>

</div>

<!-- Confirmation Modal -->
<div id="confirmModal">
    <div id="confirmModalContent">
        <p>Are you sure you want to delete this notification?</p>
        <button id="confirmYes">Yes</button>
        <button id="confirmNo">No</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart setup
new Chart(document.getElementById('appointmentsChart'), {
    data: {
        labels: <?= json_encode($allDays) ?>,
        datasets: [
            { type: 'bar', label: 'Appointments', data: <?= json_encode($allValues) ?>, backgroundColor:'#ffffff', borderRadius:10, barThickness:34 },
            { type: 'line', label: 'Trend', data: <?= json_encode($allValues) ?>, borderColor:'#facc15', backgroundColor:'#facc15', tension:.45, pointRadius:6 }
        ]
    },
    options:{
        plugins:{ legend:{ labels:{ color:'#e5e7eb' } } },
        scales:{ x:{ grid:{display:false}, ticks:{color:'#e5e7eb'} }, y:{ grid:{color:'rgba(255,255,255,.1)'}, ticks:{color:'#e5e7eb', stepSize:1} } }
    }
});

// Notification dropdown toggle
const bell = document.getElementById('bellIcon');
const dropdown = document.getElementById('notifDropdown');
bell.addEventListener('click', function(e){
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';

    // Mark unread notifications as read
    fetch('mark_notifications_read.php', { method: 'POST' })
       .then(res => res.text())
.then(text => {
    console.log(text);
    return JSON.parse(text);
})
        .then(data => {
            if(data.success){
                const countElem = document.getElementById('notifCount');
                if(countElem) countElem.style.display = 'none';
                dropdown.querySelectorAll('li.unread').forEach(li => li.classList.remove('unread'));
            }
        });
});

// Close dropdown when clicking outside
document.addEventListener('click', () => {
    dropdown.style.display = 'none';
});

// Delete notification with confirmation modal
const notifList = document.getElementById('notifList');
const confirmModal = document.getElementById('confirmModal');
const confirmYes = document.getElementById('confirmYes');
const confirmNo = document.getElementById('confirmNo');

let deleteTargetLi = null;

notifList.addEventListener('click', function(e){
    if(e.target.classList.contains('deleteNotif')){
        e.stopPropagation();
        deleteTargetLi = e.target.closest('li');
        if(!deleteTargetLi) {
            alert('Hindi mahanap ang notification para i-delete.');
            return;
        }
        confirmModal.style.display = 'flex';
    }
});

confirmYes.addEventListener('click', function(){

    if (!deleteTargetLi) {
        confirmModal.style.display = 'none';
        return;
    }

    const notifId = deleteTargetLi.dataset.id;

    // 🔥 REMOVE IMMEDIATELY (UI first)
    const liToRemove = deleteTargetLi;
    liToRemove.remove();

    // Update counter agad
    const countElem = document.getElementById('notifCount');
    if(countElem){
        let newCount = parseInt(countElem.textContent) - 1;
        if(newCount <= 0){
            countElem.style.display = 'none';
        } else {
            countElem.textContent = newCount;
        }
    }

    fetch('delete_notification.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + encodeURIComponent(notifId)
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            if(deleteTargetLi) deleteTargetLi.remove();
            const countElem = document.getElementById('notifCount');
            if(countElem){
                let newCount = parseInt(countElem.textContent) - 1;
                if(newCount <= 0) countElem.style.display = 'none';
                else countElem.textContent = newCount;
            }
        } else {
            alert('Failed to delete notification: ' + (data.msg || 'Unknown error'));
        }
    })
    .catch(err => alert('Error: ' + err));

    confirmModal.style.display = 'none';
    deleteTargetLi = null;
});

confirmNo.addEventListener('click', function(){
    confirmModal.style.display = 'none';
    deleteTargetLi = null;
});
</script>

</body>
</html>
