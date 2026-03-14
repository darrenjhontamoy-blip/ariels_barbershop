<?php
session_start();
include '../config.php';

/* =======================
   ADMIN AUTH CHECK
======================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =======================
   HANDLE ADD / EDIT / DELETE
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ===== ADD USER ===== */
    if ($_POST['action'] === 'add') {
        $fullname = trim($_POST['fullname']);
        $username = trim($_POST['username']);
        $email_address    = trim($_POST['email_address']);
        $role     = $_POST['role'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users (fullname, username, email_address, role, password, status, created_at)
             VALUES (?, ?, ?, ?, ?, 'Active', NOW())"
        );
        $stmt->bind_param("sssss", $fullname, $username, $email_address, $role, $password);
        $stmt->execute();
        $stmt->close();

        header("Location: users.php");
        exit();
    }

    /* ===== EDIT USER ===== */
    if ($_POST['action'] === 'edit') {
        $id       = (int)$_POST['user_id'];
        $fullname = trim($_POST['fullname']);
        $username = trim($_POST['username']);
        $email_address    = trim($_POST['email_address']);
        $role     = $_POST['role'];

        $stmt = $conn->prepare(
            "UPDATE users SET fullname=?, username=?, email_address=?, role=? WHERE id=?"
        );
        $stmt->bind_param("ssssi", $fullname, $username, $email_address, $role, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: users.php");
        exit();
    }

    /* ===== DELETE USER ===== */
    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['user_id'];

        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: users.php");
        exit();
    }
}

/* =======================
   FETCH USERS
======================= */
$result = mysqli_query(
    $conn,
    "SELECT id, fullname, username, email_address, role, status, created_at
     FROM users
     ORDER BY id DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Users Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
/* -- Styles remain unchanged -- */
*{
    box-sizing:border-box;
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
    margin:0;
    padding:0;
}
body{background:#f4f6f9;color:#111;}
.main{margin-left:240px;padding:30px 30px 40px;}
.top-header{display:flex;justify-content:space-between;align-items:center;background:#e3e6ff;padding:20px 25px;border-radius:15px;box-shadow:0 8px 24px rgb(115 129 199 / 15%);margin-bottom:25px;}
.top-header h1{font-size:24px;font-weight:700;color:#3f51b5;letter-spacing:.05em;}
button.add{background:#4caf50;border:none;border-radius:12px;padding:12px 20px;font-weight:700;color:#fff;cursor:pointer;margin-bottom:20px;}
button.add:hover{background:#3a9e3a;}
.table-wrapper {
    position: relative;
    border-radius: 18px;
    overflow: hidden;

    /* Gradient border effect */
    border: 3px solid transparent;
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    background-image:
        linear-gradient(rgba(255,255,255,0.95), rgba(255,255,255,0.95)), /* loob */
        linear-gradient(135deg, #3b82f6, #a855f7, #ef4444); /* border gradient */

    box-shadow: 0 8px 25px rgba(0,0,0,.08);
}
table{width:100%;border-collapse:separate;border-spacing:0 8px;}
.table-wrapper table th,
.table-wrapper table td {
    text-align: center;       /* horizontal center */
    vertical-align: middle;   /* vertical center */
}
th,td{padding:16px 24px;font-weight:600;font-size:14px;}
th{background:#d7dbff;color:#4e56a5;text-transform:uppercase;letter-spacing:.08em;}
tr{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgb(0 0 0 / 5%);}
tr:hover{background:#e1e6ff;}
.status{padding:6px 14px;border-radius:20px;font-size:12px;font-weight:bold;color:#fff;}
.active{background:#22c55e;}
.pending{background:#fbbf24;}
.rejected{background:#ef4444;}
.actions{display:flex;gap:8px;align-items:center;}
.actions form{margin:0;}
button.edit{background:#3f51b5;color:#fff;border:none;border-radius:10px;padding:6px 12px;font-size:12px;cursor:pointer;}
button.delete{background:#ef4444;color:#fff;border:none;border-radius:10px;padding:6px 12px;font-size:12px;cursor:pointer;}
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);align-items:center;justify-content:center;}
.box{background:#fff;padding:28px;border-radius:18px;width:400px;}
input,select{width:100%;padding:12px 14px;border-radius:10px;border:1px solid #d0d3ff;margin-bottom:14px;}
button.submit{width:100%;background:#4f46e5;color:#fff;padding:12px;border-radius:10px;border:none;}
button.cancel{width:100%;background:#eee;padding:12px;border-radius:10px;border:none;}
@media(max-width:768px){.main{margin-left:0;padding:20px 16px;}}
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

<div class="main">

    <div class="top-header">
        <h1>USERS MANAGEMENT</h1>
        <div class="logo"><img src="arslogo.jpg" height="36"></div>
    </div>

    <button class="add" onclick="openModal('add')">➕ Add User</button>

    <div class="table-wrapper">
        <table>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Registered At</th>
                <th>Action</th>
            </tr>

            <?php while($row=mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['fullname'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['username'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['email_address'] ?? '—') ?></td>
                <td><?= htmlspecialchars(ucfirst($row['role'] ?? '')) ?></td>
                <td>
                    <span class="status <?= strtolower($row['status'] ?? '') ?>">
                        <?= htmlspecialchars(ucfirst($row['status'] ?? '')) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($row['created_at'] ?? 'now'))) ?></td>
                <td>
                    <div class="actions">
                        <button class="edit"
                            onclick='openModal("edit", <?= json_encode([
                                'id' => $row['id'] ?? 0,
                                'fullname' => $row['fullname'] ?? '',
                                'username' => $row['username'] ?? '',
                                'email_address' => $row['email_address'] ?? '',
                                'role' => $row['role'] ?? ''
                            ]) ?>)'>
                            Edit
                        </button>
                        <form method="POST">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?? 0 ?>">
                            <button type="submit" class="delete">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- MODAL -->
<div class="modal" id="modal">
    <div class="box">
        <h3 id="modal-title">Add User</h3>
        <form method="POST">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="user_id" id="user_id">

            <input type="text" name="fullname" id="fullname" placeholder="Full Name" required>
            <input type="text" name="username" id="username" placeholder="Username" required>
            <input type="email" name="email" id="email" placeholder="Email" required>

            <select name="role" id="role">
                <option value="admin">Admin</option>
                <option value="barber">Barber</option>
            </select>

            <input type="password" name="password" id="password" placeholder="Password">
            <button class="submit">Save</button>
            <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
const modal=document.getElementById('modal');
const formAction=document.getElementById('formAction');
const user_id=document.getElementById('user_id');
const fullname=document.getElementById('fullname');
const username=document.getElementById('username');
const email=document.getElementById('email');
const role=document.getElementById('role');
const password=document.getElementById('password');

function openModal(mode,data=null){
    modal.style.display='flex';
    if(mode==='add'){
        formAction.value='add';
        fullname.value='';
        username.value='';
        email.value='';
        role.value='admin';
        password.required=true;
    }else{
        formAction.value='edit';
        user_id.value=data.id ?? 0;
        fullname.value=data.fullname ?? '';
        username.value=data.username ?? '';
        email.value=data.email ?? '';
        role.value=data.role ?? '';
        password.required=false;
    }
}
function closeModal(){ modal.style.display='none'; }
</script>

</body>
</html>
