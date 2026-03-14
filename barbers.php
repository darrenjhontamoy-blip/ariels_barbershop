<?php
session_start();
include '../config.php';

/* ==========================
   ADMIN ONLY CHECK
========================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ==========================
   HANDLE APPROVE / REJECT
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['barber_id'])) {
    $barber_id = (int)$_POST['barber_id'];
    $action    = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Active';
        $notif  = 'Your barber account has been approved by admin.';
    } elseif ($action === 'reject') {
        $status = 'Rejected';
        $notif  = 'Your barber account has been rejected by admin.';
    } else {
        $status = null;
        $notif  = '';
    }

    if ($status) {
        // Update barber status
        $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=? AND role='barber'");
        $stmt->bind_param("si", $status, $barber_id);
        $stmt->execute();
        $stmt->close();

        // Send notification
        $stmt2 = $conn->prepare("
            INSERT INTO notifications (user_id, message, created_at)
            VALUES (?, ?, NOW())
        ");
        $stmt2->bind_param("is", $barber_id, $notif);
        $stmt2->execute();
        $stmt2->close();
    }

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* ==========================
   LIST OF BARBERS
========================== */
$result = mysqli_query(
    $conn,
    "SELECT id, fullname, email_address, username, status, online_status
     FROM users
     WHERE role='barber'
     ORDER BY fullname ASC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Barbers Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;}
body{margin:0;background:#f4f6f9;}
.main{margin-left:240px;padding:30px;}

/* Header */
.top-header{display:flex;justify-content:space-between;align-items:center;background:#e3e6ff;padding:15px 30px;border-radius:15px;box-shadow:0 8px 24px rgb(115 129 199 / 15%);margin-bottom:25px;}
.top-header h1{font-weight:700;font-size:24px;color:#3f51b5;text-transform:uppercase;}
.logo img{height:40px;}

/* Table */
/* TABLE WRAPPER WITH GRADIENT BORDER */
.table-wrapper {
    position: relative;
    border-radius: 18px;
    overflow: hidden;

    /* Gradient border effect */
    border: 3px solid transparent;
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(rgba(255,255,255,0.95), rgba(255,255,255,0.95)), /* loob */
        linear-gradient(135deg, #3b82f6, #a855f7, #ef4444); /* border gradient */

    box-shadow: 0 8px 25px rgba(0,0,0,.08);
}
table{width:100%;border-collapse:separate;border-spacing:0;font-size:15px;color:#333;}
.table-wrapper table th,
.table-wrapper table td {
    text-align: center;       /* horizontally center */
    vertical-align: middle;   /* vertically center */
}
thead tr{background:#c7d0fc;color:#1e40af;font-weight:700;text-transform:uppercase;font-size:13px;}
thead th{padding:16px 20px;text-align:left;}
tbody tr{background:#fff;transition:background-color .2s ease;}
tbody tr:nth-child(even){background:#f8faff;}
tbody tr:hover{background:#dde4ff;}
tbody td{padding:16px 20px;vertical-align:middle;}

/* Status badges */
.status{display:inline-block;padding:6px 14px;border-radius:20px;font-weight:600;font-size:13px;min-width:85px;text-align:center;}
.status.pending{color:#856404;background:#fff3cd;}
.status.active{color:#1e7e34;background:#d1e7dd;}
.status.rejected{color:#842029;background:#f8d7da;}
.status.online{color:#1e7e34;background:#d1e7dd;min-width:70px;margin-left:6px;}
.status.offline{color:#6c757d;background:#e2e3e5;min-width:70px;margin-left:6px;}

/* Buttons */
form{display:inline-flex;gap:8px;margin:0;}
button{padding:8px 18px;font-size:13px;font-weight:600;border:none;border-radius:8px;cursor:pointer;transition:.25s;}
button.approve{background:#22c55e;color:#fff;}
button.approve:hover{background:#16a34a;}
button.reject{background:#ef4444;color:#fff;}
button.reject:hover{background:#b91c1c;}
.no-data{text-align:center;padding:24px 0;color:#6b7280;font-style:italic;}
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

<div class="main">
    <div class="top-header">
        <h1>BARBERS MANAGEMENT</h1>
        <div class="logo">
            <img src="arslogo.jpg" alt="Logo" />
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Barber Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php $i=1; ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <?php 
                            $accountStatus = strtolower($row['status'] ?? 'pending');
                            if ($accountStatus === '') $accountStatus = 'pending';
                            $onlineStatus  = strtolower($row['online_status'] ?? 'offline');
                        ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['fullname']) ?></td>
                            <td><?= htmlspecialchars($row['email_address'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td>
                                <span class="status <?= $accountStatus ?>"><?= ucfirst($accountStatus) ?></span>
                                <?php if($accountStatus === 'active'): ?>
                                    <span class="status <?= $onlineStatus ?>"><?= ucfirst($onlineStatus) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($accountStatus === 'pending'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="barber_id" value="<?= $row['id'] ?>">
                                        <button name="action" value="approve" class="approve" type="submit">Approve</button>
                                        <button name="action" value="reject" class="reject" type="submit">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#6b7280;font-style:italic;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">No barbers found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
