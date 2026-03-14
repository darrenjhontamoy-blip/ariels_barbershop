<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../config.php';

/* ADMIN ONLY */
if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

/* DELETE LOGIC */
if (isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];

    if ($_SESSION['role'] === 'admin') {
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: appointments.php");
    exit();
}

/* FETCH APPOINTMENTS */
$user_id = $_SESSION['user_id'];
$where = [];

if ($_SESSION['role'] !== 'admin') {
    $where[] = "user_id = $user_id";
}

if (!empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $where[] = "status = '$status'";
}

if (!empty($_GET['today'])) {
    $today = date('Y-m-d');
    $where[] = "appointment_date = '$today'";
}

$where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$query = "SELECT *, COALESCE(NULLIF(appointment_source, ''), 'walkin') AS appointment_source 
          FROM appointments 
          $where_sql 
          ORDER BY appointment_date ASC, appointment_time ASC";

$result = $conn->query($query);

/* PAGE TITLE */
$pageTitle = "All Appointments Overview";
if (!empty($_GET['status'])) {
    $pageTitle = htmlspecialchars($_GET['status']) . " Appointments Overview";
} elseif (!empty($_GET['today'])) {
    $pageTitle = "Today's Appointments Overview";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
* { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }
body { margin: 0; background: #f4f6f9; }

.main { margin-left: 240px; padding: 30px; }
.header-box { background: #2563eb; color: #fff; border-radius: 14px; padding: 20px 25px; margin-bottom: 20px; box-shadow: 0 6px 16px rgba(0,0,0,.08); }
.header-box h1 { margin: 0; font-size: 28px; }
.header-box p { margin: 5px 0 0; font-size: 14px; color: rgba(255,255,255,0.8); }

.card { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 6px 16px rgba(0,0,0,.08); overflow-x: auto; }

table { width: 100%; border-collapse: collapse; table-layout: auto; }
th, td { padding: 10px; font-size: 14px; white-space: normal; word-break: break-word; overflow: hidden; text-overflow: ellipsis; }
th { background: #f1f3f5; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; color: #444; border-radius: 10px; }
tr { border-bottom: 1px solid #e5e7eb; }
tr:hover { background: #f9fafb; }

/* Full name column wraps */
.full-name { white-space: normal; word-break: break-word; }

/* Status badges */
.status { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
.Pending { background: #fde68a; color: #92400e; }
.Completed { background: #bbf7d0; color: #166534; }
.Cancelled { background: #fecaca; color: #7f1d1d; }

/* Source badge */
.source { padding: 5px 12px; border-radius: 14px; font-size: 11px; font-weight: 600; background: #e5e7eb; }

/* Action buttons */
.action-btn { padding: 6px 12px; border-radius: 8px; font-size: 12px; text-decoration: none; display: inline-block; margin: 2px; }
.edit { background: #2563eb; color: #fff; border: none; cursor: pointer; }
.delete { background: #dc2626; color: #fff; border: none; cursor: pointer; }

/* Empty state */
.empty { text-align: center; padding: 30px; color: #777; }

/* Responsive */
@media(max-width:768px) {
    .main { margin-left: 0; padding: 15px; }
    table, thead, tbody, th, td, tr { display: block; width: 100%; }
    th { text-align: left; }
    tr { margin-bottom: 15px; border-bottom: 1px solid #ddd; }
    td { text-align: right; padding-left: 50%; position: relative; }
    td::before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        width: 45%;
        padding-left: 5px;
        font-weight: 600;
        text-align: left;
    }
}

/* Modal styles */
#deleteModal {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.modal-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
}
.modal-content {
    position: relative;
    background: white;
    padding: 25px 35px;
    border-radius: 14px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    max-width: 320px;
    width: 100%;
    text-align: center;
    font-size: 16px;
    color: #222;
}
.modal-buttons {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 15px;
}
.modal-buttons button { min-width: 70px; }
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
    <div class="header-box">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <p>Professional overview of all appointments</p>
    </div>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Barber</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Source</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td data-label="ID"><?= htmlspecialchars($row['id']) ?></td>
                        <td data-label="Customer" class="full-name"><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td data-label="Service"><?= htmlspecialchars($row['service']) ?></td>
                        <td data-label="Barber"><?= htmlspecialchars($row['barber_name']) ?></td>
                        <td data-label="Date"><?= htmlspecialchars(date('M d, Y', strtotime($row['appointment_date']))) ?></td>
                        <td data-label="Time"><?= htmlspecialchars(date('h:i A', strtotime($row['appointment_time']))) ?></td>
                        <td data-label="Status"><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                        <td data-label="Source"><span class="source"><?= htmlspecialchars($row['appointment_source']) ?></span></td>
                        <td data-label="Action">
                            <?php if($_SESSION['role'] === 'admin' || $row['user_id'] == $user_id): ?>
                                <a href="appointment_edit.php?id=<?= htmlspecialchars($row['id']) ?>" class="action-btn edit">Edit</a>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="delete_id" value="<?= htmlspecialchars($row['id']) ?>">
                                    <button type="submit" class="action-btn delete">Delete</button>
                                </form>
                            <?php else: ?>
                                <span>-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="empty">No appointments found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal HTML -->
<div id="deleteModal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <p>Are you sure you want to delete this appointment?</p>
        <div class="modal-buttons">
            <button id="confirmDeleteBtn" class="action-btn delete">Yes</button>
            <button id="cancelDeleteBtn" class="action-btn edit">No</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deleteModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const cancelBtn = document.getElementById('cancelDeleteBtn');

    let formToSubmit = null;

    document.querySelectorAll('form button.action-btn.delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            formToSubmit = this.closest('form');
            modal.style.display = 'flex';
        });
    });

    confirmBtn.addEventListener('click', () => {
        if (formToSubmit) formToSubmit.submit();
    });

    cancelBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        formToSubmit = null;
    });

    modal.querySelector('.modal-overlay').addEventListener('click', () => {
        modal.style.display = 'none';
        formToSubmit = null;
    });
});
</script>

</body>
</html>
