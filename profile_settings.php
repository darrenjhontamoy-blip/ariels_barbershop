<?php
session_start();
include '../config.php';

// CUSTOMER ONLY
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
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
<meta charset="UTF-8" />
<title>Profile Settings</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />

<style>
/* Reset */
* {
    box-sizing: border-box;
    font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
    margin: 0;
    padding: 0;
}
/* ===== PASSWORD TOGGLE STYLE ===== */
.password-box {
    position: relative;
}

.password-box input {
    width: 100%;
    padding-right: 45px; /* space for eye icon */
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 16px;
    color: #ccc;
}

.toggle-password:hover {
    color: #fff;
}
/* Body gradient background */
body {
    background: linear-gradient(135deg, #0b3d91 0%, #1e3a8a 40%, #7f1d1d 70%, #c1121f 100%);
    min-height: 100vh;
    color: white;
}

/* Main container */
.main {
    margin-left: 280px;
    padding: 40px 60px;
    min-height: 100vh;
}

/* Header card */
.header-card {
    background: linear-gradient(135deg, #6366f1, #22c55e);
    color: #fff;
    border-radius: 18px;
    padding: 25px 30px;
    margin-bottom: 30px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
    text-align: center;
}
.header-card h1 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 6px;
}
.header-card p {
    font-size: 14px;
    opacity: 0.9;
}

/* Tabs */
.tabs {
    display: flex;
    margin-top: 30px;
    border-bottom: 1px solid rgba(255 255 255 / 0.3);
    gap: 10px;
}
.tabs a {
    padding: 12px 25px;
    text-decoration: none;
    font-weight: 600;
    color: rgba(255 255 255 / 0.7);
    border-radius: 10px 10px 0 0;
    border-bottom: 3px solid transparent;
    transition: .3s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.tabs a:hover {
    background: rgba(255 255 255 / 0.1);
    color: white;
}
.tabs a.active {
    color: white;
    background: linear-gradient(90deg, #4f46e5, #22c55e);
}

/* ===== GLOSSY GLASS PROFILE CARD ===== */
.profile-card {
    position: relative;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-radius: 20px;
    padding: 40px 50px;
    margin-top: 30px;
    max-width: 1100px;
    color: white;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

/* Gradient Border */
.profile-card::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 20px;
    padding: 2px;
    background: linear-gradient(135deg, #3b82f6, #ef4444);
    -webkit-mask:
        linear-gradient(#000 0 0) content-box,
        linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}

/* Glossy Reflection */
.profile-card::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 40%;
    border-radius: 20px 20px 0 0;
    background: linear-gradient(to bottom, rgba(255,255,255,0.25), transparent);
    pointer-events: none;
}

/* SECTION TITLE WITH GRADIENT LINE */
.section-title {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 25px;
    padding-bottom: 12px;
    position: relative;
}

.section-title::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, #3b82f6, #ef4444);
}

/* Form grid */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px 30px;
}

/* Labels */
.field label {
    display: block;
    font-size: 13px;
    margin-bottom: 6px;
    color: rgba(255,255,255,0.9);
}

/* Inputs */
.field input, .field select {
    width: 100%;
    padding: 14px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.3);
    font-size: 14px;
    background: rgba(255,255,255,0.12);
    color: white;
    transition: 0.3s;
}

.field input::placeholder {
    color: rgba(255,255,255,0.6);
}

.field input:focus, .field select:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 10px rgba(59,130,246,0.7);
}

/* Buttons */
.save-btn {
    margin-top: 35px;
    background: linear-gradient(90deg, #4f46e5, #22c55e);
    color: white;
    border: none;
    padding: 14px 30px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}
.save-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.save-btn:hover:not(:disabled) {
    background: linear-gradient(90deg, #3b82f6, #16a34a);
}

/* Avatar */
.avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 40px;
}
.avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.5);
}

