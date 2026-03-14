<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

$error = "";

define('BASE_URL', '/ariels_barbershop');
// LOGIN ATTEMPT SETTINGS
$max_attempts = 3;
$lock_time = 30; // seconds

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if (!isset($_SESSION['lock_time'])) {
    $_SESSION['lock_time'] = 0;
}

// check if locked
$remaining = 0;

if ($_SESSION['lock_time'] > time()) {

    $remaining = $_SESSION['lock_time'] - time();
    $error = "Too many login attempts. Please wait $remaining seconds.";

}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $remaining <= 0) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === "" || $password === "") {
        $error = "Please fill in all fields.";
    } else {

        $stmt = $conn->prepare("
            SELECT id, fullname, password, role, status, email_address
            FROM users
            WHERE username = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            $hashedPassword = $row['password'];
            $role = strtolower(trim($row['role']));
            $status = isset($row['status']) ? ucfirst(strtolower(trim($row['status']))) : 'Active';
            $id = $row['id'];
            $fullname = $row['fullname'];
            $email_address = $row['email_address'];

            if (password_verify($password, $hashedPassword)) {

                // RESET LOGIN ATTEMPTS
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lock_time'] = 0;


                if ($role === 'barber' && $status !== 'Active') {
                    $error = "Your barber account is not yet approved by the admin.";
                    session_destroy();
                } else {

                    $_SESSION['user_id'] = $id;
                    $_SESSION['fullname'] = $fullname;
                    $_SESSION['role'] = $role;
                    $_SESSION['email_address'] = $email_address;

                    if ($role === 'barber') {
                        $update = $conn->prepare("
                            UPDATE users 
                            SET last_activity = NOW(), online_status = 'Online' 
                            WHERE id = ?
                        ");
                        $update->bind_param("i", $id);
                        $update->execute();
                        $update->close();
                    }

                    if ($role === 'admin') {
                        header("Location: " . BASE_URL . "/admin/dashboard.php");
                        exit();
                    }

                    if ($role === 'barber') {
                        header("Location: " . BASE_URL . "/barber/barber_dashboard.php");
                        exit();
                    }

                    if ($role === 'customer') {
                        header("Location: " . BASE_URL . "/customer/book_appointment.php");
                        exit();
                    }

                    session_destroy();
                    $error = "Invalid account role.";
                }

            } else {

             $_SESSION['login_attempts']++;
if ($_SESSION['login_attempts'] >= $max_attempts) {
    $_SESSION['lock_time'] = time() + $lock_time;

    // **Agad iset ang $remaining para sa countdown**
    $remaining = $_SESSION['lock_time'] - time();

    $error = "Too many failed attempts. Please wait $remaining seconds.";
} else {
    $remaining_attempts = $max_attempts - $_SESSION['login_attempts'];
    $error = "Incorrect password. $remaining_attempts attempt(s) remaining.";
}
            }

        } else {
            $error = "User not found.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | Ariel’s Barbershop</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box}

body{
    min-height:100vh;
    display:flex;
    flex-direction:column;
    justify-content:flex-start;
    align-items:center;
    font-family:'Oswald', sans-serif;
    padding-top: 80px; /* spacing from top */
    background:#000;
    position:relative;
    overflow-x:hidden;
}

/* LOGO TOP RIGHT - BILOG TRANSPARENT */
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
    padding: 0;
}

.logo-circle img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover; /* sakto sa bilog */
}

/* BACKGROUND VIDEO */
.bg-video {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -2;
}

.video-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.65);
    z-index: -1;
}

