<?php
session_start();
include '../config.php';

/* BARBER ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'barber') {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$message = "";

/* UPDATE PROFILE */
if (isset($_POST['update_profile'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email_address']);

    mysqli_query($conn, "
        UPDATE users
        SET fullname='$fullname', email_address='$email'
        WHERE id=$userId AND role='barber'
    ");

    $_SESSION['fullname'] = $fullname;
    $message = "Profile updated successfully";
}

/* CHANGE PASSWORD */
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn,"SELECT password FROM users WHERE id=$userId");
    $row = mysqli_fetch_assoc($check);

    if (password_verify($current, $row['password'])) {
        mysqli_query($conn,"UPDATE users SET password='$new' WHERE id=$userId");
        $message = "Password changed successfully";
    } else {
        $message = "Current password is incorrect";
    }
}

/* FETCH USER */
$user = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT fullname, username, email_address
    FROM users
    WHERE id=$userId AND role='barber'
"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Barber Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif}
body{margin:0;background:#f4f6f9}
/* ===== CENTERED WHITE HEADER ===== */
.page-header{
    text-align:center;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(8px);
    padding:25px;
    border-radius:20px;
    margin-bottom:30px;
    box-shadow:0 10px 25px rgba(0,0,0,.25);
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
/* MAIN */
.main{
    margin-left:270px;
    padding:30px;
}

/* HEADER */
h1{
    font-size:26px;
    margin-bottom:4px;
}
p{
    color:#6b7280;
    margin-top:0;
    margin-bottom:20px;
}

/* ALERT */
.alert{
    margin-bottom:20px;
    padding:14px 18px;
    border-radius:12px;
    background:#dcfce7;
    color:#166534;
    font-weight:500;
}

/* CARD */
.card{
    background:linear-gradient(135deg,#ffffff,#f9fafb);
    padding:25px;
    margin-bottom:25px;
    border-radius:16px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
    position:relative;
}
.card::before{
    content:'';position:absolute;top:0;left:0;width:100%;height:4px;
    background:linear-gradient(90deg,#4f46e5,#22c55e);
    border-top-left-radius:16px;border-top-right-radius:16px;
}

/* FORM */
input{
    width:100%;
    padding:12px 14px;
    margin:10px 0;
    border-radius:10px;
    border:1px solid #ccc;
    font-size:14px;
}

input:disabled{
    background:#f1f3f5;
    color:#6b7280;
}

button{
    padding:12px 18px;
    border:none;
    border-radius:10px;
    background:linear-gradient(135deg,#4f46e5,#22c55e);
    color:#fff;
    font-weight:600;
    cursor:pointer;
    transition:.3s;
}
button:hover{
    opacity:.9;
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
</style>
</head>

<body>

<?php require_once 'sidebar_barber.php'; ?>

<div class="main">

<div class="page-header">
    <h1>PROFILE SETTINGS</h1>
    <p>Manage your personal information</p>
</div>
<?php if($message): ?>
<div class="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- PROFILE INFO -->
<div class="card">
<h3>Profile Information</h3>
<form method="POST">
    <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
    <input type="email" name="email_address" value="<?= htmlspecialchars($user['email_address']) ?>" required>
    <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>

    <button name="update_profile">Update Profile</button>
</form>
</div>

<!-- PASSWORD -->
<div class="card">
<h3>Change Password</h3>
<form method="POST">
    <input type="password" name="current_password" placeholder="Current Password" required>
    <input type="password" name="new_password" placeholder="New Password" required>

    <button name="change_password">Change Password</button>
</form>
</div>

</div>
</body>
</html>
