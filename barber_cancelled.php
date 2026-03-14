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

$result = mysqli_query($conn, "
    SELECT id, customer_name, service, appointment_date, appointment_time, cancel_reason, cancelled_at
    FROM appointments
    WHERE barber_name = '$barberNameEsc' AND status = 'Cancelled'
    ORDER BY cancelled_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cancelled Appointments</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, sans-serif;
    margin: 0; padding: 0;
}
body {
    background: #f4f6f9;
    color: #111827;
}

/* MAIN CONTAINER */
.main {
    margin-left: 270px;
    padding: 30px 40px 30px 30px;
}

/* HEADER CARD */
.header-card {
    background: linear-gradient(135deg, #ffffff, #f9fafb);
    padding: 16px 24px;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    position: relative;
}
.header-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 5px;
    background: linear-gradient(90deg, #4f46e5, #22c55e);
}
.header-card h1 {
    margin: 0;
    font-size: 22px;
    color: #111827;
}
.header-card p {
    margin-top: 6px;
    font-size: 14px;
    color: #6b7280;
}

/* CARD */
.card {
    background: linear-gradient(135deg, #ffffff, #f8fafc);
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    padding: 20px;
    overflow-x: auto;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 14px 16px;
    font-size: 14px;
    text-align: left;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
    border-bottom: 1px solid #e5e7eb;
}
th {
    background: #f1f3f5;
    font-weight: 600;
    text-transform: uppercase;
    color: #444;
    letter-spacing: 0.5px;
}
tr:hover {
    background: #f9fafb;
}
td.reason-col {
    max-width: 200px;
    word-break: break-word;
}
.empty {
    text-align: center;
    padding: 30px;
    color: #777;
}
@media (max-width: 768px) {
    .main {
        margin-left: 0;
        padding: 20px;
    }
    table, thead, tbody, th, td, tr {
        display: block;
    }
    thead tr {
        display: none;
    }
    tr {
        margin-bottom: 20px;
        border-bottom: 2px solid #eee;
    }
    td {
        padding-left: 50%;
        position: relative;
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
    }
    td::before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        font-weight: 600;
        color: #444;
    }
    td.reason-col {
        max-width: 100%;
    }
}
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
</style>
</head>
<body>

<?php include 'sidebar_barber.php'; ?>

<div class="main">

    <div class="header-card">
        <h1>Cancelled Appointments</h1>
        <p>Manage your cancelled appointments</p>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Reason</th>
                    <th>Cancelled At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)):
                        // Safe formatting for cancelled_at
                        $cancelledAt = !empty($row['cancelled_at']) ? date('M d, Y h:i A', strtotime($row['cancelled_at'])) : '-';
                        $appointmentDate = !empty($row['appointment_date']) ? date('M d, Y', strtotime($row['appointment_date'])) : '-';
                        $appointmentTime = !empty($row['appointment_time']) ? date('h:i A', strtotime($row['appointment_time'])) : '-';
                    ?>
                        <tr>
                            <td data-label="ID"><?= htmlspecialchars($row['id']) ?></td>
                            <td data-label="Customer"><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td data-label="Service"><?= htmlspecialchars($row['service']) ?></td>
                            <td data-label="Date"><?= $appointmentDate ?></td>
                            <td data-label="Time"><?= $appointmentTime ?></td>
                            <td data-label="Reason" class="reason-col"><?= htmlspecialchars($row['cancel_reason']) ?: 'N/A' ?></td>
                            <td data-label="Cancelled At"><?= $cancelledAt ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty">No cancelled appointments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
