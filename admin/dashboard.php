<?php
include 'auth.php';          // handles session + security
include '../config.php';    // DB connection
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<h2>Admin Dashboard</h2>

<div class="grid">
    <a href="projects/index.php" class="card">Manage Projects</a>
    <a href="messages/index.php" class="card">View Messages</a>
    <a href="blog/index.php" class="card">Manage Blog</a>
</div>

<a href="logout.php" class="btn">Logout</a>

</body>
</html>