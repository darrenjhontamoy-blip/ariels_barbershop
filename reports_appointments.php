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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments Report | Admin</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ===== GLOBAL ===== */
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
    text-align: center;
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
    <h2>Appointments Report</h2>

    <!-- GRAPH CARD -->
    <div class="graph-card">
        <h3>Appointments per Day</h3>
        <canvas id="appointmentsLineChart"></canvas>
    </div>

    <!-- APPOINTMENTS TABLE -->
    <table>
        <tr>
            <th>Customer</th>
            <th>Service</th>
            <th>Barber</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
        <?php
        $q = mysqli_query($conn,"SELECT * FROM appointments ORDER BY appointment_date DESC");
        while ($r = mysqli_fetch_assoc($q)) {
            $statusColor = $r['status'] === 'Cancelled' ? '#ef4444' : '#3b82f6';
            echo "<tr>
                <td>{$r['customer_name']}</td>
                <td>{$r['service']}</td>
                <td>{$r['barber_name']}</td>
                <td>{$r['appointment_date']}</td>
                <td><span class='status' style='background:{$statusColor}'>{$r['status']}</span></td>
            </tr>";
        }
        ?>
    </table>
</div>

<script>
const ctx = document.getElementById('appointmentsLineChart').getContext('2d');

// BLUE GRADIENT FOR CHART
const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(59,130,246,0.55)');
gradient.addColorStop(1, 'rgba(59,130,246,0.05)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Total Appointments',
            data: <?= json_encode($values) ?>,
            borderWidth: 3,
            borderColor: '#3b82f6',
            backgroundColor: gradient,
            fill: true,
            tension: 0.45,
            pointRadius: 5,
            pointBackgroundColor: '#3b82f6'
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