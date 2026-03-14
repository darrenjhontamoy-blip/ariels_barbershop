<?php
session_start();
include '../config.php';

/* ======================
   BARBER ONLY
====================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'barber') {
    header("Location: ../login.php");
    exit();
}

$barberId = (int)$_SESSION['user_id'];

/* ======================
   FETCH ALL NOTIFICATIONS
====================== */
$res = mysqli_query($conn,"
    SELECT id, message, created_at, is_read
    FROM notifications
    WHERE user_id = $barberId
    ORDER BY created_at DESC
");

/* ======================
   MARK ALL AS READ
====================== */
mysqli_query($conn,"
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = $barberId
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Schedule Notifications</title>

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif}
body{margin:0;background:#f4f6f9}

/* MAIN */
.main{
    margin-left:270px;
    padding:40px;
}

/* HEADER */
.page-title{
    display:flex;
    align-items:center;
    gap:14px;
    margin-bottom:30px;
}
.page-title h1{
    margin:0;
    font-size:26px;
}

/* CARD */
.card{
    background:#fff;
    border-radius:18px;
    box-shadow:0 8px 22px rgba(0,0,0,.08);
    padding:22px 26px;
    margin-bottom:18px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    transition:.2s;
}
.card:hover{
    transform:translateY(-2px);
}

/* LEFT */
.card-left{
    display:flex;
    gap:16px;
    align-items:flex-start;
}
.icon{
    font-size:30px;
}

/* MESSAGE */
.message{
    font-size:16px;
    font-weight:600;
    margin-bottom:6px;
}

/* TIME */
.time{
    font-size:13px;
    color:#6b7280;
}

/* TAG */
.tag{
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
    color:#fff;
}
.new{background:#ef4444}
.read{background:#9ca3af}

/* EMPTY */
.empty{
    background:#fff;
    padding:40px;
    border-radius:18px;
    text-align:center;
    color:#6b7280;
    box-shadow:0 8px 22px rgba(0,0,0,.08);
    font-size:16px;
}
</style>
</head>

<body>

<?php include 'sidebar_barber.php'; ?>

<div class="main">

    <div class="page-title">
        <span style="font-size:32px;">🔔</span>
        <h1>Schedule Notifications</h1>
    </div>

    <?php if(mysqli_num_rows($res) > 0): ?>
        <?php while($n = mysqli_fetch_assoc($res)): ?>
            <div class="card">
                <div class="card-left">
                    <div class="icon">📅</div>
                    <div>
                        <div class="message">
                            <?= htmlspecialchars($n['message']) ?>
                        </div>
                        <div class="time">
                            <?= date("l, F d, Y • h:i A", strtotime($n['created_at'])) ?>
                        </div>
                    </div>
                </div>

                <?php if($n['is_read'] == 0): ?>
                    <div class="tag new">NEW</div>
                <?php else: ?>
                    <div class="tag read">READ</div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty">
            📭 No schedule notifications yet.
        </div>
    <?php endif; ?>

</div>

</body>
</html>
