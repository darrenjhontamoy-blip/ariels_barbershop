<?php
session_start();
include '../config.php';

/* ADMIN ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* STATS */
$stats = ['total' => 0];
$resTotal = mysqli_query($conn,"SELECT COUNT(*) total FROM customers");
$stats['total'] = mysqli_fetch_assoc($resTotal)['total'] ?? 0;

/* SEARCH */
$search = $_GET['search'] ?? '';
$searchEsc = mysqli_real_escape_string($conn, $search);

/* FETCH CUSTOMERS */
$query = "
    SELECT id, fullname, phone, email, created_at
    FROM customers
";

if (!empty($searchEsc)) {
    $query .= "
        WHERE fullname LIKE '%$searchEsc%'
           OR phone LIKE '%$searchEsc%'
           OR email LIKE '%$searchEsc%'
    ";
}

$query .= " ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Customers</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />

<!-- FONT AWESOME 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
* {
  box-sizing: border-box;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  margin: 0; padding: 0;
  color: #111;
}

body {
  background: #f4f6f9;
}

.main {
  margin-left: 240px;
  padding: 30px 30px 40px;
}

/* HEADER */
.top-header{
    display:flex;justify-content:space-between;align-items:center;
    background:#e3e6ff;padding:20px 30px;border-radius:15px;
    box-shadow:0 8px 24px rgb(115 129 199 / 15%);
    margin-bottom:25px
}
.top-header h1{font-size:28px;font-weight:700;color:#3f51b5}
.logo img{height:40px}

/* ACTIONS */
.actions{display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap;margin-bottom:25px}
.search-box{position:relative;max-width:320px;width:100%}
.search-box input{
    width:100%;padding:10px 40px 10px 15px;
    border-radius:10px;border:1px solid #d1d5db
}
.search-box i{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#777}

/* Stats */
.stats {
  display: flex;
  gap: 20px;
  margin-bottom: 25px;
}
.stat {
  background: white;
  flex: 1;
  padding: 20px 28px;
  border-radius: 15px;
  box-shadow: 0 6px 24px rgb(115 129 199 / 10%);
  text-align: center;
}
.stat h3 {
  font-size: 13px;
  font-weight: 600;
  color: #7a85cc;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
  text-transform: uppercase;
}
.stat h2 {
  font-size: 32px;
  font-weight: 700;
  color: #3f51b5;
}

/* Table */
.table-wrapper,
.stat {
    position: relative;
    border-radius: 18px;
    overflow: hidden;

    /* Gradient border effect */
    border: 3px solid transparent; /* importante */
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(rgba(255,255,255,0.95), rgba(255,255,255,0.95)), /* loob */
        linear-gradient(135deg, #3b82f6, #a855f7, #ef4444); /* border gradient */

    box-shadow: 0 8px 25px rgba(0,0,0,.08);
}

/* Table specific tweaks so that inside is still nicely visible */
.table-wrapper table {
    background: transparent;
}

/* Optional: stats header text color adjustments for contrast */
.stat h3, .stat h2 {
    color: #111;
}
table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 8px;
}
th, td {
  padding: 16px 24px;
  font-weight: 600;
  font-size: 14px;
  vertical-align: middle;
  text-align: left;
}
th {
  background: #d7dbff;
  color: #4e56a5;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  border-top-left-radius: 20px;
  border-top-right-radius: 20px;
  border-bottom: none;
  font-weight: 700;
}
tr {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgb(0 0 0 / 0.05);
  transition: background-color 0.2s ease;
}
tr:nth-child(even) {
  background: #f8faff;
}
tr:hover {
  background: #e1e6ff;
}

/* Round corners on first and last cell of each row */
td:first-child {
  border-top-left-radius: 16px;
  border-bottom-left-radius: 16px;
}
td:last-child {
  border-top-right-radius: 16px;
  border-bottom-right-radius: 16px;
}

/* Muted text fallback */
.muted {
  color: #999;
  font-style: italic;
}

/* Responsive */
@media (max-width: 768px) {
  .main {
    margin-left: 0;
    padding: 20px 16px;
  }
  .top-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .stats {
    flex-direction: column;
  }
  .search-box {
    width: 100%;
    max-width: 100%;
  }
}

/* Scrollbar for table wrapper */
.table-wrapper::-webkit-scrollbar {
  height: 8px;
}
.table-wrapper::-webkit-scrollbar-thumb {
  background-color: #a3a9f7;
  border-radius: 8px;
}
.table-wrapper::-webkit-scrollbar-track {
  background: transparent;
}
/* ===== CENTER TEXT INSIDE TABLE CELLS ===== */
table th,
table td {
    text-align: center; /* lahat ng table text naka-center */
    vertical-align: middle;
}

/* ===== CENTER TEXT INSIDE STATS CARDS ===== */
.stat h3,
.stat h2 {
    text-align: center;
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

/* TOTAL CUSTOMERS BOX GRADIENT BORDER */
.stat {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    padding: 24px;
    text-align: center;
    
    border: 3px solid transparent;
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(rgba(255,255,255,0.95), rgba(255,255,255,0.95)), /* loob */
        linear-gradient(135deg, #3b82f6, #a855f7, #ef4444); /* border gradient */
    
    box-shadow: 0 8px 25px rgba(0,0,0,.08);
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
    <h1>CUSTOMERS MANAGEMENT</h1>
    <div class="logo"><img src="arslogo.jpg"></div>
</div>

<div class="actions">
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search customers...">
        <i class="fa fa-search"></i>
    </div>
</div>

  <!-- STATS -->
  <div class="stats">
    <div class="stat">
      <h3>Total Customers</h3>
      <h2><?= $stats['total'] ?></h2>
    </div>
  </div>

  <!-- TABLE -->
  <div class="table-wrapper">
    <table role="grid" aria-label="Customer list">
      <thead>
        <tr>
          <th scope="col">ID</th>
          <th scope="col">Full Name</th>
          <th scope="col">Phone</th>
          <th scope="col">Email</th>
          <th scope="col">Registered At</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['fullname']) ?></td>
            <td><?= htmlspecialchars($row['phone'] ?? '—') ?></td>
            <td><?= htmlspecialchars($row['email'] ?? 'Walk-in') ?></td>
            <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($row['created_at']))) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="muted" style="text-align:center;">No customers found</td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// SEARCH TABLE
document.getElementById("searchInput").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll("table tbody tr").forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>
