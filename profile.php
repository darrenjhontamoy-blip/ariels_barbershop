<?php
session_start();
include '../config.php';

// CUSTOMER AUTH CHECK
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer'){
    header("Location: ../login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE id=$userId");
$user = mysqli_fetch_assoc($userQuery) ?? [];

$tab = $_GET['tab'] ?? 'profile';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile Settings</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
<style>
* { box-sizing: border-box; font-family: 'Poppins','Segoe UI',Arial,sans-serif; margin:0; padding:0; }
.main { margin-left: 280px; padding:40px 60px; background: linear-gradient(135deg, #0b3d91 0%, #1e3a8a 40%, #7f1d1d 70%, #c1121f 100%); min-height:100vh; color:white; }
.header-card { background: linear-gradient(135deg, #6366f1, #22c55e); color:#fff; border-radius:18px; padding:25px 30px; margin-bottom:30px; text-align:center; }
.header-card h1 { font-size:28px; font-weight:700; margin-bottom:6px; }
.header-card p { font-size:14px; opacity:0.9; }
.tabs { display:flex; margin-top:30px; border-bottom:1px solid rgba(255 255 255 / 0.3); gap:10px; }
.tabs a { padding:12px 25px; text-decoration:none; font-weight:600; color: rgba(255 255 255 / 0.7); border-radius:10px 10px 0 0; border-bottom:3px solid transparent; transition:.3s; display:flex; align-items:center; gap:6px; }
.tabs a:hover { background: rgba(255 255 255 / 0.1); color:white; }
.tabs a.active { color:white; background: linear-gradient(90deg, #4f46e5, #22c55e); border-color:transparent; }

/* Card with white background for personal info */
.card { background:#ffffff; color:#111827; border-radius:18px; padding:40px 50px; margin-top:30px; max-width:1100px; box-shadow:0 6px 18px rgba(0,0,0,.1); }
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:22px 30px; }
.field label { display:block; font-size:13px; margin-bottom:6px; color:#111827; }
.field input, .field select { width:100%; padding:14px; border-radius:10px; border:1px solid #ccc; font-size:14px; }
.save-btn { margin-top:35px; background: linear-gradient(90deg,#4f46e5,#22c55e); color:white; border:none; padding:14px 30px; border-radius:10px; font-weight:600; cursor:pointer; transition:.3s; }
.save-btn:hover { background: linear-gradient(90deg,#3b82f6,#16a34a); }

/* Avatar */
.avatar-section { display:flex; flex-direction:column; align-items:center; margin-bottom:40px; }
.avatar { width:120px; height:120px; border-radius:50%; object-fit:cover; border:2px solid #ccc; }
.avatar-actions { margin-top:15px; display:flex; gap:10px; }
.avatar-actions button { padding:8px 18px; border-radius:12px; border:1px solid #aaa; background:transparent; cursor:pointer; font-weight:600; transition:.3s; }
.avatar-actions button:hover { background:#f0f0f0; }

@media(max-width:900px){ .main{ margin-left:0; padding:20px; } .form-grid{ grid-template-columns:1fr; } }
</style>
</head>
<body>

<?php include 'sidebar_customer.php'; ?>

<div class="main">
<div class="header-card">
    <h1>Profile Settings</h1>
    <p>Manage your account information and preferences</p>
</div>

<div class="tabs">
    <a href="?tab=profile" class="<?= $tab==='profile'?'active':'' ?>">👤 Profile</a>
    <a href="?tab=security" class="<?= $tab==='security'?'active':'' ?>">🔒 Security</a>
</div>

<?php if($tab==='profile'): ?>
<div class="card">
    <div class="avatar-section">
        <img id="avatarPreview" src="../uploads/<?= !empty($user['profile_photo'])?$user['profile_photo']:'default.png' ?>" class="avatar" alt="Avatar">
        <div class="avatar-actions">
            <button onclick="document.getElementById('photo').click()">Change Photo</button>
            <button onclick="removePhoto()">Remove</button>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="update_profile.php">
        <input type="file" id="photo" name="photo" hidden>

        <div class="form-grid">
            <div class="field"><label>Full Name</label>
                <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']??'') ?>"></div>
            <div class="field"><label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email_address']??'') ?>"></div>
            <div class="field"><label>Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>"></div>
            <div class="field"><label>Date of Birth</label>
                <input type="text" id="dob" name="dob" value="<?= htmlspecialchars($user['dob']??'') ?>"></div>
        </div>

        <?php if($error): ?>
            <div style="color:red; margin-top:10px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <button class="save-btn">Save Profile</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr("#dob",{ dateFormat:"Y-m-d", altInput:true, altFormat:"F j, Y", maxDate:"today" });

function removePhoto(){
    if(confirm("Remove profile photo?")){
        window.location.href="remove_photo.php";
    }
}
</script>

<?php else: ?>
<div class="card">
    <h3>Security Settings</h3>
    <form method="POST" action="update_password.php" style="max-width:400px">
        <div class="field"><label>Current Password</label><input type="password" name="current"></div>
        <div class="field"><label>New Password</label><input type="password" name="new"></div>
        <div class="field"><label>Confirm Password</label><input type="password" name="confirm"></div>
        <button class="save-btn">Update Password</button>
    </form>
</div>
<?php endif; ?>

</div>
</body>
</html>
