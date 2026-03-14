<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];

    $role = isset($_POST['is_barber']) ? 'barber' : 'customer';
    $status = ($role === 'barber') ? 'Inactive' : 'Active';

    // ================= BASIC VALIDATION =================
    if(empty($fullname) || empty($username) || empty($email) || empty($phone) || empty($password)){
        $error = "Please fill in all fields.";
    }

    // ================= STRONG PASSWORD VALIDATION =================
    if(empty($error)) {
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
            $error = "Password must be at least 8 characters long and include:
            - One uppercase letter
            - One lowercase letter
            - One number
            - One special character.";
        }
    }

    if(empty($error)) {

        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email_address = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $res = $check->get_result();

        if($res->num_rows > 0){
            $error = "Username or email already exists.";
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users (fullname, username, email_address, password, role, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("ssssss", $fullname, $username, $email, $hashed, $role, $status);

            if($stmt->execute()) {

    $user_id = $conn->insert_id;

    // Kunin ang created_at ng bagong user
$stmtCreatedAt = $conn->prepare("SELECT created_at FROM users WHERE id = ?");
$stmtCreatedAt->bind_param("i", $user_id);
$stmtCreatedAt->execute();
$resCreatedAt = $stmtCreatedAt->get_result();
$createdAtRow = $resCreatedAt->fetch_assoc();
$registrationTime = $createdAtRow ? $createdAtRow['created_at'] : 'Unknown time';
$stmtCreatedAt->close();

    // ================= CUSTOMER INSERT =================
    if($role === 'customer'){
        $stmt2 = $conn->prepare("
            INSERT INTO customers (user_id, fullname, email, phone, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt2->bind_param("isss", $user_id, $fullname, $email, $phone);
        $stmt2->execute();
        $stmt2->close();

        // 🔔 NOTIFY ADMIN for new customer
        $admin_id = 1;
        $registrationTime = date('Y-m-d H:i:s'); // or pwede manggaling sa database created_at

$msg = "New Customer Registration\n"
     . "Name: $fullname\n"
     . "Email: $email\n"
     . "Status: $status\n"
     . "Registered at: $registrationTime";

$stmtN = $conn->prepare("
    INSERT INTO notifications (user_id, message, status, created_at)
    VALUES (?, ?, 'unread', NOW())
");
$stmtN->bind_param("is", $admin_id, $msg);
$stmtN->execute();
$stmtN->close();
    }

    // ================= BARBER INSERT =================
    if($role === 'barber'){
        $barberCode = "BRB-" . strtoupper(substr(md5(time()), 0, 6));

        $stmt3 = $conn->prepare("
            INSERT INTO barbers (user_id, barber_code, fullname, email, phone, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'Pending', NOW())
        ");
        $stmt3->bind_param("issss", $user_id, $barberCode, $fullname, $email, $phone);
        $stmt3->execute();
        $stmt3->close();

        $msg = "New Barber Registration\nName: $fullname\nCode: $barberCode";

        // Notify Admin
        $admin_id = 1;

        $stmtN = $conn->prepare("
            INSERT INTO notifications (user_id, message, status, created_at)
            VALUES (?, ?, 'unread', NOW())
        ");
        $stmtN->bind_param("is", $admin_id, $msg);
        $stmtN->execute();
        $stmtN->close();
    }

    $success = ($role === 'barber') 
        ? "Barber registration submitted. Please wait for admin approval."
        : "Account created successfully!";

} else {
    $error = "Registration failed. Please try again.";
}

            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | Ariel’s Barbershop</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    min-height:100vh;
    width:100%;
    font-family:'Oswald', sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#000;
    overflow:hidden;
}
.password-box{
    position: relative;
    width: 100%;
}

.password-box input{
    width: 100%;
    padding: 13px;
    padding-right: 45px; /* space for eye icon */
    background: rgba(30,30,30,0.8);
    border:1px solid #333;
    border-radius:7px;
    color:#fff;
}

.toggle-password{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    font-size:16px;
    color:#ccc;
}

.toggle-password:hover{
    color:#fff;
}

/* BACKGROUND VIDEO */
.bg-video {
    position: fixed;
    top:0; left:0;
    width:100%;
    height:100%;
    object-fit:cover;
    z-index:-2;
}
.video-overlay{
    position: fixed;
    top:0; left:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.6);
    z-index:-1;
}

/* LOGO TOP RIGHT - bilog, transparent */
.logo-circle {
    position: fixed;
    top: 20px;
    right: 30px;
    z-index: 10;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: transparent;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}
.logo-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

/* REGISTRATION CARD */
.register-card{
    width:450px;
    max-width:95%;
    display:flex;
    flex-direction:column;
    align-items:center;

    /* GLASS EFFECT */
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);

    border-radius:25px;
    padding:45px 35px;
    position:relative;
    color:#fff;

    border:2px solid transparent;
    box-shadow:0 0 40px rgba(0,0,0,0.4);
}
.register-card::before {
    content:"";
    position:absolute;
    inset:0;
    border-radius:25px;
    padding:2px;
    background: linear-gradient(135deg,#4facfe,#8e44ad,#ff4d4d);
    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events:none;
}

.register-card .brand h1{
    text-align:center;
    font-family:'Playfair Display', serif;
    font-size:32px;
    font-weight:800;
    margin-bottom:10px;
}
.brand-accent{color:#c62828;}
.decor{
    width:70px;height:3px;
    background:linear-gradient(to right,#c62828,#1e88e5);
    margin:18px auto;
}
.subtitle {
    font-size:16px;
    color:#fff;
    text-align:center;
    font-weight:600;
    margin-bottom:25px;
}

form{display:grid;gap:14px;width:100%;}
input{
    padding:13px;
    background: rgba(30,30,30,0.8);
    border:1px solid #333;
    border-radius:7px;
    color:#fff;
}
input:focus{outline:none;border-color:#c62828;}
label{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:13px;
    color:#ccc;
}
#barberId{
    display:none;
    background: rgba(30,30,30,0.8);
    border:1px dashed #555;
    padding:10px;
    border-radius:6px;
    color:#fff;
    font-size:13px;
}
button{
    margin-top:10px;
    padding:14px;
    background:#c62828;
    border:none;
    border-radius:7px;
    color:#fff;
    letter-spacing:2px;
    cursor:pointer;
}
button:hover{background:#9f1f1f;}
.error{background:#7f1d1d;padding:12px;border-radius:6px;margin-bottom:15px;text-align:center;}
.success{background:transparent;padding:12px 0;margin-bottom:15px;text-align:center;color:#fff;font-weight:600;}
.register-card .links{
    margin-top:20px;
    font-size:16px;
    color:#fff;
    text-align:center;
}
.register-card .links a{
    color:#1e88e5;
    font-weight:600;
    text-decoration:none;
}
.register-card .links a:hover{text-decoration:underline;}
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

</style>
</head>
<body>

<!-- LOGO TOP RIGHT -->
<div class="logo-circle">
    <img src="arslogo.jpg" alt="Ariel’s Barbershop Logo">
</div>

<!-- BACKGROUND VIDEO -->
<video autoplay muted loop playsinline class="bg-video">
    <source src="ars.mp4..mp4" type="video/mp4">
</video>
<div class="video-overlay"></div>

<div class="register-card">
    <div class="brand">
        <h1>Ariel’s <span class="brand-accent">Barbershop</span></h1>
        <div class="decor"></div>
        <p class="subtitle">Create your account</p>
    </div>

    <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
        <div class="links"><a href="login.php">Go to Login</a></div>
    <?php else: ?>
    <form method="POST">
        <input name="fullname" placeholder="Full Name" required>
        <input name="username" placeholder="Username" required>
        <input name="email" type="email" placeholder="Email" required>
        <input name="phone" placeholder="Phone" required>
        <div class="password-box">
    <input 
        id="password"
        name="password" 
        type="password" 
        placeholder="Password" 
        required
        minlength="8"
        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$"
    >

    <span class="toggle-password" onclick="togglePassword()">👁</span>
</div>

        <label>
            <input type="checkbox" id="isBarber" name="is_barber">
            Register as Barber
        </label>

        <input id="barberId" value="Barber ID will be generated after approval" readonly>

        <button type="submit">REGISTER</button>
    </form>

    <div class="links">
        Already have an account? <a href="login.php">Login</a>
    </div>
    <?php endif; ?>
</div>

<script>
const chk = document.getElementById('isBarber');
const barberId = document.getElementById('barberId');
chk.addEventListener('change', () => {
    barberId.style.display = chk.checked ? 'block' : 'none';
});
</script>
<script>
function togglePassword() {
    const pass = document.getElementById("password");

    if(pass.type === "password"){
        pass.type = "text";
    }else{
        pass.type = "password";
    }
}
</script>
</body>
</html>