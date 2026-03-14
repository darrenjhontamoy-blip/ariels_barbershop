<?php
session_start();
include '../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='customer'){
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$current = $_POST['current'] ?? '';
$new     = $_POST['new'] ?? '';
$confirm = $_POST['confirm'] ?? '';

// Fetch user
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT password FROM users WHERE id=$userId"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Password</title>
<style>
body{
    font-family: Poppins,Segoe UI,Arial;
    background:#f6f7fb;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.card{
    background:#fff;
    padding:30px 40px;
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,.1);
    text-align:center;
}
.message{
    margin-bottom:20px;
    font-size:16px;
    font-weight:600;
}
.success{ color:#22c55e; }
.error{ color:#dc2626; }
.back-btn{
    display:inline-block;
    padding:8px 18px;
    background:#4f46e5;
    color:#fff;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
}
</style>
</head>
<body>
<div class="card">
<?php
// Verify current password
if(!password_verify($current, $user['password'])){
    echo '<div class="message error">❌ Current password is incorrect</div>';
} 
// Check new password match
elseif($new !== $confirm){
    echo '<div class="message error">❌ New password and confirmation do not match</div>';
}
// Hash and update
else{
    $hash = password_hash($new, PASSWORD_DEFAULT);
    if(mysqli_query($conn,"UPDATE users SET password='$hash' WHERE id=$userId")){
        echo '<div class="message success">✅ Password updated successfully</div>';
        // Auto-redirect to profile settings after 2 seconds
        echo '<script>setTimeout(() => { window.location.href = "profile_settings.php?tab=security"; }, 2000);</script>';
    } else {
        echo '<div class="message error">❌ Error: '.mysqli_error($conn).'</div>';
    }
}
?>
<!-- Back button always goes to profile_settings.php -->
<a href="profile_settings.php?tab=security" class="back-btn">← Back</a>
</div>
</body>
</html>
