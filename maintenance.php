<?php
include 'config.php';

// SAFE DB CHECK
$message = "Website under maintenance";

if (isset($conn)) {
    $result = $conn->query(
        "SELECT setting_value FROM settings WHERE setting_key='maintenance_notice' LIMIT 1"
    );

    if ($result && $row = $result->fetch_assoc()) {
        $message = $row['setting_value'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Maintenance Mode</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#050816;
    color:#fff;
    font-family:Arial,sans-serif;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}

.maintenance-container{
    width:90%;
    max-width:650px;
    background:rgba(255,255,255,0.04);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:30px;
    padding:50px 40px;
    text-align:center;
    backdrop-filter:blur(10px);
    box-shadow:
        0 0 40px rgba(56,189,248,0.15),
        0 0 100px rgba(56,189,248,0.05);
}

.icon{
    font-size:70px;
    margin-bottom:20px;
}

h1{
    font-size:42px;
    margin-bottom:20px;
}

p{
    color:rgba(255,255,255,0.75);
    line-height:1.9;
    font-size:16px;
}

.notice-box{
    margin-top:30px;
    padding:20px;
    background:rgba(56,189,248,0.08);
    border-radius:18px;
    border:1px solid rgba(56,189,248,0.15);
}

.version{
    margin-top:20px;
    font-size:12px;
    color:rgba(255,255,255,0.4);
}
</style>

</head>

<body>

<div class="maintenance-container">

    <div class="icon">⚙️</div>

    <h1>Maintenance Mode</h1>

    <p>Our website is currently undergoing scheduled maintenance.</p>

    <div class="notice-box">
        <?php echo htmlspecialchars($message); ?>
    </div>

    <div class="version">
        Please check back later
    </div>

</div>

</body>
</html>