<?php
session_start();
include '../config.php';

/* ADMIN ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($fullname) || empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {

        /* CHECK USERNAME */
        $check = mysqli_query($conn,
            "SELECT id FROM users WHERE username = '$username'"
        );

        if (mysqli_num_rows($check) > 0) {
            $error = "Username already exists.";
        } else {

            /* HASH PASSWORD */
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            /* INSERT USER ACCOUNT */
            mysqli_query($conn,"
                INSERT INTO users (fullname, username, password, role)
                VALUES ('$fullname', '$username', '$hashed', 'barber')
            ");

            /* INSERT BARBER PROFILE */
            mysqli_query($conn,"
                INSERT INTO barbers (fullname, status)
                VALUES ('$fullname', 'active')
            ");

            $success = "Barber account created successfully!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Barber</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif}
body{margin:0;background:#f4f6f9}
.main{margin-left:240px;padding:30px}

.form-card{
    max-width:500px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:16px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
}
.form-card h1{
    margin-bottom:20px;
    text-align:center;
}
input{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border-radius:8px;
    border:1px solid #ccc;
}
button{
    width:100%;
    padding:12px;
    background:#4f46e5;
    border:none;
    color:#fff;
    border-radius:8px;
    cursor:pointer;
    font-size:16px;
}
.success{
    background:#22c55e;
    color:#fff;
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
    text-align:center;
}
.error{
    background:#ef4444;
    color:#fff;
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
    text-align:center;
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="form-card">
        <h1>Add Barber</h1>

        <?php if($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Create Barber Account</button>
        </form>
    </div>
</div>

</body>
</html>
