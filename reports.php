<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* COUNTS */
$customers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM customers"))['total'] ?? 0;
$barbers   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM users WHERE role='barber'"))['total'] ?? 0;
$appointments = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM appointments"))['total'] ?? 0;
$payments  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM payments"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- FONT AWESOME 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;margin:0;padding:0}
body{background:#f4f6f9}

/* MAIN */
.main{margin-left:240px;padding:30px}

/* HEADER */
.top-header{
    display:flex;justify-content:space-between;align-items:center;
    background:#e3e6ff;padding:20px 30px;border-radius:15px;
    box-shadow:0 8px 24px rgb(115 129 199 / 15%);
    margin-bottom:25px
}
.top-header h1{font-size:28px;font-weight:700;color:#3f51b5}
.logo img{height:40px}

/* ACTIONS */
.actions{display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap;margin-bottom:25px}
.search-box{position:relative;max-width:320px;width:100%}
.search-box input{
    width:100%;padding:10px 40px 10px 15px;
    border-radius:10px;border:1px solid #d1d5db
}
.search-box i{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#777}

/* EXPORT */
.export-buttons{display:flex;gap:10px}
.export-btn{
    border:none;padding:10px 15px;border-radius:10px;
    font-weight:600;color:#fff;cursor:pointer
}
.excel{background:#4f46e5}
.pdf{background:#ef4444}

/* CARDS */
.report-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:20px
}
.report-link{text-decoration:none;color:inherit;display:block}
.report-card{
    background:#fff;padding:25px;border-radius:18px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
    text-align:center;transition:.2s;cursor:pointer
}
.report-card i {
    font-size: 38px;
    margin-bottom: 10px;
    color: #4f46e5; /* Blue icon */
}
.report-card h3{font-size:20px;margin-bottom:5px}
.report-card p{font-size:14px;color:#555}
.report-card:hover{
    transform:translateY(-6px);
    box-shadow:0 10px 25px rgba(79,70,229,.25)
}

/* GRAPH */
.overview-graph{
    background:linear-gradient(135deg,#0f172a,#020617);
    padding:30px;border-radius:22px;
    box-shadow:0 20px 50px rgba(0,0,0,.45);
    margin-top:35px
}
.overview-graph h2{color:#fff;margin-bottom:20px}
.overview-graph canvas{
    width:100%!important;
    height:300px!important;
    max-height:300px
}

@media(max-width:768px){
    .main{margin-left:0;padding:20px}
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
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">

<div class="top-header">
    <h1>REPORTS</h1>
    <div class="logo"><img src="arslogo.jpg"></div>
</div>

<div class="actions">
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search reports...">
        <i class="fa fa-search"></i>
    </div>
    <div class="export-buttons">
        <button class="export-btn excel" onclick="location.href='export_excel.php'">
            <i class="fa-solid fa-file-excel"></i> Excel
        </button>
        <button class="export-btn pdf" onclick="location.href='export_pdf.php'">
            <i class="fa-solid fa-file-pdf"></i> PDF
        </button>
    </div>
</div>

<div class="report-grid">

    <a href="reports_customers.php" class="report-link">
        <div class="report-card">
            <i class="fa-solid fa-users"></i>
            <h3>Customers</h3>
            <p>Total: <?= $customers ?></p>
        </div>
    </a>

    <a href="reports_barbers.php" class="report-link">
        <div class="report-card">
            <i class="fa-solid fa-scissors"></i> <!-- Blue scissors icon -->
            <h3>Barbers</h3>
            <p>Total: <?= $barbers ?></p>
        </div>
    </a>

    <a href="reports_appointments.php" class="report-link">
        <div class="report-card">
            <i class="fa-solid fa-calendar-check"></i>
            <h3>Appointments</h3>
            <p>Total: <?= $appointments ?></p>
        </div>
    </a>

    <a href="reports_payments.php" class="report-link">
        <div class="report-card">
            <i class="fa-solid fa-credit-card"></i>
            <h3>Payments</h3>
            <p>Total: <?= $payments ?></p>
        </div>
    </a>

</div>

<div class="overview-graph">
    <h2>System Overview</h2>
    <canvas id="overviewChart"></canvas>
</div>

</div>

<script>
// SEARCH
document.getElementById("searchInput").addEventListener("keyup",function(){
    let f=this.value.toLowerCase();
    document.querySelectorAll(".report-link").forEach(c=>{
        c.style.display=c.innerText.toLowerCase().includes(f)?"block":"none";
    });
});

// CHART
new Chart(document.getElementById('overviewChart'),{
    data:{
        labels:['Customers','Barbers','Appointments','Payments'],
        datasets:[
            {
                type:'bar',
                label:'Count',
                data:[<?= $customers ?>,<?= $barbers ?>,<?= $appointments ?>,<?= $payments ?>],
                backgroundColor:'#ffffff',
                borderRadius:10,
                barThickness:36,
                maxBarThickness:38,
                categoryPercentage:0.6,
                barPercentage:0.7
            },
            {
                type:'line',
                label:'Trend',
                data:[<?= $customers ?>,<?= $barbers ?>,<?= $appointments ?>,<?= $payments ?>],
                borderColor:'#facc15',
                tension:.45,
                pointRadius:5
            }
        ]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{legend:{labels:{color:'#fff'}}},
        scales:{
            x:{ticks:{color:'#cbd5f5'},grid:{display:false}},
            y:{ticks:{color:'#cbd5f5'},grid:{color:'rgba(255,255,255,.08)'},beginAtZero:true}
        }
    }
});
</script>

</body>
</html>
