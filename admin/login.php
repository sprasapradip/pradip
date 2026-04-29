<?php
session_start();

$msg = "";

// Your credentials (change this)
$admin_user = "admin";
$admin_pass = "1234";

if(isset($_POST['login'])){
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if($user === $admin_user && $pass === $admin_pass){
        $_SESSION['admin'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $msg = "Invalid Login Credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>
    <p style="color:red;"><?php echo $msg; ?></p>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button class="btn" name="login">Login</button>
    </form>
</div>

</body>
</html>