<?php
session_start();
include '../config.php';

/* =====================
   ADMIN ONLY
===================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* =====================
   FETCH PENDING PAYMENTS ONLY
===================== */
$result = mysqli_query($conn, "
    SELECT * 
    FROM appointments 
    WHERE status='Completed' AND payment_status='Pending'
    ORDER BY appointment_date ASC, appointment_time ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pending Payments | Ariel's Barbershop</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:Poppins,Segoe UI,Arial,sans-serif;background:#f0f4f8;color:#333;padding:40px;}
.main{max-width:1000px;margin:auto;}
h1{margin-bottom:30px;color:#1e293b;}
.card{background:#fff;padding:20px;border-radius:15px;box-shadow:0 6px 20px rgba(0,0,0,0.1);}
.table-wrapper{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th,td{padding:12px 14px;text-align:left;border-bottom:1px solid #eee;font-size:14px;}
th{background:#f1f3f5;text-transform:uppercase;font-size:12px;letter-spacing:.5px;color:#444;}
tr:hover{background:#f9fafb;}
.status{padding:5px 10px;border-radius:12px;font-weight:600;color:#fff;}
.status.Pending{background:#f59e0b;}
.action-btn{padding:6px 12px;border-radius:6px;font-size:12px;text-decoration:none;color:#fff;background:#4f46e5;display:inline-block;}
.empty{text-align:center;padding:30px;color:#777;}
</style>
</head>
<body>

<div class="main">
<h1>Pending Payments</h1>

<div class="card table-wrapper">
    <table>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Service</th>
            <th>Barber</th>
            <th>Date</th>
            <th>Time</th>
            <th>Payment Status</th>
            <th>Action</th>
        </tr>

        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['service']) ?></td>
                <td><?= htmlspecialchars($row['barber_name']) ?></td>
                <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                <td><?= date('h:i A', strtotime($row['appointment_time'])) ?></td>
                <td><span class="status Pending"><?= $row['payment_status'] ?></span></td>
                <td>
                    <a href="payments.php?id=<?= $row['id'] ?>" class="action-btn">Mark Payment</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" class="empty">No pending payments found</td></tr>
        <?php endif; ?>
    </table>
</div>
</div>

</body>
</html>
