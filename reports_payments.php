<?php
session_start();
include '../config.php';

/* ADMIN ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* =========================
   FETCH DAILY INCOME & DURATION
========================= */
$labels = [];
$dailyIncome = [];
$dailyDuration = [];

$res = mysqli_query($conn, "
    SELECT DATE(payment_date) AS day, SUM(price) AS total_income, SUM(duration) AS total_duration
    FROM appointments
    WHERE payment_status='Paid'
    GROUP BY DATE(payment_date)
    ORDER BY day
");

while ($r = mysqli_fetch_assoc($res)) {
    $labels[] = $r['day'];
    $dailyIncome[] = (float)$r['total_income'];
    $dailyDuration[] = (int)$r['total_duration'];
}

/* =========================
   FETCH WEEKLY INCOME & DURATION
========================= */
$weeklyLabels = [];
$weeklyIncome = [];
$weeklyDuration = [];

$resWeekly = mysqli_query($conn, "
    SELECT YEAR(payment_date) AS year, WEEK(payment_date, 1) AS week,
           SUM(price) AS total_income, SUM(duration) AS total_duration
    FROM appointments
    WHERE payment_status='Paid'
    GROUP BY YEAR(payment_date), WEEK(payment_date,1)
    ORDER BY year, week
");

while ($row = mysqli_fetch_assoc($resWeekly)) {
    $weeklyLabels[] = "Week " . $row['week'] . ", " . $row['year'];
    $weeklyIncome[] = (float)$row['total_income'];
    $weeklyDuration[] = (int)$row['total_duration'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment & Duration Reports</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{font-family:'Poppins', sans-serif;background:linear-gradient(135deg,#eef2ff,#f8fafc);margin:0;}
.main{margin-left:350px;padding:30px;}
.graph-card{background:rgba(255,255,255,0.95);backdrop-filter:blur(10px);padding:30px;border-radius:20px;box-shadow:0 20px 40px rgba(79,70,229,0.15);margin-bottom:30px;}
.graph-card h3{margin-bottom:20px;font-size:22px;font-weight:700;color:#4338ca;}
.note-card{
    background:#fff;
    padding:20px;
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    color:#555;
    margin-bottom:30px;
    text-align: center;  /* <-- center-align ng text */
}
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background: linear-gradient(135deg, #0b3d91 0%, #1e3a8a 40%, #7f1d1d 70%, #c1121f 100%);
    min-height: 100vh;
    color: #111;
}

.main {
    margin-left: 350px; /* match sidebar width */
    padding: 30px 40px 50px 30px;
}

/* ===== PAGE HEADER ===== */
.main h2 {
    font-size: 28px;
    color: #fff;
    margin-bottom: 25px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ===== GRAPH CARD ===== */
.graph-card {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    padding: 28px 32px;
    border-radius: 20px;
    margin-bottom: 30px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.graph-card h3 {
    margin-bottom: 20px;
    font-size: 20px;
    font-weight: 700;
    color: #1d4ed8;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    background: rgba(255,255,255,0.95);
    border-collapse: collapse;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

th {
    background: linear-gradient(90deg, #3b82f6, #ef4444);
    color: #fff;
    font-weight: 700;
}

tr:nth-child(even) { background: #f9fafb; }
tr:hover { background: #e0f2fe; }

/* STATUS BADGE */
.status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    color: #fff;
    background: #3b82f6;
}

/* ===== RESPONSIVE ===== */
@media(max-width: 768px) {
    .main {
        margin-left: 0;
        padding: 20px;
    }
    table, th, td {
        font-size: 13px;
        padding: 8px 10px;
    }
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
    <h2>Payment & Duration Reports</h2>

    <!-- DAILY GRAPH -->
    <div class="graph-card">
        <h3>Daily Income & Total Duration (Paid Appointments)</h3>
        <canvas id="dailyChart"></canvas>
    </div>

    <!-- WEEKLY GRAPH -->
    <div class="graph-card">
        <h3>Weekly Income & Total Duration (Paid Appointments)</h3>
        <canvas id="weeklyChart"></canvas>
    </div>

    <div class="note-card">
        <p>💳 Graphs show payments marked as <strong>Paid</strong> and total appointment duration.</p>
        <p>Daily and weekly totals reflect actual appointment prices and durations.</p>
    </div>
</div>

<script>
/* ===== DAILY CHART ===== */
const ctxDaily = document.getElementById('dailyChart').getContext('2d');

new Chart(ctxDaily, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            {
                label: 'Income (₱)',
                data: <?= json_encode($dailyIncome) ?>,
                backgroundColor: 'rgba(79,70,229,0.7)',
                yAxisID: 'yIncome'
            },
            {
                label: 'Total Duration (mins)',
                data: <?= json_encode($dailyDuration) ?>,
                backgroundColor: 'rgba(16,185,129,0.7)',
                yAxisID: 'yDuration'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            yIncome: {
                type: 'linear',
                position: 'left',
                beginAtZero: true,
                ticks: { callback: value => '₱' + value },
                title: { display: true, text: 'Income (₱)' }
            },
            yDuration: {
                type: 'linear',
                position: 'right',
                beginAtZero: true,
                ticks: { callback: value => value + ' min' },
                grid: { drawOnChartArea: false },
                title: { display: true, text: 'Duration (mins)' }
            }
        }
    }
});

/* ===== WEEKLY CHART ===== */
const ctxWeekly = document.getElementById('weeklyChart').getContext('2d');

new Chart(ctxWeekly, {
    type: 'bar',
    data: {
        labels: <?= json_encode($weeklyLabels) ?>,
        datasets: [
            {
                label: 'Income (₱)',
                data: <?= json_encode($weeklyIncome) ?>,
                backgroundColor: 'rgba(79,70,229,0.7)',
                yAxisID: 'yIncome'
            },
            {
                label: 'Total Duration (mins)',
                data: <?= json_encode($weeklyDuration) ?>,
                backgroundColor: 'rgba(16,185,129,0.7)',
                yAxisID: 'yDuration'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            yIncome: {
                type: 'linear',
                position: 'left',
                beginAtZero: true,
                ticks: { callback: value => '₱' + value },
                title: { display: true, text: 'Income (₱)' }
            },
            yDuration: {
                type: 'linear',
                position: 'right',
                beginAtZero: true,
                ticks: { callback: value => value + ' min' },
                grid: { drawOnChartArea: false },
                title: { display: true, text: 'Duration (mins)' }
            }
        }
    }
});
</script>

</body>
</html>
