<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* =========================
   LINE GRAPH DATA
========================= */
$labels = [];
$values = [];

$graph = mysqli_query($conn,"
    SELECT appointment_date AS day, COUNT(*) AS total
    FROM appointments
    GROUP BY appointment_date
    ORDER BY appointment_date
");

while ($row = mysqli_fetch_assoc($graph)) {
    $labels[] = $row['day'];
    $values[] = $row['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Barber Reports</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    font-family:Segoe UI;
    background:linear-gradient(135deg,#ecfeff,#f0fdf4)
}
.main{
    margin-left:300px;
    padding:30px
}

.graph-card{
    background:rgba(255,255,255,.9);
    backdrop-filter:blur(10px);
    padding:30px;
    border-radius:20px;
    margin-bottom:30px;
    box-shadow:0 20px 40px rgba(34,197,94,.18);
}

.graph-card h3{
    margin-bottom:20px;
    font-size:20px;
    font-weight:700;
    color:#15803d
}

table{
    width:100%;
    background:#fff;
    border-collapse:collapse;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}
th,td{
    padding:12px;
    border-bottom:1px solid #eee
}
th{
    background:#22c55e;
    color:#fff;
    text-align:left
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
    text-align: center;  /* center-align lahat ng header at laman */
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
<h2>Barber Reports</h2>

<!-- ESTHETIC LINE GRAPH -->
<div class="graph-card">
    <h3>Total Appointments per Day</h3>
    <canvas id="barberLineChart"></canvas>
</div>

<!-- TABLE -->
<table>
<tr>
    <th>ID</th>
    <th>Full Name</th>
    <th>Status</th>
</tr>

<?php
$q = mysqli_query($conn,"SELECT id, fullname, status FROM users WHERE role='barber'");
while ($r = mysqli_fetch_assoc($q)) {
    echo "<tr>
        <td>{$r['id']}</td>
        <td>{$r['fullname']}</td>
        <td>{$r['status']}</td>
    </tr>";
}
?>
</table>

</div>

<script>
const ctx = document.getElementById('barberLineChart').getContext('2d');

// GREEN GRADIENT
const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(34,197,94,0.55)');
gradient.addColorStop(1, 'rgba(34,197,94,0.05)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Appointments',
            data: <?= json_encode($values) ?>,
            borderWidth: 3,
            borderColor: '#22c55e',
            backgroundColor: gradient,
            fill: true,
            tension: 0.45,
            pointRadius: 5,
            pointBackgroundColor: '#22c55e'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html>
