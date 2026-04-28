<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="style.css">
<!DOCTYPE html>
<html>
<head>
    <title>Pradip Subedi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <a href="index.php" class="<?php echo ($current == 'index.php') ? 'active' : ''; ?>">Home</a>
    <a href="projects.php" class="<?php echo ($current == 'projects.php') ? 'active' : ''; ?>">Projects</a>
    <a href="experience.php" class="<?php echo ($current == 'experience.php') ? 'active' : ''; ?>">Experience</a>
    <a href="services.php" class="<?php echo ($current == 'services.php') ? 'active' : ''; ?>">Services</a>
    <a href="contact.php" class="<?php echo ($current == 'contact.php') ? 'active' : ''; ?>">Contact</a>
</nav>