<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* =======================
   FETCH WALK-IN LIST BASED ON APPOINTMENTS STATUS 'Pending'
   Pinag-synchronize status ng walkin_queue sa appointments
======================= */
$res = mysqli_query($conn, "
    SELECT w.id, w.customer_name, w.service, w.barber_name,
           w.appointment_date, w.appointment_time,
           COALESCE(a.status, w.status) AS status
    FROM walkin_queue w
    LEFT JOIN appointments a ON w.appointment_id = a.id
    WHERE COALESCE(a.status, w.status) = 'Pending'
    ORDER BY w.appointment_date ASC, w.appointment_time ASC
");

/* =======================
   FETCH COUNT PARA SA PAREHONG QUERY
======================= */
$totalCount = 0;
if ($res) {
    $totalCount = mysqli_num_rows($res);
}

/* =======================
   FORCE NO CACHE PARA LAGING FRESH DATA
======================= */
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Walk-In Customers</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;margin:0;padding:0;}
body{background:#f4f6f9;}
.main{margin-left:240px;padding:30px;}

/* PAGE HEADER */
.page-header{
    background:#1e40af;color:#fff;
    padding:20px 25px;border-radius:14px;
    margin-bottom:25px;box-shadow:0 6px 16px rgba(0,0,0,.08);
}
.page-header h1{font-size:26px;margin-bottom:5px;}
.page-header p{font-size:14px;color:#e0e7ff;}

/* CARD */
.card{
    background:#fff;border-radius:14px;padding:20px;
    box-shadow:0 6px 16px rgba(0,0,0,.08);
    overflow-x:auto;
}

/* GRID TABLE */
.table {
    display: grid;
    grid-template-columns: 55px 1.5fr 1.5fr 1.5fr 140px 140px 120px;
    width: 100%;
}

.table .head,
.table .row {
    display: contents;
}

.table .cell {
    padding: 12px 14px;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border-bottom: 1px solid #e5e7eb;
}

.table .head .cell {
    background: #f1f3f5;
    font-size: 12px;
    text-transform: uppercase;
    color: #444;
    letter-spacing: .5px;
    font-weight: 700;
}

.table .row:hover .cell {
    background: #f9fafb;
}

.table .cell.center {
    text-align: center;
}

/* BADGES */
.badge{
    padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;
    display:inline-block;
}
.Pending{background:#fde68a;color:#92400e;}
.Completed{background:#22c55e;color:#fff;}
.Cancelled{background:#ef4444;color:#fff;}

/* EMPTY MESSAGE */
.empty {
    padding: 30px;
    color: #777;
    grid-column: 1 / -1;
    text-align: center;
}

/* BACKGROUND & MAIN */
body{
    margin:0;
    background: linear-gradient(135deg, #0b3d91 0%, #1e3a8a 40%, #7f1d1d 70%, #c1121f 100%);
    min-height:100vh;
}
.main{
    margin-left:270px;
    padding:30px 40px 30px 30px;
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="page-header">
        <h1>Walk-In Customers</h1>
        <p>Pending walk-in queue (<?= $totalCount ?>)</p>
    </div>

    <div class="card">
        <div class="table">
            <div class="head">
                <div class="cell center">ID</div>
                <div class="cell">Customer</div>
                <div class="cell">Service</div>
                <div class="cell">Barber</div>
                <div class="cell center">Date</div>
                <div class="cell center">Time</div>
                <div class="cell center">Status</div>
            </div>

            <?php if($res && $totalCount > 0): ?>
                <?php while($r = mysqli_fetch_assoc($res)): ?>
                    <div class="row">
                        <div class="cell center"><?= $r['id'] ?></div>
                        <div class="cell"><?= htmlspecialchars($r['customer_name']) ?></div>
                        <div class="cell"><?= htmlspecialchars($r['service']) ?></div>
                        <div class="cell"><?= htmlspecialchars($r['barber_name']) ?></div>
                        <div class="cell center"><?= date('M d, Y', strtotime($r['appointment_date'])) ?></div>
                        <div class="cell center"><?= date('h:i A', strtotime($r['appointment_time'])) ?></div>
                        <div class="cell center">
                            <span class="badge <?= htmlspecialchars($r['status']) ?>">
                                <?= htmlspecialchars($r['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty">No Pending walk-in customers.</div>
            <?php endif; ?>

        </div> 
    </div>
</div>

</body>
</html>