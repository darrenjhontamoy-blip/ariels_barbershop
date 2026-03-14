<?php
session_start();
include '../config.php';

/* ADMIN ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* SEARCH */
$search = $_GET['search'] ?? '';
$searchEsc = mysqli_real_escape_string($conn, $search);

/* FETCH ONLINE APPOINTMENTS */
$sql = "SELECT * FROM appointments WHERE appointment_source='online'";
if ($searchEsc) {
    $sql .= " AND customer_name LIKE '%$searchEsc%'";
}
$sql .= " ORDER BY appointment_date ASC, appointment_time ASC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Online Appointments | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- FONT AWESOME 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;margin:0;padding:0}
body{background:#f4f6f9;color:#333}

/* MAIN */
.main{margin-left:240px;padding:30px}

/* HEADER */
.top-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    background:#e3e6ff;
    color:#3f51b5;
    padding:20px 30px;
    border-radius:15px;
    box-shadow:0 8px 24px rgb(115 129 199 / 15%);
    margin-bottom:25px;
}
.top-header h1{font-size:28px;font-weight:700;text-transform:uppercase}
.logo img{height:40px}

/* SEARCH */
.search-box{
    position: relative;
    max-width: 300px;
    margin-bottom:20px;
}
.search-box input{
    padding:10px 40px 10px 15px; /* extra padding for icon */
    width:100%;
    border-radius:10px;
    border:1px solid #d1d5db;
    font-size:14px;
}
.search-box input:focus{
    outline:none;
    border-color:#3f51b5;
    box-shadow:0 0 5px rgba(63,81,181,.3);
}
.search-box i{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    color:#777;
    pointer-events:none;
    font-size:14px;
}

/* TABLE CARD */
.table-wrapper {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    padding: 0; /* table mismo ang laman */
    
    /* Gradient border effect */
    border: 3px solid transparent; /* important */
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(white, white), /* loob ng box */
        linear-gradient(135deg, #3b82f6, #a855f7, #ef4444); /* gradient border */
    
    box-shadow: 0 6px 18px rgba(0,0,0,.08);
}
/* TABLE */
/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    text-align: center; /* center lahat ng cells by default */
}

th, td {
    padding: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 14px;
    text-align: center; /* siguradong naka-center ang text */
}

th {
    background: #dbeafe;
    font-size: 13px;
    text-transform: uppercase;
    color: #1e40af;
}
tr:nth-child(even){background:#f9fafb}
tr:hover{background:#eef2ff;transition:0.2s}

/* STATUS BADGES */
.status{
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}
.Pending{background:#fde68a;color:#92400e}     /* yellow */
.Completed{background:#bbf7d0;color:#166534}   /* green */
.Accepted{background:#bbf7d0;color:#166534}    /* green */
.Cancelled{background:#fecaca;color:#7f1d1d}   /* red */

/* EMPTY ROW */
.empty{text-align:center;padding:25px;color:#777}

/* RESPONSIVE */
@media(max-width:768px){
    .main{padding:20px;margin-left:0}
    .search-box input{width:100%}
    table,th,td{font-size:13px}
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
        <h1>ONLINE APPOINTMENTS</h1>
        <div class="logo">
            <img src="arslogo.jpg" alt="Logo">
        </div>
    </div>

    <!-- SEARCH -->
    <form class="search-box" method="GET">
        <input type="text" name="search" placeholder="Search customer..." value="<?= htmlspecialchars($search) ?>">
        <i class="fas fa-search"></i>
    </form>

    <!-- TABLE -->
    <div class="table-wrapper">
        <table>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Barber</th>
                <th>Status</th>
            </tr>

            <?php if(mysqli_num_rows($result)): ?>
                <?php while($r=mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= date('M d, Y',strtotime($r['appointment_date'])) ?></td>
                    <td><?= date('h:i A',strtotime($r['appointment_time'])) ?></td>
                    <td><?= htmlspecialchars($r['customer_name']) ?></td>
                    <td><?= htmlspecialchars($r['service']) ?></td>
                    <td><?= htmlspecialchars($r['barber_name']) ?></td>
                    <td>
                        <?php
                            $status = $r['status'] ?: 'Pending';
                            $statusClass = ($status==='Completed'||$status==='Accepted') ? 'Completed' : $status;
                        ?>
                        <span class="status <?= $statusClass ?>"><?= $status ?></span>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="empty">No online appointments found</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>
</body>
</html>
