<?php
define('APP_INIT', true);
require_once __DIR__ . '/includes/config.php';
include 'maintenance-check.php'; 
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

<meta name="description" content="Pradip Subedi, Electrical Engineer at Maulakalika Cable Car specializing in power systems and industrial maintenance.">

<meta name="keywords" content="Electrical Engineer, Cable Car, Power Systems, Nepal">

<meta property="og:title" content="Pradip Subedi">
<meta property="og:description" content="Electrical Engineer at Maulakalika Cable Car.">
<meta property="og:image" content="https://pradipsubedi1.com.np/assets/img/profile.jpg">
<meta property="og:url" content="https://pradipsubedi1.com.np/">
<meta property="og:type" content="website">

<style>
.navbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:10px 20px;
    background:#fff;
    border-bottom:1px solid #eee;
}

/* LEFT SIDE */
.nav-left{
    display:flex;
    align-items:center;
    gap:18px;
    overflow-x:auto;
    white-space:nowrap;
}

/* HERO ICON */
.hero-logo{
    display:flex;
    align-items:center;
    margin-right:10px;
}

.hero-logo img{
    width:42px;
    height:42px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #2563eb;
    transition:0.3s;
}

.hero-logo img:hover{
    transform:scale(1.05);
}

/* LINKS */
.nav-left a{
    text-decoration:none;
    color:#111;
    font-weight:500;
    white-space:nowrap;
}

.nav-left a.active{
    color:#2563eb;
}

/* RIGHT */
.nav-right{
    flex-shrink:0;
}

.admin-btn{
    background:#2563eb;
    color:#fff;
    padding:8px 14px;
    border-radius:8px;
    text-decoration:none;
    font-weight:500;
}
</style>

</head>


<nav class="navbar">

    <!-- LEFT -->
    <div class="nav-left">

        <!-- HERO PHOTO -->
        <a href="/pradip/index.php" class="hero-logo">
            <img src="/pradip/images/favicon.jpg" alt="Pradip Subedi">
        </a>

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

    <!-- RIGHT -->
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