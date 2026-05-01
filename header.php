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

<!-- NAVBAR -->
<nav class="navbar">

    <!-- LEFT MENU -->
    <div class="nav-left">

        <a href="/pradip/index.php" class="<?= ($current == 'index.php') ? 'active' : '' ?>">
            Home
        </a>

        <a href="/pradip/projects.php" class="<?= ($current == 'projects.php') ? 'active' : '' ?>">
            Projects
        </a>

        <a href="/pradip/experience.php" class="<?= ($current == 'experience.php') ? 'active' : '' ?>">
            Experience
        </a>

        <a href="/pradip/services.php" class="<?= ($current == 'services.php') ? 'active' : '' ?>">
            Services
        </a>

        <a href="/pradip/contact.php" class="<?= ($current == 'contact.php') ? 'active' : '' ?>">
            Contact
        </a>

    </div>

    <!-- RIGHT ADMIN -->
    <div class="nav-right">

        <?php if (!empty($_SESSION['admin'])): ?>
            <a href="/pradip/admin/index.php" class="admin-btn">
                Dashboard
            </a>
        <?php else: ?>
            <a href="/pradip/admin/login.php" class="admin-btn">
                Admin
            </a>
        <?php endif; ?>

    </div>

</nav>