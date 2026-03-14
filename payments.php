<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../config.php';

/* ===================== ADMIN ONLY ===================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ===================== SEARCH ===================== */
$search = $_GET['search'] ?? '';
$searchEsc = $conn->real_escape_string($search);

/* ===================== STATS ===================== */
$stats = ['total'=>0,'paid'=>0,'pending'=>0];

/* Total appointments accepted/completed */
$resTotal = $conn->query("SELECT COUNT(*) AS total FROM appointments WHERE status IN ('Accepted','Completed')");
$stats['total'] = $resTotal->fetch_assoc()['total'] ?? 0;

/* Paid (Completed) */
$resPaid = $conn->query("SELECT COUNT(*) AS paid FROM appointments WHERE status='Completed'");
$stats['paid'] = $resPaid->fetch_assoc()['paid'] ?? 0;

/* Pending (Accepted) */
$resPending = $conn->query("SELECT COUNT(*) AS pending FROM appointments WHERE status='Accepted'");
$stats['pending'] = $resPending->fetch_assoc()['pending'] ?? 0;

/* ===================== FETCH APPOINTMENTS ===================== */
$query = "SELECT * FROM appointments WHERE status IN ('Accepted','Completed')";
if(!empty($searchEsc)){
    $query .= " AND (customer_name LIKE '%$searchEsc%' 
                OR service LIKE '%$searchEsc%' 
                OR barber_name LIKE '%$searchEsc%')";
}
$query .= " ORDER BY appointment_date ASC, appointment_time ASC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payments | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;margin:0;padding:0;color:#111;}
body{background:#f4f6f9;}
.main{margin-left:240px;padding:30px;}

/* Header */
.top-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#e3e6ff;
    padding:15px 30px;
    border-radius:15px;
    box-shadow:0 8px 24px rgb(115 129 199 / 15%);
    margin-bottom:25px;
}
.top-header h1{
    font-weight:700;
    font-size:24px;
    color:#3f51b5;
    text-transform:uppercase;
}
<div class="gradient-border">
    <div class="inner-card stat">
        <h3>Total</h3>
        <h2><?= $stats['total'] ?></h2>
    </div>
</div>
/* Stats */
.stats{display:flex;gap:20px;margin-bottom:25px;}

.stat{
    background:white;
    flex:1;
    padding:20px 28px;
    border-radius:15px;
    box-shadow:0 6px 24px rgb(115 129 199 / 10%);
    text-align:center;
}
.stat h3{
    font-size:13px;
    font-weight:600;
    color:#7a85cc;
    margin-bottom:8px;
    text-transform:uppercase;
}
.stat h2{
    font-size:32px;
    font-weight:700;
    color:#3f51b5;
}

/* Chart */

.chart-card{
    background:#0f172a;
    padding:28px;
    border-radius:22px;
    box-shadow:0 12px 30px rgba(0,0,0,.25);
    margin-bottom:30px;
}
.chart-card h2{
    color:#ffffff;
    margin-bottom:18px;
    font-size:20px;
}
.chart-card canvas{
    max-height:300px;
}

/* Table */
.card{
    background:white;
    padding:22px;
    border-radius:14px;
    box-shadow:0 6px 16px rgba(0,0,0,.08);
    overflow-x:auto;
}
table{width:100%;border-collapse:separate;border-spacing:0 8px;}
th,td{padding:16px 24px;font-weight:600;font-size:14px;text-align:left;}
th{
    background:#d7dbff;
    color:#4e56a5;
    text-transform:uppercase;
}
tr:nth-child(even){background:#f8faff;}
tr:hover{background:#e1e6ff;}

/* Status badges */
.status-badge{
    padding:5px 12px;
    border-radius:12px;
    font-size:13px;
    font-weight:700;
    display:inline-block;
    min-width:70px;
}
.status-Paid{background:#22c55e;color:#fff;}
.status-Pending{background:#d97706;color:#fff;}

@media(max-width:768px){
    .main{margin-left:0;padding:20px;}
    .stats{flex-direction:column;}
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

    <!-- HEADER -->
    <div class="top-header">
        <h1>Payments</h1>
    </div>

    <!-- STATS -->
    <div class="stats">
        <div class="stat">
            <h3>Total</h3>
            <h2><?= $stats['total'] ?></h2>
        </div>
        <div class="stat">
            <h3>Paid</h3>
            <h2><?= $stats['paid'] ?></h2>
        </div>
        <div class="stat">
            <h3>Pending</h3>
            <h2><?= $stats['pending'] ?></h2>
        </div>
    </div>

    <!-- CHART -->
    <div class="chart-card">
        <h2>Payments Overview</h2>
        <canvas id="paymentsChart"></canvas>
    </div>

    <!-- TABLE -->
    <div class="card">
        <?php if($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Barber</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td><?= htmlspecialchars($row['service']) ?></td>
                    <td><?= htmlspecialchars($row['barber_name']) ?></td>
                    <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                    <td><?= date('h:i A', strtotime($row['appointment_time'])) ?></td>
                    <td>
                        <?php
                            // Dynamic payment status
                            if($row['status'] === 'Accepted') {
                                echo '<span class="status-badge status-Pending">Pending</span>';
                            } elseif($row['status'] === 'Completed') {
                                echo '<span class="status-badge status-Paid">Paid</span>';
                            } else {
                                echo '<span class="status-badge">N/A</span>';
                            }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="text-align:center;padding:20px;color:#777;">No appointments found.</p>
        <?php endif; ?>
    </div>

</div>

<script>
new Chart(document.getElementById('paymentsChart'), {
    data:{
        labels:['Pending','Paid'],
        datasets:[
            {
                type:'bar',
                label:'Count',
                data:[<?= $stats['pending'] ?>, <?= $stats['paid'] ?>],
                backgroundColor:'#ffffff',
                borderRadius:10,
                barThickness:50
            },
            {
                type:'line',
                label:'Trend',
                data:[<?= $stats['pending'] ?>, <?= $stats['paid'] ?>],
                borderColor:'#facc15',
                backgroundColor:'#facc15',
                tension:.4,
                pointRadius:6
            }
        ]
    },
    options:{
        plugins:{legend:{labels:{color:'#e5e7eb'}}},
        scales:{
            x:{ticks:{color:'#e5e7eb'},grid:{display:false}},
            y:{ticks:{color:'#e5e7eb'},grid:{color:'rgba(255,255,255,.1)'}}
        }
    }
});
</script>

</body>
</html>
