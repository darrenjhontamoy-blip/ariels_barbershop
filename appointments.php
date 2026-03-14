    <?php
    session_start();
    include '../config.php';

    /* CUSTOMER ONLY */
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
        header("Location: ../login.php");
        exit();
    }

    $customer_name = $_SESSION['fullname'];
    $today = date('Y-m-d');
    
/* ================= DELETE NOTIFICATION ================= */
if (isset($_POST['delete_notification'])) {

    $deleteId = (int)$_POST['delete_notification_id'];

    $stmt = $conn->prepare("
        DELETE FROM appointments
        WHERE id = ? AND customer_name = ?
    ");
    $stmt->bind_param("is", $deleteId, $customer_name);
    $stmt->execute();
    $stmt->close();

    header("Location: appointments.php");
    exit();
}
    /* ===================== CANCEL APPOINTMENT ===================== */
    /* ===================== CANCEL APPOINTMENT ===================== */
if (isset($_POST['cancel_id']) && isset($_POST['cancel_reason'])) {

    $id = (int)$_POST['cancel_id'];
    $reason = trim($_POST['cancel_reason']);

    if ($reason !== '') {

        // 🔹 Kunin muna barber_id
        $getBarber = $conn->prepare("
            SELECT barber_id 
            FROM appointments 
            WHERE id = ?
        ");
        $getBarber->bind_param("i", $id);
        $getBarber->execute();
        $barberData = $getBarber->get_result()->fetch_assoc();
        $getBarber->close();

        $barberId = (int)($barberData['barber_id'] ?? 0);

        // 🔹 Update appointment
       $stmt = $conn->prepare("
    UPDATE appointments
    SET status = 'Cancelled', 
        cancel_reason = ?, 
        cancelled_by = 'customer',
        cancelled_at = NOW()
    WHERE id = ? 
    AND customer_name = ? 
    AND status='Pending'
");
        $stmt->bind_param('sis', $reason, $id, $customer_name);
        $stmt->execute();
        $stmt->close();

        // 🔔 Insert notification for barber
        if ($barberId > 0) {

            $message = "Customer {$customer_name} cancelled an appointment. Reason: {$reason}";

            $notif = $conn->prepare("
                INSERT INTO notifications (user_id, message, status, created_at)
                VALUES (?, ?, 'unread', NOW())
            ");
            $notif->bind_param("is", $barberId, $message);
            $notif->execute();
            $notif->close();
        }

        header("Location: appointments.php?filter=upcoming");
        exit();
    }
}
    /* ===================== FETCH COUNTS ===================== */
    function fetchCount($conn, $customer_name, $where = '') {
        $sql = "SELECT COUNT(*) AS total FROM appointments WHERE customer_name = ?";
        if ($where) $sql .= " AND $where";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $customer_name);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($res['total'] ?? 0);
    }

    $total = fetchCount($conn, $customer_name);
    $upcoming = fetchCount($conn, $customer_name, "appointment_date >= '$today' AND status='Pending'");
    $completed = fetchCount($conn, $customer_name, "status='Completed'");

    /* ===================== FILTER LOGIC ===================== */
    $filter = $_GET['filter'] ?? 'total';
    $where = "customer_name = ?";
    $where_param = $customer_name;

    if ($filter === 'upcoming') {
        $where .= " AND appointment_date >= '$today' AND status='Pending'";
    } elseif ($filter === 'completed') {
        $where .= " AND status='Completed'";
    }

    /* ===================== FETCH APPOINTMENTS ===================== */
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE $where ORDER BY appointment_date DESC, appointment_time DESC");
    $stmt->bind_param('s', $where_param);
    $stmt->execute();
    $appointments = $stmt->get_result();
    $stmt->close();

    /* ===================== FETCH NOTIFICATIONS ===================== */
    $notifCount = 0;
    $notifications = [];
    $stmt = $conn->prepare("
        SELECT id, barber_name, appointment_date, appointment_time, status
        FROM appointments
        WHERE customer_name = ? AND status IN ('Accepted','Completed')
        ORDER BY appointment_date DESC, appointment_time DESC
    ");
    $stmt->bind_param('s', $customer_name);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()){
        $notifications[] = $row;
        $notifCount++;
    }
    $stmt->close();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>My Appointments</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
    :root{
        --sidebar-width: 260px;
        --content-gap: 40px;
    }

    *{
        box-sizing:border-box;
        margin:0;
        padding:0;
        font-family:'Poppins','Segoe UI',Arial,sans-serif;
    }

    body{
        background: linear-gradient(135deg, #0b3d91 0%, #1e3a8a 40%, #7f1d1d 70%, #c1121f 100%);
        min-height:100vh;
        color:#111827;
    }

    /* ================= HEADER ================= */
    .header-card {
        background: linear-gradient(135deg, #6366f1, #22c55e);
        border-radius: 18px;
        padding: 25px 30px;
        color: #fff;
        text-align: center;
        margin: 20px 20px 35px calc(var(--sidebar-width) + var(--content-gap) + 30px); /* Malayo sa sidebar at kanan */
        box-shadow: none;
        position: relative;
    }
    .header-card h1 {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 15px;
    }

    .header-card .subtitle {
        font-size: 14px;
        opacity: 0.9;
    }


    /* ================= NOTIFICATION ================= */
    .notif-container{
        position:absolute;
        top:20px;
        right:30px;
    }

    .notif-bell{
        cursor:pointer;
        font-size:24px;
        position:relative;
    }

    .notif-bell .badge{
        background:red;
        color:#fff;
        font-size:12px;
        padding:2px 6px;
        border-radius:50%;
        position:absolute;
        top:-8px;
        right:-10px;
    }

    .notification-dropdown{
        display:none;
        position:absolute;
        right:0;
        top:36px;
        width:300px;
        background:#fff;
        color:#111;
        border-radius:8px;
        box-shadow:0 6px 18px rgba(0,0,0,.2);
        max-height:350px;
        overflow-y:auto;
        z-index:1000;
    }
.notification-dropdown{
    z-index: 9999;
}

.delete-notif{
    cursor:pointer;
    pointer-events:auto;
}
    .notification-dropdown.active{
        display:block;
    }

    .notification-dropdown h4{
        margin:10px;
        font-size:14px;
        color:#555;
    }

    .notification-dropdown .notif-item{
        padding:10px;
        border-bottom:1px solid #eee;
        font-size:13px;
    }

    .notification-dropdown .notif-item:last-child{
        border-bottom:none;
    }
    
    /* ================= CARDS ================= */
    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); /* Responsive cards */
        gap: 16px;
        max-width: calc(100% - var(--sidebar-width) - 60px);
        margin-left: calc(var(--sidebar-width) + var(--content-gap) + 30px);
        margin-right: 30px;
        margin-bottom: 40px;
    }
    .card-link{
        text-decoration:none;
        color:inherit;
    }

    /* WHITE CARD + GRADIENT BORDER */
    .card {
        padding: 22px;
        border-radius: 18px;
        text-align: center;
        cursor: pointer;
        position: relative;
        background: #ffffff;
        border: 2px solid transparent;
        background-clip: padding-box, border-box;
        background-origin: padding-box, border-box;
        background-image: linear-gradient(#ffffff, #ffffff), linear-gradient(135deg, #3b82f6, #a855f7, #ef4444);
        box-shadow: none;
    }

    .card:hover {
        box-shadow: none;
    }

    .card h4 {
        margin: 0;
        font-size: 13px;
        color: #6b7280;
    }

    .card h2 {
        margin-top: 10px;
        font-size: 22px;
        font-weight: 800;
    }


    /* ================= TABLE ================= */
    .table-wrapper {
        border-radius: 20px;
        padding: 30px;
        margin-left: calc(var(--sidebar-width) + var(--content-gap) + 30px); /* Malayo sa sidebar */
        margin-right: 30px; /* Malayo sa kanan */
        background: #ffffff;
        border: 2px solid transparent;
        background-clip: padding-box, border-box;
        background-origin: padding-box, border-box;
        background-image: linear-gradient(#ffffff, #ffffff), linear-gradient(135deg, #3b82f6, #a855f7, #ef4444);
        box-shadow: none;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #f3f4f6;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6b7280;
        text-align: left;
        padding: 14px;
    }

    td {
        padding: 14px;
        border-bottom: 1px solid #eee;
    }

    tr:hover td {
        background: #f9fafb;
    }

    /* ================= STATUS BADGES ================= */
    .status {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .status.Pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status.Completed {
        background: #dcfce7;
        color: #166534;
    }

    .status.Cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    /* ================= CANCEL BUTTON ================= */
    .cancel-btn {
        background: #ef4444;
        color: #fff;
        border: none;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 5px;
        transition: .25s;
    }

    .cancel-btn:hover {
        background: #dc2626;
    }

    .cancel-reason-select,
    .cancel-reason-other {
        padding: 6px 8px;
        font-size: 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        width: 100%;
        margin-top: 5px;
    }

    .cancel-reason-other {
        display: none;
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 900px) {
        .cards, .table-wrapper {
            margin-left: 20px;
            margin-right: 20px;
        }
    }
    </style>
    </head>
    <body>
    <!-- CONFIRM MODAL (DITO MO ILAGAY) -->
   <div id="confirmModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.5); justify-content:center; align-items:center; z-index:10000;">
    <div style="background:#fff; padding:20px 25px; border-radius:10px; text-align:center; width:300px;">
        <p style="margin-bottom:20px;">Delete this notification?</p>
        <button id="confirmYes" style="background:#ef4444; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer;">Yes</button>
        <button id="confirmNo" style="background:#ccc; border:none; padding:6px 14px; border-radius:6px; cursor:pointer;">No</button>
        
    </div>
</div>
    <?php include 'sidebar_customer.php'; ?>

    <div class="header-card">
    <h1>MY APPOINTMENTS</h1>
    <p class="subtitle">View and manage your appointments</p>

    <div class="notif-container">
        <div class="notif-bell" id="notifBell">
            🔔
            <?php if($notifCount>0): ?>
                <span class="badge"><?= $notifCount ?></span>
            <?php endif; ?>
        </div>

        <!-- DITO LANG dapat ang notifications -->
        <div class="notification-dropdown" id="notifDropdown">
            <h4>Notifications</h4>

            <?php if($notifCount>0): ?>
                <?php foreach($notifications as $n): ?>
    <div class="notif-item">
        <span>
            Appointment with 
            <strong><?= htmlspecialchars($n['barber_name']) ?></strong>
            on <?= $n['appointment_date'] ?>
            <?= date('h:i A', strtotime($n['appointment_time'])) ?>
            is <strong><?= $n['status'] ?></strong>
        </span>

        <form method="POST" style="margin-top:6px;">
            <input type="hidden" 
                   name="delete_notification_id" 
                   value="<?= $n['id'] ?>">

            <button type="submit" 
                    name="delete_notification"
                    style="background:#ef4444;color:#fff;border:none;padding:4px 10px;border-radius:6px;cursor:pointer;">
                Delete
            </button>
        </form>
    </div>
<?php endforeach; ?>
                
            <?php else: ?>
                <div class="notif-item">No new notifications</div>
            <?php endif; ?>
        </div>

    </div>
</div>

    <div class="cards">
        <a href="appointments.php?filter=total" class="card-link">
            <div class="card <?= $filter==='total'?'active':'' ?>">
                <h4>Total</h4>
                <h2><?= $total ?></h2>
            </div>
        </a>
        <a href="appointments.php?filter=upcoming" class="card-link">
            <div class="card <?= $filter==='upcoming'?'active':'' ?>">
                <h4>Upcoming</h4>
                <h2><?= $upcoming ?></h2>
            </div>
        </a>
        <a href="appointments.php?filter=completed" class="card-link">
            <div class="card <?= $filter==='completed'?'active':'' ?>">
                <h4>Completed</h4>
                <h2><?= $completed ?></h2>
            </div>
        </a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Barber</th>
                    <th>Service</th>
                    <th>Hairstyle</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <?php if($filter==='upcoming'): ?><th>Action</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if($appointments && $appointments->num_rows > 0): ?>
                    <?php while($row = $appointments->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($row['barber_name']) ?></strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['service']) ?>
                            </td>

                            <td>
                                <?php if(!empty($row['hairstyle'])): ?>
                                    <?= htmlspecialchars($row['hairstyle']) ?>
                                <?php else: ?>
                                    <span style="color:#9ca3af;">Not specified</span>
                                <?php endif; ?>
                            </td>

                            <td><?= htmlspecialchars($row['appointment_date']) ?></td>

                            <td><?= date('h:i A', strtotime($row['appointment_time'])) ?></td>

                            <td>
                                <span class="status <?= htmlspecialchars($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>

                            <?php if($filter==='upcoming'): ?>
                            <td>
                                <?php if($row['status']==='Pending'): ?>
                                <form method="POST" class="cancel-form">
                                    <input type="hidden" name="cancel_id" value="<?= $row['id'] ?>">

                                    <select name="cancel_reason_select" class="cancel-reason-select" onchange="toggleOtherReason(this)">
                                        <option value="">-- Select reason --</option>
                                        <option value="Schedule conflict">Schedule conflict</option>
                                        <option value="Found another barber">Found another barber</option>
                                        <option value="Too expensive">Too expensive</option>
                                        <option value="Other">Other</option>
                                    </select>

                                    <input type="text" name="cancel_reason_other" 
                                        class="cancel-reason-other" 
                                        placeholder="Type your reason">

                                    <button type="submit" class="cancel-btn">
                                        Cancel
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span style="color:#9ca3af;">—</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $filter==='upcoming'?7:6 ?>" 
                            style="text-align:center; color:#6b7280;">
                            No appointments found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>


    <script>
    function toggleOtherReason(select) {
        var form = select.closest('.cancel-form');
        var otherInput = form.querySelector('.cancel-reason-other');
        otherInput.style.display = (select.value === 'Other') ? 'block' : 'none';
        otherInput.required = (select.value === 'Other');
    }

    // Handle cancel reason form submit
    document.querySelectorAll('.cancel-form').forEach(function(form){
        form.addEventListener('submit', function(e){
            var select = form.querySelector('.cancel-reason-select');
            var other = form.querySelector('.cancel-reason-other');
            var hiddenInput = form.querySelector('input[name="cancel_reason"]');
            if(!hiddenInput){
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'cancel_reason';
                form.appendChild(hiddenInput);
            }
            if(select.value === 'Other'){
                if(other.value.trim() === ''){
                    e.preventDefault();
                    alert('Please type your reason.');
                    return false;
                }
                hiddenInput.value = other.value.trim();
            } else {
                hiddenInput.value = select.value;
            }
        });
    });

    // Notification bell toggle
    const bell = document.getElementById('notifBell');
    const dropdown = document.getElementById('notifDropdown');
    bell.addEventListener('click', ()=>{ dropdown.classList.toggle('active'); });
    // Close dropdown on outside click
    document.addEventListener('click', e => {
        if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });
    let deleteForm = null;

document.querySelectorAll('.delete-notif').forEach(button => {
    button.addEventListener('click', function() {
        deleteForm = this.closest('form');
        document.getElementById('confirmModal').style.display = 'flex';
    });
});

document.getElementById('confirmYes').addEventListener('click', function() {
    if (deleteForm) {
        deleteForm.submit();
    }
});

document.getElementById('confirmNo').addEventListener('click', function() {
    document.getElementById('confirmModal').style.display = 'none';
});
    </script>
    
    </body>
    </html>
