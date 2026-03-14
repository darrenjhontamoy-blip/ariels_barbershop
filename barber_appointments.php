<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("mailer.php");
session_start();
include '../config.php';

/* ======================
   BARBER ONLY
===================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'barber') {
    header("Location: ../login.php");
    exit();
}

$barberName = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Barber';
$barberNameEsc = mysqli_real_escape_string($conn, $barberName);

/* ======================
   UPDATE STATUS
===================== */
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['appointment_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $message = "";

    if ($status === 'Cancelled') {
        $cancelReason = mysqli_real_escape_string($conn, $_POST['cancel_reason'] ?? 'Cancelled by barber');
        mysqli_query($conn, "
            UPDATE appointments
            SET status='Cancelled',
                cancelled_by='barber',
                cancelled_at=NOW(),
                cancel_reason='$cancelReason'
            WHERE id=$id
            AND barber_name='$barberNameEsc'
        ");
        $message = "Your appointment was CANCELLED by the barber. Reason: $cancelReason";

    } elseif ($status === 'Accepted') {
        mysqli_query($conn, "
            UPDATE appointments
            SET status='Accepted'
            WHERE id=$id
            AND barber_name='$barberNameEsc'
        ");
        $message = "Your appointment has been ACCEPTED by the barber.";

  } elseif ($status === 'Completed') {

    // 1️⃣ Update appointments table
    mysqli_query($conn, "
        UPDATE appointments
        SET status='Completed',
            payment_status='Paid',
            payment_date=NOW()
        WHERE id=$id
        AND barber_name='$barberNameEsc'
    ");

    // 2️⃣ Get appointment details
    $resDetails = mysqli_query($conn, "
        SELECT customer_name, service, barber_name 
        FROM appointments 
        WHERE id=$id
        LIMIT 1
    ");

    if($details = mysqli_fetch_assoc($resDetails)){

        // 3️⃣ Check if payment already exists
        $checkPayment = mysqli_query($conn, "
            SELECT id FROM payments 
            WHERE appointment_id=$id
            LIMIT 1
        ");

        if(mysqli_num_rows($checkPayment) == 0){

            // 4️⃣ INSERT payment record
            mysqli_query($conn, "
                INSERT INTO payments
                (customer_name, service, barber_name, payment_method, payment_status, appointment_id, payment_date)
                VALUES (
                    '".mysqli_real_escape_string($conn,$details['customer_name'])."',
                    '".mysqli_real_escape_string($conn,$details['service'])."',
                    '".mysqli_real_escape_string($conn,$details['barber_name'])."',
                    'Cash',
                    'Paid',
                    $id,
                    NOW()
                )
            ");

        } else {

            // If exists, update it
            mysqli_query($conn, "
                UPDATE payments
                SET payment_status='Paid',
                    payment_date=NOW()
                WHERE appointment_id=$id
            ");
        }
    }

    $message = "Your haircut is DONE. Thank you for choosing Ariel's Barbershop!";
}

    // SEND NOTIFICATION + EMAIL
    $res = mysqli_query($conn, "SELECT customer_name, email_address FROM appointments WHERE id=$id");
    if ($row = mysqli_fetch_assoc($res)) {
        // Fetch user_id based on email
$res_user = mysqli_query($conn, "
    SELECT id FROM users 
    WHERE email_address='".mysqli_real_escape_string($conn,$row['email_address'])."' 
    LIMIT 1
");

if ($user_row = mysqli_fetch_assoc($res_user)) {
    $user_id = (int)$user_row['id'];

    mysqli_query($conn, "
        INSERT INTO notifications (user_id, message)
        VALUES ($user_id, '".mysqli_real_escape_string($conn,$message)."')
    ");
} else {
    // Optional: log if user not found
    error_log("User not found for email: ".$row['email_address']);
}
        if (!empty($row['email_address'])) {
            sendMail(
    $row['email_address'],
    "Appointment Update | Ariel’s Barbershop",
    $message,
    "Ariel’s Barbershop"
);

    }

    header("Location: barber_appointments.php?status=".$_GET['status']);
    exit();
}
}

/* ======================
   FETCH APPOINTMENTS
===================== */
$filter = $_GET['status'] ?? 'Total';
$isPendingFilter = $filter === 'Pending';
$isCompletedFilter = $filter === 'Completed';
$isCancelledFilter = $filter === 'Cancelled';

$where = "barber_name='$barberNameEsc'";
if ($isPendingFilter) {
    $where .= " AND (status='Pending' OR status='Accepted')";
} elseif ($isCompletedFilter) {
    $where .= " AND status='Completed'";
} elseif ($isCancelledFilter) {
    $where .= " AND status='Cancelled'";
}

$result = mysqli_query($conn, "
    SELECT * FROM appointments
    WHERE $where
    ORDER BY appointment_date DESC, appointment_time ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Barber Appointments</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* { box-sizing:border-box; font-family:'Segoe UI',Tahoma,sans-serif; }
body { margin:0; background:#f4f6f9; }

/* MAIN LAYOUT */
.main { margin-left:270px; padding:30px; }

/* HEADER */
.header-card {
    background: linear-gradient(135deg,#ffffff,#f9fafb);
    padding:24px 30px;
    border-radius:20px;
    box-shadow:0 8px 25px rgba(0,0,0,0.08);
    margin-bottom:25px;
    position:relative;
    transition: transform 0.3s;
}
.header-card:hover { transform: translateY(-3px); }
.header-card::before {
    content:'';
    position:absolute; top:0; left:0;
    width:100%; height:6px;
    border-radius:20px 20px 0 0;
    background: linear-gradient(90deg,#6366f1,#22c55e);
}
.header-card h1 { margin:0; font-size:24px; font-weight:700; }
.header-card p { margin-top:6px; font-size:14px; color:#4b5563; }

/* FILTER CARDS */
.filter-container { display:flex; gap:15px; margin-bottom:25px; flex-wrap: wrap; }
.filter-card {
    background:#fff;
    padding:14px 20px;
    border-radius:14px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
    text-decoration:none;
    color:#111;
    font-weight:600;
    transition: all 0.3s;
}
.filter-card:hover { background:#e6f5e9; transform: translateY(-2px); }
.filter-card.active { background:#22c55e; color:#fff; box-shadow:0 6px 20px rgba(34,197,94,0.4); }

/* TABLE CARD */
.card {
    background:#fff;
    border-radius:20px;
    box-shadow:0 8px 25px rgba(0,0,0,0.08);
    padding:20px;
    overflow-x:auto;
}
table { width:100%; border-collapse:collapse; }
th, td { text-align:center; padding:14px; font-size:14px; vertical-align:middle; }
th { background:#f3f4f6; color:#374151; font-weight:600; }
tr:nth-child(even){ background:#fafafa; }
tr:hover{ background:#f0fdf4; }

/* STATUS BADGES */
.status-badge {
    padding:6px 12px;
    border-radius:12px;
    font-size:13px;
    font-weight:600;
    color:#fff;
    display:inline-block;
    min-width:80px;
}
.status-pending{background:#f59e0b;}
.status-accepted{background:#22c55e;}
.status-completed{background:#3b82f6;}
.status-cancelled{background:#ef4444;}

/* ACTION BUTTONS */
.btn {
    padding:7px 14px;
    border:none;
    border-radius:8px;
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    color:#fff;
    transition: all 0.3s;
}
.btn-accept { background:#22c55e; }
.btn-accept:hover { background:#16a34a; transform: translateY(-2px); }
.btn-cancel { background:#ef4444; }
.btn-cancel:hover { background:#dc2626; transform: translateY(-2px); }
.btn-done { background:#3b82f6; }
.btn-done:hover { background:#2563eb; transform: translateY(-2px); }

.action-container { display:flex; gap:8px; justify-content:center; align-items:center; flex-wrap:wrap; }

/* CANCEL FORM */
.cancel-reason-select, .cancel-reason-other {
    padding:5px 8px;
    font-size:13px;
    border-radius:6px;
    border:1px solid #cbd5e1;
    width:120px;
}
.cancel-reason-other { display:none; }

/* RESPONSIVE */
@media(max-width:768px) {
    .main { padding:20px; margin-left:0; }
    .filter-container { justify-content:flex-start; overflow-x:auto; }
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
/* ===== CENTERED WHITE HEADER ===== */
.page-header{
    text-align:center;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    padding:28px;
    border-radius:22px;
    margin-bottom:30px;
    box-shadow:0 15px 30px rgba(0,0,0,.35);
}

.page-header h1{
    margin:0;
    font-size:28px;
    color:#ffffff;
    font-weight:800;
    letter-spacing:1px;
}

.page-header p{
    margin-top:6px;
    font-size:14px;
    color:#e5e7eb;
}
</style>

</head>
<body>

<?php require_once 'sidebar_barber.php'; ?>

<div class="main">

<div class="page-header">
    <h1>MY APPOINTMENTS</h1>
    <p><?= $filter ?> Appointments</p>
</div>

<div class="filter-container">
    <a href="barber_appointments.php" class="filter-card <?= $filter==='Total'?'active':'' ?>">Total Appointments</a>
    <a href="barber_appointments.php?status=Pending" class="filter-card <?= $isPendingFilter?'active':'' ?>">Pending</a>
    <a href="barber_appointments.php?status=Completed" class="filter-card <?= $isCompletedFilter?'active':'' ?>">Completed</a>
    <a href="barber_appointments.php?status=Cancelled" class="filter-card <?= $isCancelledFilter?'active':'' ?>">Cancelled</a>
</div>

<div class="card">
<table>
<tr>
<th>Date</th>
<th>Customer</th>
<th>Service</th>
<th>Hairstyle</th>
<th>Time</th>
<th>Status / Action</th>
</tr>

<?php if(mysqli_num_rows($result)>0): ?>
<?php while($row=mysqli_fetch_assoc($result)): ?>
<tr>
<td><?= htmlspecialchars($row['appointment_date']) ?></td>

<td>
    <strong><?= htmlspecialchars($row['customer_name']) ?></strong>
</td>

<td><?= htmlspecialchars($row['service']) ?></td>

<td>
    <?php if(!empty($row['hairstyle'])): ?>
        <?= htmlspecialchars($row['hairstyle']) ?>
    <?php else: ?>
        <span style="color:#9ca3af;">Not specified</span>
    <?php endif; ?>
</td>

<td><?= date("h:i A",strtotime($row['appointment_time'])) ?></td>

<td>
<?php if($isPendingFilter): ?>
    <?php if($row['status']=='Pending'): ?>
        <div class="action-container">
            <form method="POST">
                <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="status" value="Accepted">
                <button name="update_status" class="btn btn-accept">Accept</button>
            </form>

            <form method="POST" class="cancel-form">
                <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="status" value="Cancelled">

                <select name="cancel_reason_select" class="cancel-reason-select" onchange="toggleOtherReason(this)" required>
                    <option value="">Reason</option>
                    <option value="Fully booked">Fully booked</option>
                    <option value="Schedule conflict">Schedule conflict</option>
                    <option value="Personal emergency">Personal emergency</option>
                    <option value="Other">Other</option>
                </select>

                <input type="text" name="cancel_reason_other" 
                       class="cancel-reason-other" 
                       placeholder="Type reason">

                <button name="update_status" class="btn btn-cancel">Cancel</button>
            </form>
        </div>

    <?php elseif($row['status']=='Accepted'): ?>
        <form method="POST">
            <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
            <input type="hidden" name="status" value="Completed">
            <button name="update_status" class="btn btn-done">Done</button>
        </form>

    <?php else: ?>
        <span class="status-badge <?= 'status-'.strtolower($row['status']) ?>">
            <?= htmlspecialchars($row['status']) ?>
        </span>
    <?php endif; ?>

<?php else: ?>
    <span class="status-badge <?= 'status-'.strtolower($row['status']) ?>">
        <?= htmlspecialchars($row['status']) ?>
    </span>
<?php endif; ?>
</td>

</tr>
<?php endwhile; ?>

<?php else: ?>
<tr>
<td colspan="6" style="text-align:center;color:#777">
No appointments found
</td>
</tr>
<?php endif; ?>

</table>
</div>

</div>

<script>
function toggleOtherReason(select){
    var form = select.closest('.cancel-form');
    var other = form.querySelector('.cancel-reason-other');
    if(select.value==='Other'){other.style.display='block';other.required=true;}
    else{other.style.display='none';other.required=false;}
}

document.querySelectorAll('.cancel-form').forEach(function(form){
    form.addEventListener('submit',function(e){
        var select=form.querySelector('.cancel-reason-select');
        var other=form.querySelector('.cancel-reason-other');
        var hidden=document.createElement('input');
        hidden.type='hidden';
        hidden.name='cancel_reason';
        if(select.value==='Other'){
            if(other.value.trim()===''){e.preventDefault();alert('Please type your reason');return false;}
            hidden.value=other.value.trim();
        }else{hidden.value=select.value;}
        form.appendChild(hidden);
    });
});
</script>

</body>
</html>