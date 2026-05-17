<?php
session_start();

include '../config.php';

$msg = "";

// Security
if (!isset($_SESSION['login_attempt'])) {
    $_SESSION['login_attempt'] = 0;
}

if (!isset($_SESSION['last_attempt'])) {
    $_SESSION['last_attempt'] = time();
}

// Generate CSRF token
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

if(isset($_POST['login'])){

    // CSRF Check
    if($_POST['token'] !== $_SESSION['token']){
        $msg = "Invalid request.";
    }

    // Brute Force Protection
    elseif($_SESSION['login_attempt'] >= 5){

        $remaining = 30 - (time() - $_SESSION['last_attempt']);

        if($remaining > 0){
            $msg = "Too many attempts. Try again in {$remaining} sec.";
        } else {
            $_SESSION['login_attempt'] = 0;
        }
    }

    else {

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $stmt = $conn->prepare("SELECT * FROM admins WHERE username=? LIMIT 2");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows > 0){

            $admin = $result->fetch_assoc();

            if(password_verify($password, $admin['password'])){

                session_regenerate_id(true);

                $_SESSION['admin'] = true;
                $_SESSION['admin_username'] = $admin['username'];

                header("Location: index.php");
                exit;

            } else {

                $_SESSION['login_attempt']++;
                $_SESSION['last_attempt'] = time();

                $msg = "Invalid username or password.";
            }

        } else {

            $_SESSION['login_attempt']++;
            $_SESSION['last_attempt'] = time();

            $msg = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Login</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial, sans-serif;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:
    linear-gradient(135deg,#020617,#0f172a);
}

.login-box{
    width:100%;
    max-width:400px;
    background:rgba(255,255,255,0.06);
    backdrop-filter:blur(12px);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:18px;
    padding:30px;
    color:white;
    box-shadow:0 10px 40px rgba(0,0,0,0.4);
}

.login-box h2{
    text-align:center;
    margin-bottom:25px;
}

.input-group{
    margin-bottom:18px;
}

.input-group label{
    display:block;
    margin-bottom:8px;
    font-size:14px;
}

.input-group input{
    width:100%;
    padding:14px;
    border:none;
    outline:none;
    border-radius:10px;
    background:rgba(255,255,255,0.08);
    color:white;
}

.input-group input::placeholder{
    color:#94a3b8;
}

.btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:#2563eb;
    color:white;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

.btn:hover{
    background:#1d4ed8;
}

.error{
    background:rgba(255,0,0,0.1);
    color:#ffb4b4;
    padding:10px;
    border-radius:10px;
    margin-bottom:15px;
    text-align:center;
}

.home-btn{
    margin-top:12px;
    display:block;
    text-align:center;
    text-decoration:none;
    color:#cbd5e1;
}

.home-btn:hover{
    color:white;
}

</style>

</head>
<body>

<div class="login-box">

    <h2>Admin Login</h2>

    <?php if($msg!=""): ?>
        <div class="error">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">

        <div class="input-group">
            <label>Username</label>
            <input 
                type="text" 
                name="username" 
                placeholder="Enter username"
                required
            >
        </div>

        <div class="input-group">
            <label>Password</label>
            <input 
                type="password" 
                name="password" 
                placeholder="Enter password"
                required
            >
        </div>

        <button class="btn" name="login">
            Login
        </button>

    </form>

    <a href="../index.php" class="home-btn">
        ← Go to Homepage
    </a>

</div>

</body>
</html>