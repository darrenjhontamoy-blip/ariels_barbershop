<?php
session_start();
include '../config.php';

/* =======================
   ADMIN ONLY CHECK
======================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = false;
$error   = "";

/* =======================
   SAVE WALK-IN APPOINTMENT
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customer_name = trim($_POST['customer_name'] ?? '');
    $service       = trim($_POST['service'] ?? '');
    $barber_name   = trim($_POST['barber_name'] ?? '');
    $date          = $_POST['appointment_date'] ?? '';
    $time          = $_POST['appointment_time'] ?? '';

    if (!$customer_name || !$service || !$barber_name || !$date || !$time) {
        $error = "❌ Please fill in all required fields.";
    } else {
        $conn->begin_transaction();
        try {
            // INSERT sa appointments
            $stmt = $conn->prepare("
                INSERT INTO appointments
                (customer_name, service, barber_name, appointment_date, appointment_time, status, appointment_source)
                VALUES (?, ?, ?, ?, ?, 'Pending', 'Walkin')
            ");
            $stmt->bind_param("sssss", $customer_name, $service, $barber_name, $date, $time);
            $stmt->execute();

            // Kuhanin ang bagong appointment ID
            $appointment_id = $conn->insert_id;
            $stmt->close();

            // INSERT sa walkin_queue, naka-link sa appointment_id
            $stmt2 = $conn->prepare("
                INSERT INTO walkin_queue
                (customer_name, service, barber_name, appointment_date, appointment_time, status, appointment_id)
                VALUES (?, ?, ?, ?, ?, 'Pending', ?)
            ");
            $stmt2->bind_param("sssssi", $customer_name, $service, $barber_name, $date, $time, $appointment_id);
            $stmt2->execute();
            $stmt2->close();

            $conn->commit();
            $success = true;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "❌ Error saving walk-in: " . $e->getMessage();
        }
    }
}

/* =======================
   FETCH WALK-IN LIST
======================= */
$res = mysqli_query($conn, "
    SELECT w.id, w.customer_name, w.service, w.barber_name,
           w.appointment_date, w.appointment_time, a.status
    FROM walkin_queue w
    LEFT JOIN appointments a ON w.appointment_id = a.id
    ORDER BY w.appointment_date ASC, w.appointment_time ASC
");
$totalCount = $res ? mysqli_num_rows($res) : 0;

/* =======================
   FETCH SERVICES & BARBERS
======================= */
$services = mysqli_query($conn,"SELECT service_name FROM services ORDER BY service_name ASC");
$barbers  = mysqli_query($conn,"SELECT fullname FROM users WHERE role='barber' AND status='Active'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Walk-in Appointments</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;margin:0;padding:0;}
body{background:#f4f6f9;color:#333;}

/* Adjust top-header to respect sidebar width */
.top-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #e3e6ff;
    color: #3f51b5;
    padding: 20px 30px;
    border-radius: 14px;

    /* Increase left margin to give space from sidebar */
    margin: 20px 30px 30px 300px; /* was calc(240px + 30px) */
}

/* Adjust main content */
.main {
    margin-left: 300px; /* was 240px */
    padding: 30px 40px 30px 30px;
}
.main{margin-left:240px;padding:0 30px 40px;}
.logo img{height:40px}

.card {
    max-width:500px;
    margin:0 auto;
    padding:32px;
    border-radius:20px;
    position:relative;
    overflow:hidden;

    /* Gradient border effect */
    border: 3px solid transparent; /* importante */
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(white, white), /* loob ng card */
        linear-gradient(135deg, #3b82f6, #a855f7, #ef4444); /* gradient border */

    box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.card::before{
    content:"";position:absolute;top:0;left:0;right:0;height:6px;
    background:linear-gradient(90deg,#4f46e5,#6366f1);
    border-radius:20px 20px 0 0;
}
.card h2{text-align:center;color:#4f46e5;margin-bottom:20px;}

input,select,button{
    width:100%;padding:12px 14px;border-radius:12px;
    border:1px solid #d1d5db;margin-bottom:16px;font-size:15px;
}
input:focus,select:focus{
    outline:none;border-color:#6366f1;
    box-shadow:0 0 0 3px rgba(99,102,241,.15);
}
button{
    background:linear-gradient(135deg,#4f46e5,#6366f1);
    color:#fff;border:none;height:48px;
    font-weight:700;cursor:pointer;
}

.service-grid{
    display:grid;grid-template-columns:repeat(3,1fr);
    gap:12px;margin-bottom:20px;
}
.service-card{
    padding:14px;border-radius:14px;
    border:1.5px solid #e5e7eb;
    text-align:center;font-weight:600;
    background:#f9fafb;cursor:pointer;
}
.service-card.active{
    background:#4f46e5;color:#fff;border-color:#4f46e5;
}

/* TIME SLOTS */
.time-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:10px;margin-bottom:20px;
}
.time-slot{
    padding:10px;border-radius:12px;
    text-align:center;font-weight:600;
    border:1.5px solid #e5e7eb;
    background:#f9fafb;cursor:pointer;
}
.time-slot.active{
    background:#4f46e5;color:#fff;
}
.time-slot.disabled{
    background:#fee2e2;color:#991b1b;
    border-color:#ef4444;cursor:not-allowed;
}

/* POPUP MESSAGE */
.popup {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #22c55e;
    color: #fff;
    padding: 14px 18px;
    border-radius: 12px;
    box-shadow: 0 8px 18px rgba(0,0,0,.15);
    display: none;
    z-index: 9999;
}
.popup.error { background: #dc2626; }

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

<div class="top-header">
    <h1>WALK-IN APPOINTMENTS</h1>
    <div class="logo">
        <img src="arslogo.jpg" alt="Logo">
    </div>
</div>

<div class="main">
<div class="card">
<h2>Add Walk-In Customer</h2>

<form method="POST">
<input type="text" name="customer_name" placeholder="Customer Name" required>

<input type="hidden" name="service" id="serviceInput" required>

<div class="service-grid">
<?php while($s=mysqli_fetch_assoc($services)): ?>
<div class="service-card" onclick="selectService(this,'<?= $s['service_name']?>')">
    <?= ucwords($s['service_name']) ?>
</div>
<?php endwhile; ?>
</div>

<select name="barber_name" id="barber" required>
<option disabled selected>Select Barber</option>
<?php while($b=mysqli_fetch_assoc($barbers)): ?>
<option><?= $b['fullname']?></option>
<?php endwhile; ?>
</select>

<input type="date" name="appointment_date" id="appointment_date" required>

<div class="time-grid" id="timeGrid"></div>
<input type="hidden" name="appointment_time" id="appointment_time" required>

<button type="submit">ADD TO WALK-IN</button>
</form>
</div>
</div>

<!-- POPUP MESSAGE -->
<div id="popup" class="popup"></div>

<script>
const today = new Date().toISOString().split('T')[0];
document.getElementById('appointment_date').min = today;

const dateInput = document.getElementById('appointment_date');
const barber = document.getElementById('barber');
const timeGrid = document.getElementById('timeGrid');
const timeInput = document.getElementById('appointment_time');

const slots = ["09:00","09:30","10:00","10:30","11:00","11:30","12:00","13:00","13:30","14:00","14:30","15:00","15:30","16:00","16:30","17:00","17:30","18:00","18:30","19:00"];

dateInput.value = today;  // AUTO SET TODAY

function selectService(el,service){
    document.querySelectorAll('.service-card').forEach(c=>c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('serviceInput').value = service;
}

function renderTimes(booked=[]){
    timeGrid.innerHTML='';

    const now = new Date();
    const nowMinutes = now.getHours() * 60 + now.getMinutes();
    const selectedDate = dateInput.value;
    const isToday = selectedDate === today;

    slots.forEach(t=>{
        const d=document.createElement('div');
        d.className='time-slot';
        d.innerText=t;

        const [hh, mm] = t.split(':');
        const slotMinutes = parseInt(hh) * 60 + parseInt(mm);

        if(isToday && slotMinutes <= nowMinutes){
            d.classList.add('disabled');
        }

        if(booked.includes(t)){
            d.classList.add('disabled');
        }

        if(!d.classList.contains('disabled')){
            d.onclick=()=>{
                document.querySelectorAll('.time-slot').forEach(x=>x.classList.remove('active'));
                d.classList.add('active');
                timeInput.value=t;
            }
        }

        timeGrid.appendChild(d);
    });
}

async function loadBooked(){
    if(!dateInput.value || !barber.value) return;
    const res = await fetch('check_schedule.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({date:dateInput.value, barber:barber.value})
    });
    const data = await res.json();
    renderTimes(data);
}

dateInput.addEventListener('change',loadBooked);
barber.addEventListener('change',loadBooked);
loadBooked();

/* POPUP */
const popup = document.getElementById('popup');

function showPopup(message, isError = false) {
    popup.innerText = message;
    popup.className = isError ? 'popup error' : 'popup';
    popup.style.display = 'block';

    setTimeout(() => {
        popup.style.display = 'none';
    }, 2500);
}

/* Show message from PHP */
<?php if($success): ?>
showPopup('✅ Walk-in added successfully');
<?php endif; ?>

<?php if($error): ?>
showPopup('<?= addslashes($error) ?>', true);
<?php endif; ?>
</script>

</body>
</html>
