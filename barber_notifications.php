<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'barber') {
    header("Location: ../login.php");
    exit();
}

$barberId = (int)($_SESSION['user_id'] ?? 0);

// Mark all as read pag open ng page
mysqli_query($conn, "UPDATE notifications SET status='read' WHERE user_id=$barberId");

// Get notifications
$result = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id=$barberId ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Notifications</title>
<style>
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background: linear-gradient(135deg,#0b3d91,#c1121f);
    padding:40px;
}

.container{
    max-width:800px;
    margin:auto;
}

.card{
    background:#fff;
    padding:18px;
    margin-bottom:15px;
    border-radius:14px;
    box-shadow:0 8px 20px rgba(0,0,0,.2);
}

.card small{
    color:#777;
}

.back-btn{
    display:inline-block;
    margin-bottom:20px;
    text-decoration:none;
    color:#fff;
    font-weight:bold;
}
</style>
</head>
<body>

<div class="container">
    <a href="barber_dashboard.php" class="back-btn">← Back to Dashboard</a>

    <h2 style="color:white;">Your Notifications</h2>

    <?php if(mysqli_num_rows($result) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="card">
                <?= nl2br(htmlspecialchars($row['message'])) ?>
                <br>
                <small><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card">No notifications found.</div>
    <?php endif; ?>
</div>

</body>
</html>