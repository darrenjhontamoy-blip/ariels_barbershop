<?php
session_start();
include '../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

/* ADMIN ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* HANDLE STATUS UPDATE */
if (isset($_POST['action'], $_POST['id'])) {
    $id = (int)$_POST['id'];

    $get = mysqli_query($conn, "SELECT * FROM appointments WHERE id=$id");
    $data = mysqli_fetch_assoc($get);

    $status = '';
    $subject = '';
    $body = '';

    /* ACCEPT */
    if ($_POST['action'] === 'accept') {
        mysqli_query($conn, "UPDATE appointments SET status='Accepted' WHERE id=$id");

        $status = 'ACCEPTED';
        $subject = 'Appointment Accepted';
        $body = "
        Hi {$data['customer_name']}!<br><br>
        ✅ <b>Your appointment has been ACCEPTED!</b><br><br>
        📅 Date: <b>".date('M d, Y', strtotime($data['appointment_date']))."</b><br>
        ⏰ Time: <b>".date('h:i A', strtotime($data['appointment_time']))."</b><br><br>
        Please arrive 10 minutes early.<br><br>
        Thank you for choosing Ariel Barbershop!
        ";
    }

    /* CANCEL */
    if ($_POST['action'] === 'cancel') {
        mysqli_query($conn, "UPDATE appointments SET status='Cancelled' WHERE id=$id");

        $status = 'CANCELLED';
        $subject = 'Appointment Cancelled';
        $body = "
        Hi {$data['customer_name']}!<br><br>
        ❌ Your appointment on <b>".date('M d, Y', strtotime($data['appointment_date']))."</b>
        at <b>".date('h:i A', strtotime($data['appointment_time']))."</b> has been cancelled.<br><br>
        Please contact us if you want to reschedule.
        ";
    }

    /* SEND EMAIL */
    if ($subject) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jhondarren86@gmail.com';
            $mail->Password = 'ptbf nqeb ijoj eytv';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('jhondarren86@gmail.com', 'Ariel Barbershop');
            $mail->addAddress($data['customer_email'], $data['customer_name']);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
        } catch (Exception $e) {}
    }

    /* SEND SMS */
    if (!empty($data['customer_phone']) && !empty($data['customer_carrier'])) {
        $gateways = [
            'Globe' => '@txt.globe.com.ph',
            'Smart' => '@messaging.smart.com.ph',
            'Sun'   => '@sms.sunph.com'
        ];

        if (isset($gateways[$data['customer_carrier']])) {
            $sms_email = $data['customer_phone'].$gateways[$data['customer_carrier']];

            try {
                $sms = new PHPMailer(true);
                $sms->isSMTP();
                $sms->Host = 'smtp.gmail.com';
                $sms->SMTPAuth = true;
                $sms->Username = 'jhondarren86@gmail.com';
                $sms->Password = 'ptbf nqeb ijoj eytv';
                $sms->SMTPSecure = 'tls';
                $sms->Port = 587;

                $sms->setFrom('jhondarren86@gmail.com', 'Ariel Barbershop');
                $sms->addAddress($sms_email);
                $sms->Subject = '';
                $sms->Body = "Hi {$data['customer_name']}, your appointment on "
                    .date('M d, Y', strtotime($data['appointment_date']))
                    ." at ".date('h:i A', strtotime($data['appointment_time']))
                    ." has been {$status}.";
                $sms->send();
            } catch (Exception $e) {}
        }
    }

    header("Location: admin_online_appointments.php");
    exit();
}

/* SEARCH */
$search = $_GET['search'] ?? '';
$searchEsc = mysqli_real_escape_string($conn, $search);

$sql = "SELECT * FROM appointments WHERE appointment_source='online'";
if ($searchEsc) {
    $sql .= " AND customer_name LIKE '%$searchEsc%'";
}
$sql .= " ORDER BY appointment_date, appointment_time";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Online Appointments - Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif}
body{margin:0;background:#f4f6f9}
.main{margin-left:240px;padding:30px}

.top-header{
    display:flex;
    justify-content:space-between;
    background:#fff;
    padding:20px 30px;
    border-radius:10px;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
    margin-bottom:25px;
}

.search-box input{
    padding:10px 14px;
    border-radius:8px;
    border:1px solid #ccc;
    width:260px;
    margin-bottom:15px;
}

.table-wrapper{
    background:#fff;
    border-radius:14px;
    box-shadow:0 4px 12px rgba(0,0,0,.08);
    overflow:hidden;
}

table{width:100%;border-collapse:collapse}
th,td{padding:14px}
th{background:#f1f3f5;font-size:13px;text-transform:uppercase}
tr:nth-child(even){background:#fafafa}

.status{padding:6px 14px;border-radius:20px;font-size:12px;font-weight:bold}
.Pending{background:#fde68a;color:#92400e}
.Accepted{background:#bfdbfe;color:#1e40af}
.Cancelled{background:#fecaca;color:#7f1d1d}

.actions form{display:flex;gap:8px}
.btn{padding:6px 14px;border:none;border-radius:6px;font-size:12px;cursor:pointer}
.complete{background:#22c55e;color:#fff}
.cancel{background:#ef4444;color:#fff}
</style>
</head>

<body>
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="top-header">
        <h1>ONLINE APPOINTMENTS</h1>
    </div>

    <form class="search-box" method="GET">
        <input type="text" name="search" placeholder="Search customer..." value="<?= htmlspecialchars($search) ?>">
    </form>

    <div class="table-wrapper">
        <table>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Barber</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while($r=mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= date('M d, Y',strtotime($r['appointment_date'])) ?></td>
                <td><?= date('h:i A',strtotime($r['appointment_time'])) ?></td>
                <td><?= htmlspecialchars($r['customer_name']) ?></td>
                <td><?= htmlspecialchars($r['service']) ?></td>
                <td><?= htmlspecialchars($r['barber_name']) ?></td>
                <td><span class="status <?= $r['status'] ?>"><?= $r['status'] ?></span></td>
                <td class="actions">
                    <?php if($r['status']=='Pending'): ?>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button class="btn accept" name="action" value="accept">Accept</button>
                        <button class="btn cancel" name="action" value="cancel">Cancel</button>
                    </form>
                    <?php else: ?>
                        <span style="color:#777;font-size:12px;">No actions</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
