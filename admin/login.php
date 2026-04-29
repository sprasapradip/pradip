<?php
session_start();

$msg = "";

if(isset($_POST['login'])){
    if($_POST['username']=="admin" && $_POST['password']=="1234"){
        $_SESSION['admin'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $msg = "Invalid login";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>
    <p style="color:red;"><?php echo $msg; ?></p>

    <form method="POST">
        <input name="username" placeholder="Username">
        <input name="password" type="password" placeholder="Password">
        <button class="btn" name="login">Login</button>
    </form>
</div>

</body>
</html>