<?php
session_start();

$msg = "";

if(isset($_POST['login'])){
    if($_POST['username']=="operation" && $_POST['password']=="Operation@123"){
        $_SESSION['admin'] = true;
        header("Location: index.php");
        exit;
    } else {
        $msg = "Invalid login";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>

<link rel="stylesheet" href="../style.css">

<style>
/* =========================
   LOGIN PAGE DESIGN
========================= */

body{
    margin:0;
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.login-container{
    background:#fff;
    padding:30px;
    width:100%;
    max-width:400px;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

.login-container h2{
    text-align:center;
    margin-bottom:20px;
}

.login-container input{
    width:100%;
    padding:10px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:14px;
}

.login-container button{
    width:100%;
    padding:10px;
    border:none;
    border-radius:8px;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

.btn-login{
    background:#2a5298;
    color:white;
}

.btn-login:hover{
    background:#1e3c72;
}

.btn-home{
    background:#f1f1f1;
    margin-top:10px;
}

.btn-home:hover{
    background:#ddd;
}

/* Mobile Responsive */
@media(max-width:480px){
    .login-container{
        margin:20px;
        padding:20px;
    }
}
</style>

</head>
<body>

<div class="login-container">

    <h2>Admin Login</h2>

    <p style="color:red; text-align:center;">
        <?php echo $msg; ?>
    </p>

    <form method="POST">
        <input name="username" placeholder="Username" required>
        <input name="password" type="password" placeholder="Password" required>

        <button class="btn-login" name="login">Login</button>
    </form>

    <!-- Go to Homepage Button -->
    <a href="../index.php">
        <button type="button" class="btn-home">
            Go to Homepage
        </button>
    </a>

</div>

</body>
</html>