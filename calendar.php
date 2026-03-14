<?php
session_start();
include '../config.php';

/* ADMIN ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* FETCH BARBERS */
$barbers = [];
$res = mysqli_query($conn,"SELECT id, fullname FROM users WHERE role='barber' ORDER BY fullname");
while ($r = mysqli_fetch_assoc($res)) $barbers[] = $r;

/* CALENDAR LOGIC */
$year  = $_GET['year']  ?? date('Y');
$month = $_GET['month'] ?? date('m');

$time = strtotime("$year-$month-01");
$monthName   = date('F Y', $time);
$daysInMonth = date('t', $time);
$firstDay    = date('w', $time);

$todayDate = date('Y-m-d');

$prevMonth = $month - 1; $prevYear = $year;
$nextMonth = $month + 1; $nextYear = $year;
if ($prevMonth == 0) { $prevMonth = 12; $prevYear--; }
if ($nextMonth == 13) { $nextMonth = 1; $nextYear++; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Calendar</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;margin:0;padding:0}
body{background:#f4f6f9;color:#333}
.main{margin-left:240px;padding:30px}

.top-header{
    display:flex;justify-content:space-between;align-items:center;
    background:#e3e6ff;color:#3f51b5;
    padding:20px 30px;border-radius:15px;
    box-shadow:0 8px 24px rgb(115 129 199 / 15%);
    margin-bottom:25px;
}
.top-header h1{font-size:28px;font-weight:700}
.logo img{height:40px}

.month-nav{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.month-nav strong,
.month-nav a {
    text-decoration: none; 
    color: #fff; 
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 8px;
}
.month-nav a:hover {
    background: #dbeafe; 
    color: #000;           
}
.calendar-wrapper{
    display:grid;grid-template-columns:repeat(7,1fr);gap:12px;
}
.day-head {
    text-align:center;
    font-weight:600;
    font-size:13px;
    color:#fff; 
}

.day-box{
    background:#fff;border-radius:15px;padding:10px;
    min-height:90px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
    cursor:pointer;
}
.day-box:hover{background:#eef2ff}

.day-number{font-weight:600;font-size:16px}
.today{
    background:#3f51b5;color:#fff;
    padding:5px 10px;border-radius:12px;
}

/* 🔒 DISABLED PAST DATE */
.past{
    background:#f1f5f9;
    color:#9ca3af;
    cursor:not-allowed;
    opacity:0.6;
}
.past:hover{background:#f1f5f9}

/* MODAL */
.modal{
    display:none;position:fixed;inset:0;
    background:rgba(0,0,0,.5);
    align-items:center;justify-content:center;
}
.box{
    background:#fff;padding:25px;
    border-radius:15px;width:350px;
}

input,select,button{
    width:100%;padding:10px;margin-bottom:12px;
    border-radius:10px;border:1px solid #d1d5db;
}
button{
    background:#3f51b5;color:#fff;
    border:none;font-weight:600;cursor:pointer;
}
button.cancel{background:#eee;color:#333}

@media(max-width:768px){.calendar-wrapper{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.calendar-wrapper{grid-template-columns:1fr}}

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
</style>
</head>

<?php include 'sidebar.php'; ?>

<body>

<div class="main">

    <div class="top-header">
        <h1>CALENDAR</h1>
        <div class="logo">
            <img src="arslogo.jpg" alt="Logo">
        </div>
    </div>

    <div class="month-nav">
        <a href="?month=<?=$prevMonth?>&year=<?=$prevYear?>">◀ Previous</a>
        <strong><?=$monthName?></strong>
        <a href="?month=<?=$nextMonth?>&year=<?=$nextYear?>">Next ▶</a>
    </div>

    <div class="calendar-wrapper">
        <?php foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
            <div class="day-head"><?=$d?></div>
        <?php endforeach; ?>

        <?php
        for ($i=0;$i<$firstDay;$i++) echo "<div></div>";

        for ($d=1;$d<=$daysInMonth;$d++):
            $date = sprintf("%04d-%02d-%02d",$year,$month,$d);
            $isToday = ($date === $todayDate);
            $isPast  = ($date < $todayDate);
        ?>
        <div
            class="day-box <?= $isPast ? 'past' : '' ?>"
            <?= $isPast ? '' : "onclick=\"openModal('$date')\"" ?>
        >
            <div class="day-number <?= $isToday ? 'today' : '' ?>">
                <?=$d?>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>

<!-- MODAL -->
<div class="modal" id="modal">
<div class="box">
<h3>Set Barber Schedule</h3>

<form method="POST" action="save_schedule.php">
    <input type="hidden" name="work_date" id="work_date">
    <input type="hidden" name="work_day" id="work_day">

    <label>Barber</label>
    <select name="barber_id" required>
        <option value="">Select Barber</option>
        <?php foreach ($barbers as $b): ?>
            <option value="<?=$b['id']?>"><?=htmlspecialchars($b['fullname'])?></option>
        <?php endforeach; ?>
    </select>

    <!-- 🔒 START TIME FIXED -->
    <label>Start Time (Fixed)</label>
    <input type="time" name="start_time" value="09:00" readonly>

    <!-- 🔓 END TIME SELECTABLE -->
    <label>End Time</label>
    <input type="time" name="end_time" value="12:00" min="12:00" max="21:00" required>

    <button type="submit">Save Schedule</button>
    <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
</form>
</div>
</div>

<script>
function openModal(date){
    const d = new Date(date + 'T00:00:00');
    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    document.getElementById('work_date').value = date;
    document.getElementById('work_day').value  = days[d.getDay()];
    document.getElementById('modal').style.display = 'flex';
}
function closeModal(){
    document.getElementById('modal').style.display = 'none';
}
</script>

</body>
</html>