/* LOGIN CARD */
.login-card {
    width: 900px;
    max-width: 95%;
    display: flex;
    align-items: center;

    /* GLASS EFFECT */
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);

    border-radius: 25px;
    padding: 40px;

    /* GRADIENT BORDER */
    border: 2px solid transparent;
    background-clip: padding-box;
    position: relative;

    box-shadow: 0 0 40px rgba(0,0,0,0.4);
}
.login-card::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 25px;
    padding: 2px; /* kapal ng border */
    background: linear-gradient(135deg, #4facfe, #8e44ad, #ff4d4d);
    -webkit-mask: 
        linear-gradient(#fff 0 0) content-box, 
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}

.login-left {
    flex: 1;
    padding: 40px 30px 30px 30px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    border-right: 1px solid rgba(255, 255, 255, .08);
}

.login-left h1 {
    font-family: 'Playfair Display', serif;
    font-size: 40px;
    margin-bottom: 10px;
}

.brand-main { color: #fff; }
.brand-accent { color: #c62828; }

.decor {
    width: 80px;
    height: 3px;
    background: linear-gradient(to right, #c62828, #1e88e5);
    margin: 25px 0;
}

.login-left p {
    color: #ccc;
    line-height: 1.6;
}

.login-right {
    flex: 1;
    padding: 30px 30px 30px 30px;
    color: #fff;
}

.back-home {
    display: inline-block;
    margin-bottom: 20px;
    font-size: 13px;
    color: #fff;
    text-decoration: none;
    font-weight: 600;
}

.back-home:hover {
    text-decoration: underline;
}

.login-right h2 {
    font-size: 28px;
    text-align: center;
    font-weight: 700;
}

.subtitle {
    font-size: 14px;
    color: #ccc;
    text-align: center;
    margin-bottom: 25px;
}

 form input {
    width: 100%;
    padding: 14px;
    margin-bottom: 15px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    border-radius: 8px;
    backdrop-filter: blur(10px);
}

form input:focus {
    outline: none;
    border-color: #4facfe;
}

form button {
    width: 100%;
    padding: 14px;
    background: #c62828;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-size: 15px;
    letter-spacing: 2px;
    cursor: pointer;
    transition: .3s;
}

form button:hover {
    background: #9f1f1f;
}

.error {
    background: #7f1d1d;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-size: 14px;
}

.register-text {
    margin-top: 20px;
    font-size: 14px;
    text-align: center;
}

.register-text a {
    color: #1e88e5;
    text-decoration: none;
    font-weight: 600;
}

.register-text a:hover {
    text-decoration: underline;
}

/* CONTACT FOOTER */
.contact-footer {
    width: 100%;
    text-align: center;
    margin-top: 40px;
    padding: 30px 20px 60px;
    color: #ffffff;
    font-size: 15px;
    line-height: 1.9;
}

@media(max-width:768px){
    .login-card { flex-direction: column; }
    .login-left { border-right: none; border-bottom: 1px solid rgba(255,255,255,.08); padding: 60px 20px 40px 20px; }
    .login-right { padding: 60px 20px 40px 20px; }
    .logo-circle { width: 60px; height: 60px; top: 15px; right: 20px; }
}
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

<div class="login-card">

    <div class="login-left">
        <h1>
            <span class="brand-main">Ariel’s</span>
            <span class="brand-accent">Barbershop</span>
        </h1>
        <div class="decor"></div>
        <p>
            Precision cuts. Clean fades.<br>
            Professional grooming for the modern gentleman.
        </p>
    </div>

    <div class="login-right">
        <a href="<?= BASE_URL ?>/home.php" class="back-home">← Back to Home</a>
        <h2>WELCOME BACK</h2>
        <p class="subtitle">Sign in to your account</p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" <?= (isset($remaining) && $remaining > 0) ? 'disabled' : '' ?>>
LOGIN
</button>
        </form>

        <p class="register-text">
            Don’t have an account?
            <a href="<?= BASE_URL ?>/register.php">Create Account</a>
        </p>
    </div>

</div>

<div class="contact-footer">
    <p><strong>Visit us or reach out to book your next appointment at Ariel’s Barbershop.</strong></p>
    <p>SBR Center Builders Inc, Unit 5 Lot 2115-5-3-G, Maribel Subd St, Maribel Sub, Canlalay, Biñan, Laguna</p>
    <p>TM-09658932994 | SMART-09494159956</p>
    <p>Email: Trijoariel@yahoo.com</p>
</div>
<script>

let remaining = <?= isset($remaining) ? $remaining : 0 ?>;

if (remaining > 0) {
    const btn = document.querySelector("button[type='submit']");
    const errorBox = document.querySelector(".error");

    btn.disabled = true;

    const timer = setInterval(function(){
        if (remaining <= 0) {
            clearInterval(timer);
            btn.disabled = false;
            if (errorBox) errorBox.innerHTML = "You can now login.";
        } else {
            if (errorBox) errorBox.innerHTML = "Too many login attempts. Try again in " + remaining + " seconds.";
        }
        remaining--;
    }, 1000);
}

</script>
</body>
</html>