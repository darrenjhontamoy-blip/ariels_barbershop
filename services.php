<?php
session_start();
include '../config.php';

/* ADMIN ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM services ORDER BY service_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Services</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* { box-sizing: border-box; font-family:'Segoe UI',Tahoma,sans-serif; margin:0; padding:0; }
body { background:#f4f6f9; }

.main { margin-left: 240px; padding:30px; }

/* ===== HEADER ===== */
.top-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #e3e6ff; /* light purple */
    padding: 20px 30px;
    border-radius: 15px;
    box-shadow: 0 8px 24px rgb(115 129 199 / 15%);
    margin-bottom: 25px;
}
.top-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #3f51b5; /* darkish blue */
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.logo img { height: 40px; }

/* ===== CARD ===== */
.card {
    position: relative;
    border-radius: 18px;
    overflow: hidden;

    /* Gradient border */
    border: 3px solid transparent;
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(rgba(255,255,255,0.95), rgba(255,255,255,0.95)), /* loob */
        linear-gradient(135deg, #3b82f6, #a855f7, #ef4444); /* border gradient */

    box-shadow: 0 8px 25px rgba(0,0,0,.08);
    padding: 20px;
}
.card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; }
.card-header h3 { margin:0; font-size:18px; font-weight:600; }

/* ===== TABLE ===== */
table { width:100%; border-collapse: separate; border-spacing: 0 10px; }
table th,
table td {
    text-align: center;       /* horizontal center */
    vertical-align: middle;   /* vertical center */
}
th, td { padding:14px 20px; font-size:14px; vertical-align:middle; text-align:left; }
th {
    background:#d7dbff;
    color:#4e56a5;
    font-weight:600;
    text-transform: uppercase;
    letter-spacing:0.05em;
    border-top-left-radius:15px;
    border-top-right-radius:15px;
}
tr {
    background:white;
    border-radius:14px;
    box-shadow:0 1px 4px rgb(0 0 0 / 5%);
    transition: background-color 0.2s ease;
}
tr:nth-child(even){ background:#f8faff; }
tr:hover{ background:#e1e6ff; }

/* Status badges */
.status {
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
    color:#fff;
}
.available { background:#22c55e; } /* green */
.not-available { background:#ef4444; } /* red */

/* Actions */
.actions a {
    display:inline-block;
    padding:6px 10px;
    border-radius:8px;
    color:#fff;
    font-size:12px;
    text-decoration:none;
    margin-right:5px;
    font-weight:600;
    transition: opacity 0.2s;
}
.check { background:#4f46e5; } /* purple */
.reject { background:#ef4444; } /* red */
.edit { background:#facc15; color:#000; } /* yellow */
.actions a:hover { opacity:0.85; }

/* Responsive */
@media (max-width:768px){
    .main{ margin-left:0; padding:20px; }
    .top-header{ flex-direction:column; align-items:flex-start; }
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
        <h1>MANAGE SERVICES</h1>
        <div class="logo"><img src="arslogo.jpg" alt="Logo"></div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>All Services</h3>
        </div>

        <table>
            <tr>
                <th>Service Name</th>
                <th>Duration (min)</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['service_name']) ?></td>
                    <td><?= $row['duration'] ?></td>
                    <td>₱<?= number_format($row['price'],2) ?></td>
                    <td>
                        <?php $class = ($row['status'] === 'Available') ? 'available' : 'not-available'; ?>
                        <span class="status <?= $class ?>"><?= $row['status'] ?></span>
                    </td>
                    <td class="actions">
                        <a href="activate_service.php?service=<?= urlencode($row['service_name']) ?>" class="check">✔️</a>
                        <a href="deactivate_service.php?service=<?= urlencode($row['service_name']) ?>" class="reject">✖️</a>
                        <a href="edit_service.php?service=<?= urlencode($row['service_name']) ?>" class="edit">✏️</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;color:#777">No services found</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

</body>
</html>
