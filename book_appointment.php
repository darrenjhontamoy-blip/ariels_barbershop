<?php
session_start();
include '../config.php';

/* CUSTOMER ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

$customerName = $_SESSION['fullname'] ?? 'Customer';
$email = $_SESSION['email_address'] ?? '';

/* SERVICE INFO */
$services = [
    "Classic Haircut" => ["duration" => 30, "price" => 90],
    "Modern Haircut" => ["duration" => 30, "price" => 90],
    "Haircut + Beard" => ["duration" => 35, "price" => 100],
];

if (isset($_POST['confirm'])) {

    $service  = $_POST['service'];
    $price    = $services[$service]['price'];
    $duration = $services[$service]['duration'];

    // 1️⃣ Prepare INSERT statement with correct column names
    $stmt = $conn->prepare("
    INSERT INTO appointments
    (customer_name, barber_name, service, hairstyle, appointment_date, appointment_time, status, payment_status, email_address, price, duration, appointment_source)
    VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'Pending', ?, ?, ?, 'Online')
");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // 2️⃣ Bind parameters
$stmt->bind_param(
    "sssssssdd",
    $customerName,              // customer_name
    $_POST['barber_name'],      // barber_name
    $service,                   // service
    $_POST['hairstyle'],        // hairstyle ✅
    $_POST['appointment_date'], // appointment_date
    $_POST['appointment_time'], // appointment_time
    $email,                     // email_address
    $price,                     // price
    $duration                   // duration
);


    if ($stmt->execute()) {

    $appointment_id = $stmt->insert_id;
    $appointmentBooked = true;

    // ================= Notification =================
    $barberNameSelected = $_POST['barber_name'];
    $message = "New appointment booked by $customerName for $service on ".$_POST['appointment_date']." at ".$_POST['appointment_time'].".";

    $res = mysqli_query($conn, "SELECT id FROM users WHERE fullname='".mysqli_real_escape_string($conn, $barberNameSelected)."' AND role='barber' LIMIT 1");

    if($res && mysqli_num_rows($res) > 0){
        $barber = mysqli_fetch_assoc($res);
        $barber_id = (int)$barber['id'];

        $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')");
        $stmtNotif->bind_param("is", $barber_id, $message);
        $stmtNotif->execute();
        $stmtNotif->close();
    }

} else {
    die("Execute failed: " . $stmt->error);
}


    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Appointment | Ariel's Barbershop</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* ========================= BARBER COLOR THEME ========================= */
:root{
    --blue:#0b3d91;
    --red:#c1121f;
    --white:#ffffff;
    --light-gray:#d1d5db;
    --glass-bg: rgba(255,255,255,0.1);
    --glass-blur: blur(15px);
    --hover-shadow: 0 15px 40px rgba(0,0,0,0.3);
}

/* ========================= BASIC RESET ========================= */
*{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins','Segoe UI',Arial,sans-serif;}
body{
    background: linear-gradient(135deg, #0b3d91 0%, #1e3a8a 40%, #7f1d1d 70%, #c1121f 100%);
    min-height: 100vh;
    color: #ffffff;
}

a{text-decoration:none;color:inherit;}

/* ========================= OVERLAY ========================= */
body::before{
    content:'';
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.45);
    z-index:0;
}


/* ========================= LAYOUT ========================= */
.main{
    position:relative;
    z-index:1;
    margin-left:240px;
    padding:40px;
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:30px;
}

/* ========================= CARD GLASS ========================= */
.card, .summary{
    position: relative;
    background: linear-gradient(135deg, 
        rgba(255,255,255,0.08),
        rgba(255,255,255,0.03)
    );
    backdrop-filter: blur(18px);
    border-radius: 22px;
    padding: 40px;
    width:100%;
    max-width:450px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
    transition: all .3s ease;
}

/* Gradient Border Effect */
.card::before,
.summary::before{
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 22px;
    padding: 2px;
    background: linear-gradient(135deg, #3b82f6, #ef4444);
    
    -webkit-mask:
        linear-gradient(#000 0 0) content-box,
        linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;

    pointer-events: none; /* ⭐ IMPORTANT FIX */
}



    backdrop-filter: blur(20px);

    backdrop-filter: var(--glass-blur);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    padding: 40px;
    width:100%;
    max-width:450px;
    transition:.3s;

.card:hover, .summary:hover{
    transform: translateY(-5px);
    box-shadow:
        0 25px 60px rgba(0,0,0,0.45),
        0 0 30px rgba(139,92,246,0.35);
}

/* ========================= HEADER ========================= */
.brand-header{text-align:center;margin-bottom:35px;}
.brand-title{
    font-size:44px;
    font-weight:900;
    letter-spacing:4px;
    text-transform:uppercase;
    color: #ffffff;

    text-shadow:
        0 3px 10px rgba(0,0,0,0.6),   /* depth */
        0 0 15px rgba(255,255,255,0.4); /* soft glow */
}

letter-spacing:2px;text-transform:uppercase;
.brand-title{
    font-size:38px;
    font-weight:900;
    padding:10px 24px;
    border-radius:14px;
    background: linear-gradient(90deg,#2563eb,#dc2626);
    color:#ffffff;
    display:inline-block;
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    letter-spacing:2px;
}


/* ========================= STEPS ========================= */
.steps{display:flex;justify-content:space-between;margin-bottom:30px;gap:10px;}
.step{
    flex:1;
    text-align:center;
    padding:12px;
    font-size:13px;
    font-weight:600;
    color: var(--light-gray);
    border-radius:12px;
    cursor:pointer;
    background: var(--glass-bg);
    transition:.3s;
}
.step.active{color:var(--white);background: linear-gradient(90deg,var(--blue),var(--red));}

/* ========================= GRID ITEMS ========================= */
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:16px;justify-items:center;}
.item{
    border-radius:16px;
    padding:20px;
    text-align:center;
    background: rgba(255,255,255,0.15);
    cursor:pointer;
    transition:0.3s ease;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
}
.item:hover{transform:translateY(-5px); box-shadow:0 12px 25px rgba(0,0,0,0.15);}
.item.active{border:2px solid var(--blue); background: rgba(79,70,229,0.2);}

/* ========================= DATE & TIME ========================= */
input[type="date"]{
    padding:12px;
    width:100%;
    border-radius:12px;
    border:1px solid var(--light-gray);
    font-size:14px;
    margin-top:10px;
}
.time{
    width:80px;
    padding:10px;
    border-radius:12px;
    border:2px solid var(--light-gray);
    cursor:pointer;
    text-align:center;
    background: var(--white);
    color:#333;
    transition:.3s;
}
.time:hover{background: var(--blue); color: var(--white); border-color: var(--blue);}
.time.active{background: var(--blue); color: var(--white); border-color: var(--blue);}
.time.disabled{background:#ef4444;color:var(--white);cursor:not-allowed;border-color:#ef4444;}

/* ========================= BUTTONS ========================= */
button{
    padding:14px 24px;
    border:none;
    border-radius:12px;
    font-weight:700;
    font-size:14px;
    cursor:pointer;
    transition:.3s;
}
button.confirm{background: linear-gradient(90deg,var(--blue),var(--red)); color:var(--white);}
button.confirm:hover{opacity:0.9;}
button.cancel{background:#ef4444;color:var(--white);}
button.cancel:hover{opacity:0.9;}

/* ========================= SUMMARY ========================= */
.summary h2{
    font-size:24px;
    text-align:center;
    padding-bottom:8px;
    margin-bottom:25px;
    font-weight:700;
    color:var(--white);
    border:none; /* remove old border */
}

.summary h2::after{
    content:"";
    display:block;
    width:120px;
    height:2px;
    background:rgba(255,255,255,0.4);
    margin:10px auto 0; 
}
.summary-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.1);font-size:15px;}
.summary-row:last-child{border-bottom:none;}
.summary-label{font-weight:600;color:var(--white);}
.summary-value{text-align:right;max-width:180px;overflow-wrap:break-word;}
.summary-image{max-width:100px;max-height:100px;margin-left:10px;border-radius:12px;object-fit:cover;vertical-align:middle;display:inline-block;}
.total-row{margin-top:20px;font-weight:700;font-size:16px;display:flex;justify-content:space-between;color:var(--white);}

/* ========================= RESPONSIVE ========================= */
@media(max-width:1000px){
    .main{margin-left:0;flex-direction:column;align-items:center;}
    .card, .summary{max-width:100%;}
}
.hidden{display:none;}

/* ========================= STEP TRANSITION ========================= */
#step1,#step2,#step3,#step4{
    transition:opacity .3s,transform .3s;
}

/* ===== VIDEO BACKGROUND ===== */
#bgVideo{
    position: fixed;
    top: 0;
    left: 0;
    min-width: 100%;
    min-height: 100%;
    object-fit: cover;
    z-index: -3; /* nasa pinakalikod */
    opacity: 0.35; /* subtle lang */
}
.section-title{
    text-align:center;
    color:#ffffff;
    font-size:20px;
    font-weight:700;
    margin-bottom:20px;
    position:relative;
    letter-spacing:1px;
}

.arrow-down{
    display:block;
    font-size:18px;
    margin-top:5px;
    animation: bounce 1.5s infinite;
    color:#60a5fa;
}

@keyframes bounce{
    0%,100%{ transform: translateY(0); }
    50%{ transform: translateY(6px); }
}
</style>

</head>
<body>

<?php include 'sidebar_customer.php'; ?>

<video autoplay muted loop playsinline id="bgVideo">
    <source src="ariel..mp4" type="video/mp4">
</video>

<div class="main">
<!-- BOOKING CARD -->
<div class="card">
<div class="brand-header">
    <h1 class="brand-title">𝓐𝓻𝓲𝓮𝓵'𝓼 𝓑𝓪𝓻𝓫𝓮𝓼𝓱𝓸𝓹</h1>
    <p class="brand-subtitle">Please select your booking appointment</p>
</div>

<div class="steps">
  <div class="step active" id="s1">Service</div>
  <div class="step" id="s2">Hairstyle</div>
  <div class="step" id="s3">Barber</div>
  <div class="step" id="s4">Date & Time</div>
</div>

<form method="POST" id="bookingForm">

<!-- STEP 1 -->
<div id="step1">
<h3 class="section-title">
    Select Service
    <span class="arrow-down">▼</span>
</h3>
<div class="grid">
  <div class="item" onclick="selectService('Classic Haircut',this)">Classic Haircut</div>
  <div class="item" onclick="selectService('Modern Haircut',this)">Modern Haircut</div>
  <div class="item" onclick="selectService('Haircut + Beard',this)">Haircut + Beard</div>
</div>
</div>

<!-- STEP 2 -->
<div id="step2" class="hidden">
<h3 class="section-title">
    Select Hairstyle
    <span class="arrow-down">▼</span>
</h3>
<div class="grid" id="styleGrid"></div>
</div>

<!-- STEP 3 -->
<div id="step3" class="hidden">
<h3 class="section-title">
    Select Barber
    <span class="arrow-down">▼</span>
</h3>
<div class="grid" id="barberGrid"></div>
</div>

<!-- STEP 4 -->
<div id="step4" class="hidden">
<h3 class="section-title">
    Select Date
    <span class="arrow-down">▼</span>
</h3>
<input type="date" name="appointment_date" id="dateInput" required min="<?= date('Y-m-d') ?>">
<h3 class="section-title" style="margin-top:22px;">
    Select Time
    <span class="arrow-down">▼</span>
</h3>
<div class="grid" id="timeGrid"></div>

</div>

<input type="hidden" name="service" id="serviceInput" required>
<input type="hidden" name="hairstyle" id="hairstyleInput" required>
<input type="hidden" name="barber_name" id="barberInput" required>
<input type="hidden" name="appointment_time" id="timeInput" required>

<!-- Buttons -->
<div class="nav-buttons" style="margin-top:20px;display:flex;justify-content:space-between;gap:10px;">
    <button type="button" id="prevBtn" class="cancel" onclick="prevStep()">Previous</button>
    <button type="submit" name="confirm" id="confirmBtn" class="confirm hidden">Confirm Appointment</button>
    <button type="button" id="cancelBtn" class="cancel" onclick="location.href='book_appointment.php'">Cancel</button>
</div>
</form>
</div>

<!-- SUMMARY CARD -->
<div class="summary" id="summaryContainer">
<h2>APPOINTMENT SUMMARY</h2>
<div class="summary-row"><div class="summary-label">Service:</div><div class="summary-value" id="sumService">-</div></div>
<div class="summary-row"><div class="summary-label">Duration:</div><div class="summary-value" id="sumDuration">-</div></div>
<div class="summary-row"><div class="summary-label">Price:</div><div class="summary-value" id="sumPrice">-</div></div>
<div class="summary-row" style="align-items:center;"><div class="summary-label">Hairstyle:</div><div class="summary-value" id="sumStyleName" style="display:flex;align-items:center;gap:8px;">- <img id="sumStyleImage" class="summary-image" src="" alt="Hairstyle preview" style="display:none;"></div></div>
<div class="summary-row"><div class="summary-label">Barber:</div><div class="summary-value" id="sumBarber">-</div></div>
<div class="summary-row"><div class="summary-label">Date:</div><div class="summary-value" id="sumDate">-</div></div>
<div class="summary-row"><div class="summary-label">Time:</div><div class="summary-value" id="sumTime">-</div></div>
<hr style="margin:20px 0;">
<div class="total-row"><div>Total Duration:</div><div id="sumTotalDuration">-</div></div>
<div class="total-row"><div>Total Price:</div><div id="sumTotalPrice">-</div></div>
</div>
<!-- FULL-WIDTH HORIZONTAL CONTACT PARAGRAPH -->
<div style="
    width:100%;
    text-align:center;
    margin:50px 0;
    color:#ffffff;
    line-height:1.8;
">
    <p style="font-size:18px; font-weight:600;">
        Visit us or reach out to book your next appointment:
    </p>

    <p>
        <strong>Address:</strong> SBR Center Builders Inc, Unit 5 Lot 2115-5-3-G, 
        Maribel Subd St, Maribel Sub, Canlalay, Biñan, Laguna
    </p>

    <p>
        <strong>Contact Number:</strong> TM-09658932994 | SMART-09494159956
    </p>

    <p>
        <strong>Email:</strong> Trijoariel@yahoo.com
    </p>
</div>
</div>

<?php if (!empty($appointmentBooked)): ?>
<div id="popup" style="
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    animation: fadeInPopup 0.3s ease forwards;
">
    <div style="
        background: linear-gradient(135deg, #ffffff, #e6f0ff);
        padding: 40px 35px;
        border-radius: 20px;
        max-width: 450px;
        width: 90%;
        box-shadow:
            0 8px 20px rgba(0, 0, 0, 0.12),
            0 12px 40px rgba(0, 0, 0, 0.10);
        text-align: center;
        color: #222;
        position: relative;
    ">
        <div style="
            font-size: 56px;
            color: #22c55e;
            margin-bottom: 20px;
            filter: drop-shadow(0 2px 4px rgba(34,197,94,0.7));
        ">✅</div>
        <h2 style="
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 18px;
            letter-spacing: 0.06em;
            color: #1e293b;
        ">Appointment Booked!</h2>
        <p style="
            font-size: 16.2px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 28px;
            font-weight: 500;
        ">
            Thank you <strong><?= htmlspecialchars($customerName) ?></strong>,<br>
            your appointment for <strong><?= htmlspecialchars($_POST['service']) ?></strong><br>
            with <strong><?= htmlspecialchars($_POST['barber_name']) ?></strong><br>
            on <strong><?= htmlspecialchars($_POST['appointment_date']) ?></strong> at
            <strong><?= htmlspecialchars($_POST['appointment_time']) ?></strong> has been confirmed.
        </p>
        <p style="
            font-size: 15px;
            color: #ef4444;
            font-weight: 700;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        ">
            <span style="font-size: 22px;">⏰</span>
            Reminder: If you are late, your slot may be given to another customer.
        </p>
        <button onclick="document.getElementById('popup').style.display='none'" style="
            background: linear-gradient(90deg, #4f46e5, #22c55e);
            padding: 14px 38px;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 6px 15px rgba(34, 197, 94, 0.5);
            transition: background-color 0.3s ease, transform 0.2s ease;
        " onmouseover="this.style.background='linear-gradient(90deg, #3b3bbf, #16a34a)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='linear-gradient(90deg, #4f46e5, #22c55e)'; this.style.transform='scale(1)';">
            OK
        </button>
    </div>
</div>

<style>
@keyframes fadeInPopup {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>



<?php endif; ?>

<script>
// ========================= JS LOGIC =========================
const services = <?= json_encode($services) ?>;
let currentStep = 1;

const prevBtn = document.getElementById('prevBtn');
const confirmBtn = document.getElementById('confirmBtn');
const cancelBtn = document.getElementById('cancelBtn');

const serviceInput = document.getElementById('serviceInput');
const barberInput = document.getElementById('barberInput');
const timeInput = document.getElementById('timeInput');
const dateInput = document.getElementById('dateInput');

const sumService = document.getElementById('sumService');
const sumDuration = document.getElementById('sumDuration');
const sumPrice = document.getElementById('sumPrice');
const sumStyleName = document.getElementById('sumStyleName');
const sumStyleImage = document.getElementById('sumStyleImage');
const sumBarber = document.getElementById('sumBarber');
const sumDate = document.getElementById('sumDate');
const sumTime = document.getElementById('sumTime');
const sumTotalDuration = document.getElementById('sumTotalDuration');
const sumTotalPrice = document.getElementById('sumTotalPrice');

prevBtn.style.display='none';
confirmBtn.classList.add('hidden');
cancelBtn.style.display='none';

let selectedStyle = null;
let selectedStyleName = '';
const hairstyleInput = document.getElementById('hairstyleInput');


const allTimes = ['09:00','09:30','10:00','10:30','11:00','11:30','12:00','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00','20:30','21:00'];

const barbers = [
  { id: 1, name: 'Ariel Trijo', image: 'uploads/barbers/ars..jpg' },
  { id: 2, name: 'Kim Zaragoza', image: 'uploads/barbers/KIM.jpg' }
];


function goToStep(n){
    currentStep = n;
    for(let i=1;i<=4;i++){
        document.getElementById('step'+i).classList.add('hidden');
        document.getElementById('s'+i).classList.remove('active');
    }
    document.getElementById('step'+n).classList.remove('hidden');
    document.getElementById('s'+n).classList.add('active');

    prevBtn.style.display = n === 1 ? 'none' : 'inline-block';
    confirmBtn.classList.toggle('hidden', n !== 4);
    cancelBtn.style.display = n === 4 ? 'inline-block' : 'none';

    if(n===3) loadBarbers();
    if(n===4) loadTimeSlots();
}

function selectService(value,el){
    serviceInput.value=value;
    document.querySelectorAll('#step1 .item').forEach(x=>x.classList.remove('active'));
    el.classList.add('active');
    loadStyles(value);
    goToStep(2);
}

function loadStyles(service){
    const styleGrid = document.getElementById('styleGrid');
    styleGrid.innerHTML = '';

    let styles = [];

    if(service === 'Classic Haircut'){
        styles = [
            { name:'Barber Cut', image:'barber.jpg' },
            { name:'Semi Barber', image:'semi_barber.jpg' },
            { name:'Semi Kalbo', image:'semi_kalbo.jpg' },
            { name:'Flat Top', image:'semi_flat_top.jpg' },
            { name:'Clean Cut', image:'clean_cut.jpg' },
            { name:'Army Cut', image:'army_cut.jpg' },
        ];
    }
    else if(service === 'Modern Haircut'){
        styles = [
            { name:'Burst Fade', image:'burst_fade.jpg' },
            { name:'Buzz Cut', image:'buzz_cut.jpg' },
            { name:'Low Fade V', image:'low_fade_v.jpg' },
            { name:'Mid Fade', image:'mid_fade.jpg' },
            { name:'Mullet Fade', image:'mide_mullet.jpg' },
            { name:'Taper Fade', image:'taper_fade.jpg' },
        ];
    }
    else if(service === 'Haircut + Beard'){
        styles = [
            { name:'Full Beard', image:'aws.jpg' },
            { name:'Extended Beard', image:'ews.jpg' },
            { name:'Light Beard', image:'iws.jpg' },
            { name:'Upper Beard', image:'uws.jpg' },
            { name:'Oval Beard', image:'ows.jpg' },
            { name:'Pointed Beard', image:'ops.jpg' },
        ];
    }

    styles.forEach(style => {
        const wrapper = document.createElement('div');
        wrapper.className = 'item';
        wrapper.innerHTML = `
            <img src="uploads/styles/${style.image}" style="width:100px;height:100px;border-radius:14px;margin-bottom:8px;">
            <div>${style.name}</div>
        `;
        wrapper.onclick = () => selectStyle(wrapper, style);
        styleGrid.appendChild(wrapper);
    });
}

function selectStyle(el, style){
    selectedStyle = el;
    selectedStyleName = style.name;
    hairstyleInput.value = style.name; // ✅ SAVE TO HIDDEN INPUT

    document.querySelectorAll('#step2 .item').forEach(x => x.classList.remove('active'));
    el.classList.add('active');
    goToStep(3);
}

function loadBarbers(){
    const barberGrid = document.getElementById('barberGrid');
    barberGrid.innerHTML = '';
    barbers.forEach(barber => {
        const div = document.createElement('div');
        div.className = 'item';
        div.innerHTML = `
            <img src="${barber.image}" alt="${barber.name}" style="width:100px;height:100px;border-radius:14px;object-fit:cover;margin-bottom:8px;">
            <div>${barber.name}</div>
        `;
        div.onclick = () => selectBarber(barber.name, div);
        barberGrid.appendChild(div);
    });
}

function selectBarber(value,el){
    barberInput.value=value;
    document.querySelectorAll('#step3 .item').forEach(x=>x.classList.remove('active'));
    el.classList.add('active');
    goToStep(4);
}

async function loadTimeSlots() {
    const timeGrid = document.getElementById('timeGrid');
    timeGrid.innerHTML = '';

    const barber = barberInput.value;
    const date = dateInput.value;

    if (!barber || !date) return;

    let booked = [];

    try {
        const res = await fetch(`check_booked.php?barber=${encodeURIComponent(barber)}&date=${date}`);
        booked = await res.json();
    } catch(e){ console.log(e); }

    // Get current time if the selected date is today
    const today = new Date();
    const selectedDate = new Date(date);
    let currentHour = 0;
    let currentMinute = 0;

    if (
        today.getFullYear() === selectedDate.getFullYear() &&
        today.getMonth() === selectedDate.getMonth() &&
        today.getDate() === selectedDate.getDate()
    ) {
        currentHour = today.getHours();
        currentMinute = today.getMinutes();
    }

    allTimes.forEach(time => {
        const btn = document.createElement('div');
        btn.className = 'time';
        btn.textContent = time;

        const [hourStr, minuteStr] = time.split(':');
        const hour = parseInt(hourStr);
        const minute = parseInt(minuteStr);

        // Disable if already booked
        if (booked.includes(time)) btn.classList.add('disabled');

        // Disable if the time is in the past for today
        if (today.getFullYear() === selectedDate.getFullYear() &&
            today.getMonth() === selectedDate.getMonth() &&
            today.getDate() === selectedDate.getDate() &&
            (hour < currentHour || (hour === currentHour && minute <= currentMinute))
        ) {
            btn.classList.add('disabled');
        }

        btn.onclick = () => {
            if (btn.classList.contains('disabled')) return;
            timeInput.value = time;
            document.querySelectorAll('.time').forEach(x => x.classList.remove('active'));
            btn.classList.add('active');
            updateSummary();
        }

        timeGrid.appendChild(btn);
    });
}

function prevStep(){ goToStep(currentStep-1); }

function updateSummary(){
    sumService.textContent = serviceInput.value;
    sumDuration.textContent = services[serviceInput.value]?.duration + ' mins';
    sumPrice.textContent = '₱'+services[serviceInput.value]?.price;

    sumStyleName.textContent = selectedStyleName;
    if(selectedStyle){
        const img = selectedStyle.querySelector('img');
        if(img){
            sumStyleImage.src = img.src;
            sumStyleImage.style.display = 'inline-block';
        }
    }

    sumBarber.textContent = barberInput.value;
    sumDate.textContent = dateInput.value;
    sumTime.textContent = timeInput.value;

    sumTotalDuration.textContent = services[serviceInput.value]?.duration + ' mins';
    sumTotalPrice.textContent = '₱'+services[serviceInput.value]?.price;
}

dateInput.addEventListener('change', loadTimeSlots);
</script>
</body>
</html>