/* Responsive */
@media (max-width: 900px) {
    .main {
        margin-left: 0;
        padding: 20px;
    }
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>


</head>
<body>

<?php include 'sidebar_customer.php'; ?>

<div class="main">

<div class="header-card">
    <h1>PROFILE SETTINGS</h1>
    <p>Manage your account information and preferences</p>
</div>

<div class="tabs">
    <a href="?tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>">👤 Profile</a>
    <a href="?tab=security" class="<?= $tab === 'security' ? 'active' : '' ?>">🔒 Security</a>
</div>

<?php if ($tab === 'profile'): ?>

<div class="profile-card">
    <div class="section-title">Personal Information</div>

    <div class="avatar-section">
        <img id="avatarPreview" src="../uploads/<?= !empty($user['profile_photo']) ? htmlspecialchars($user['profile_photo']) : 'default.png' ?>"
             class="avatar" alt="User Avatar">
        <div class="avatar-actions">
            <button type="button" onclick="document.getElementById('photo').click()">Change Photo</button>
            <button type="button" onclick="removePhoto()">Remove</button>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="update_profile.php">
        <input type="file" name="photo" id="photo" hidden accept="image/*">

        <div class="form-grid">
            <div class="field">
                <label>Full Name</label>
                <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" />
            </div>

            <div class="field">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email_address'] ?? '') ?>" />
            </div>

            <div class="field">
                <label>Phone Number</label>
                <input type="text" name="phone" id="phone" maxlength="11" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" />
                <div class="error-text" id="phoneError">
                    Phone number must start with 09 and be exactly 11 digits.
                </div>
            </div>

            <div class="field">
                <label>Date of Birth</label>
                <input type="text" id="dob" name="dob" placeholder="YYYY-MM-DD" value="<?= htmlspecialchars($user['dob'] ?? '') ?>" />
            </div>
        </div>

        <?php if ($error): ?>
            <div class="error-text show" style="margin-top:15px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <button class="save-btn" id="saveBtn">Save Profile</button>
    </form>
</div>

<?php else: ?>

<div class="profile-card" style="text-align:center;">
    <h2 style="font-size:28px; margin-bottom:30px;">Security</h2>
    <form method="POST" action="update_password.php" style="max-width:400px; margin:0 auto; text-align:left;">
        <div class="field password-box">
    <label>Current Password</label>
    <input type="password" id="currentPassword" name="current" />
    <span class="toggle-password" onclick="togglePassword('currentPassword')">👁</span>
</div>
        <div class="field password-box">
    <label>New Password</label>
    <input type="password" id="newPassword" name="new" />
    <span class="toggle-password" onclick="togglePassword('newPassword')">👁</span>
</div>
        <div class="field password-box">
    <label>Confirm Password</label>
    <input type="password" id="confirmPassword" name="confirm" />
    <span class="toggle-password" onclick="togglePassword('confirmPassword')">👁</span>
</div>
        <button class="save-btn">Update Password</button>
    </form>
</div>

<?php endif; ?>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
document.getElementById('photo').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

function removePhoto() {
    if (confirm("Are you sure you want to remove your profile photo?")) {
        document.getElementById('avatarPreview').src = '../uploads/default.png';
        window.location.href = 'remove_photo.php';
    }
}

// PHONE VALIDATION
const phoneInput = document.getElementById('phone');
const phoneError = document.getElementById('phoneError');
const saveBtn = document.getElementById('saveBtn');

function validatePhone() {
    phoneInput.value = phoneInput.value.replace(/\D/g, '');
    const valid = /^09\d{9}$/.test(phoneInput.value);

    if (phoneInput.value === '') {
        phoneError.style.display = 'none';
        saveBtn.disabled = false;
    } else if (!valid) {
        phoneError.style.display = 'block';
        saveBtn.disabled = true;
    } else {
        phoneError.style.display = 'none';
        saveBtn.disabled = false;
    }
}
phoneInput.addEventListener('input', validatePhone);
validatePhone();

// FLATPICKR
flatpickr("#dob", {
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "F j, Y",
    allowInput: true,
    maxDate: "today",
    defaultDate: "<?= htmlspecialchars($user['dob'] ?? '') ?>"
});
</script>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
</script>
</body>
</html>
