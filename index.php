<?php
session_start();

$conn = new mysqli("localhost", "root", "", "ariels_barbershop_db");

if ($conn->connect_error) {
    die("Database connection failed");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT users.user_id, users.password, roles.role_name 
                             FROM users 
                             JOIN roles ON users.role_id = roles.role_id
                             WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role_name'];

            header("Location: dashboard_owner.php");
            exit;
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ariel's Barbershop | Login</title>
</head>
<body>

<h2>Login</h2>

<?php if ($error): ?>
<p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>

<form method="POST">
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
</form>

<p>
    Don’t have an account?
    <a href="register.php">Register here</a>
</p>

</body>
</html>
