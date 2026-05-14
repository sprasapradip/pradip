<?php
define('APP_INIT', true);
require_once __DIR__ . '/includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Pradip Subedi</title>

<link rel="icon" type="image/png" href="/pradip/images/favicon.jpg">
<link rel="stylesheet" href="/pradip/style.css">

<!-- SEO -->
<meta name="description" content="Pradip Subedi, Electrical Engineer at Maulakalika Cable Car specializing in power systems and industrial maintenance.">
<meta name="keywords" content="Electrical Engineer, Cable Car, Power Systems, Nepal">

<!-- Open Graph -->
<meta property="og:title" content="Pradip Subedi">
<meta property="og:description" content="Electrical Engineer at Maulakalika Cable Car.">
<meta property="og:image" content="https://pradipsubedi1.com.np/assets/img/profile.jpg">
<meta property="og:url" content="https://pradipsubedi1.com.np/">
<meta property="og:type" content="website">

<!-- MOBILE NAV SCROLL FIX -->
<style>
    .nav-left {
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch; /* smooth scroll on iOS */
        scrollbar-width: none;             /* hide scrollbar on Firefox */
        -ms-overflow-style: none;          /* hide scrollbar on IE/Edge */
    }

    /* Hide scrollbar on Chrome & Safari */
    .nav-left::-webkit-scrollbar {
        display: none;
    }

    .nav-left a {
        white-space: nowrap; /* prevent each link from wrapping */
    }

    /* Keep Admin button always visible, never squished */
    .nav-right {
        flex-shrink: 0;
    }
</style>

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
        <a href="/pradip/blogs.php" class="<?= ($current == 'blogs.php') ? 'active' : '' ?>">
            Blogs
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