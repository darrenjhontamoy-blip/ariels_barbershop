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
$barberId   = (int)$_SESSION['user_id'];

/* ======================
   FETCH UPCOMING SCHEDULE
===================== */
$scheduleRes = mysqli_query($conn,"
    SELECT work_date, start_time, end_time 
    FROM barber_schedule 
    WHERE barber_id = $barberId 
      AND work_date >= CURDATE()
    ORDER BY work_date ASC, start_time ASC
");

if(!$scheduleRes){
    die("Query Failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Schedule</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif}
body{margin:0;background:#f4f6f9}
.main{margin-left:270px;padding:30px}
.header{display:flex;align-items:center;margin-bottom:22px}
.header-card{
    flex:1;
    background:linear-gradient(135deg,#ffffff,#f9fafb);
    padding:16px 24px;
    border-radius:16px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
    position:relative;
}
.header-card::before{
    content:'';
    position:absolute;
    top:0;left:0;
    width:100%;
    height:4px;
    background:linear-gradient(90deg,#4f46e5,#22c55e);
}
.header-card h1{margin:0;font-size:22px}
.header-card p{margin-top:4px;font-size:13px;color:#6b7280}

.table-box{
    background:linear-gradient(135deg,#ffffff,#f8fafc);
    border-radius:18px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
    overflow:hidden;
    position:relative;
}
.table-box::before{
    content:'';
    position:absolute;
    top:0;left:0;
    width:100%;
    height:4px;
    background:linear-gradient(90deg,#22c55e,#4f46e5);
}
table{width:100%;border-collapse:collapse}
th,td {
    padding: 14px;
    text-align: center; /* optional: kung gusto mo rin i-center ang content ng rows */
    font-size: 14px;
}
    padding: 14px;
    text-align: center; /* change from left to center */
    font-size: 14px;
    font-weight: 600;
}
tr:nth-child(even){background:#fafafa}
tr:hover{background:#f0fdf4}

.badge{
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:700;
    display:inline-block;
}
.scheduled{
    background:#3b82f6;
    color:#fff;
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
/* ===== CENTERED WHITE HEADER ===== */
.page-header{
    text-align:center;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    padding:28px;
    border-radius:22px;
    margin-bottom:30px;
    box-shadow:0 15px 30px rgba(0,0,0,.35);
}

.page-header h1{
    margin:0;
    font-size:28px;
    color:#ffffff;
    font-weight:800;
    letter-spacing:1px;
}

.page-header p{
    margin-top:6px;
    font-size:14px;
    color:#e5e7eb;
}
</style>

</head>

<body>

<?php include 'sidebar_barber.php'; ?>

<div class="main">

    <div class="page-header">
    <h1>MY SCHEDULE</h1>
    <p>View your upcoming work schedule</p>
</div>

    <div class="table-box">
        <table>
            <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Time</th>
                <th>Status</th>
            </tr>

            <?php if(mysqli_num_rows($scheduleRes) > 0): ?>
                <?php while($s = mysqli_fetch_assoc($scheduleRes)): ?>
                    <tr>
                        <td>
                            <?= date('M d, Y', strtotime($s['work_date'])) ?>
                        </td>

                        <td>
                            <?= date('l', strtotime($s['work_date'])) ?>
                        </td>

                        <td>
                            <?= date('h:i A', strtotime($s['start_time'])) ?> -
                            <?= date('h:i A', strtotime($s['end_time'])) ?>
                        </td>

                        <td>
                            <span class="badge scheduled">Scheduled</span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center;color:#777">
                        No schedule assigned yet
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

</div>

</body>
</html>
