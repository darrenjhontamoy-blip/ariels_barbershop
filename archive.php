<?php
session_start();
include '../config.php';

/* ADMIN ONLY */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* RESTORE / DELETE LOGIC */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // RESTORE
    if (!empty($_POST['restore_id'])) {
        $id = (int)$_POST['restore_id'];
        $stmt = $conn->prepare("UPDATE appointments SET status='Pending', cancel_reason=NULL, cancelled_by=NULL WHERE id=? AND status='Cancelled'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header("Location: archive.php");
        exit();
    }

    // DELETE
    if (!empty($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id=? AND status='Cancelled'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header("Location: archive.php");
        exit();
    }
}

/* FETCH CANCELLED APPOINTMENTS */
$query = "SELECT id, customer_name, barber_name, appointment_date, appointment_time, cancel_reason, cancelled_by 
          FROM appointments 
          WHERE status='Cancelled' 
          ORDER BY appointment_date DESC, appointment_time DESC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cancelled Appointments | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    margin:0;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background:#f4f6f9;
    color:#111827;
}
/* MAIN CONTENT */
.main-content {
    margin-left: 300px; /* dagdag ng 30px para may breathing room */
    padding: 30px 40px 30px 30px;
}

/* Optional: responsive para sa smaller screens */
@media(max-width:768px){ 
    .main-content {
        margin-left: 0;
        padding: 20px;
    }
}
/* HEADER CARD */
.header-card { background: #e3e6ff; padding: 20px 25px; border-radius: 15px; box-shadow: 0 8px 24px rgb(115 129 199 / 15%); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
.header-card h1 { font-weight:700; font-size:24px; color:#3f51b5; text-transform: uppercase; letter-spacing: 0.05em; margin:0; }
.header-card p { margin:0; font-size:14px; color: #4e56a5; }
/* TABLE */
.table-wrapper { background:#fff; padding:15px; border-radius:10px; box-shadow:0 4px 12px rgb(0 0 0 / 5%); overflow-x:auto; }
table { width:100%; border-collapse: collapse; font-size:14px; }
th, td { padding:10px 12px; border: 1px solid #d1d5db; text-align:left; }
th { background:#f3f4f6; color:#1f2937; font-weight:700; position: sticky; top:0; z-index:2; }
tr:nth-child(even) { background:#f9fafb; }
tr:hover { background:#e0f2fe; }
/* Status badge */
.status { padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold; color:#fff; background:#ef4444; }
/* Buttons */
button { padding:4px 10px; border-radius:4px; font-size:12px; border:none; cursor:pointer; transition: background 0.2s ease; }
.restore-btn { background:#22c55e; color:#fff; }
.restore-btn:hover { background:#16a34a; }
.delete-btn { background:#ef4444; color:#fff; }
.delete-btn:hover { background:#dc2626; }
/* Flex row for buttons */
.action-buttons { display:flex; gap:6px; }
.action-buttons form { margin:0; }
/* No data */
.no-data { text-align:center; padding:20px; color:#6b7280; }
/* Responsive */
@media(max-width:768px){ 
    .main-content { margin-left:0; padding:20px; } 
    th, td { padding:8px; font-size:12px; } 
    .action-buttons { flex-direction: column; gap:4px; } 
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

<?php include 'sidebar.php'; ?>

<body>

<div class="main-content">
    <div class="header-card">
        <div><h1>Cancelled Appointments</h1></div>
        <div><!-- optional logo or button --></div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Barber</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Reason</th>
                    <th>Cancelled By</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['customer_name'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($row['barber_name'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($row['appointment_date'] ?: '-') ?></td>
                            <td><?= htmlspecialchars(date('h:i A', strtotime($row['appointment_time'] ?: '00:00:00'))) ?></td>
                            <td><?= htmlspecialchars($row['cancel_reason'] ?: '-') ?></td>
                            <td><?= htmlspecialchars(ucfirst($row['cancelled_by'] ?: '-')) ?></td>
                            <td><span class="status">Cancelled</span></td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST">
                                        <input type="hidden" name="restore_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="restore-btn">Restore</button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="delete-btn">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="no-data">No cancelled appointments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>