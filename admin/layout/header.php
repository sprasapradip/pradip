<?php
if (!defined('APP_INIT')) {
    die("Direct access not allowed.");
}

if(!isset($admin)){
    $admin = $conn->query("SELECT * FROM admins LIMIT 1")->fetch_assoc();
}

$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>
<link rel="stylesheet" href="/pradip/admin/assets/css/header.css">
<link rel="stylesheet" href="/pradip/admin/assets/css/admin.css">
<link rel="stylesheet" href="/pradip/admin/assets/css/blogs.css">
<link rel="stylesheet" href="/pradip/admin/assets/css/messages.css">
<link rel="stylesheet" href="/pradip/admin/assets/css/navigation.css">
<link rel="stylesheet" href="/pradip/admin/assets/css/profile.css">
<link rel="stylesheet" href="/pradip/admin/assets/css/projects.css">
<link rel="stylesheet" href="/pradip/admin/assets/css/services.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

</head>

<body>

<div class="app">

<!-- TOPBAR -->
<header class="topbar">

    <div class="left">
        <button class="icon-btn" id="menuToggle">
            <i class="fa fa-bars"></i>
        </button>

        <div class="brand">Admin Dashboard</div>
    </div>


    <div class="search-box">
        <i class="fa fa-search"></i>
        <input type="text" id="adminSearch" placeholder="Search...">
    </div>

    <div class="right">

        <button class="icon-btn" id="darkToggle">🌙</button>

        <div class="dropdown">
            <button class="icon-btn">🔔</button>
            <div class="dropdown-menu">
                <p>New Message</p>
                <p>New Project</p>
                <p>System Alert</p>
            </div>
        </div>

        <!-- PROFILE -->
        <div class="profile" id="profileBox">

            <div class="avatar" id="avatarBtn">

                <?php if(!empty($admin['image'])): ?>
                    <img src="/pradip/uploads/<?= htmlspecialchars($admin['image']) ?>">
                <?php else: ?>
                    <?= strtoupper(substr($admin['username'] ?? 'A', 0, 1)) ?>
                <?php endif; ?>

            </div>

            <div class="profile-menu">

                <a href="/pradip/admin/profile/index.php">Profile</a>
                <a href="/pradip/admin/settings/site-settings.php">Settings</a>
                <a href="/pradip/admin/logout.php">Logout</a>

            </div>

        </div>

    </div>

</header>

<!-- LAYOUT -->
<div class="layout">

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

<div class="sidebar-header">MENU</div>

<nav class="nav">

<a class="<?= ($current=='index.php')?'active':'' ?>" href="/pradip/admin/index.php">
<i class="fa fa-gauge"></i> Dashboard
</a>

<a class="<?= strpos($_SERVER['REQUEST_URI'],'messages')!==false?'active':'' ?>" href="/pradip/admin/messages/index.php">
<i class="fa fa-envelope"></i> Messages
</a>

<a class="<?= strpos($_SERVER['REQUEST_URI'],'projects')!==false?'active':'' ?>" href="/pradip/admin/projects/index.php">
<i class="fa fa-folder"></i> Projects
</a>

<a class="<?= strpos($_SERVER['REQUEST_URI'],'blogs')!==false?'active':'' ?>" href="/pradip/admin/blogs/index.php">
<i class="fa fa-pen"></i> Blogs
</a>

<a class="<?= strpos($_SERVER['REQUEST_URI'],'services')!==false?'active':'' ?>" href="/pradip/admin/services/index.php">
<i class="fa fa-bolt"></i> Services
</a>

<a class="<?= strpos($_SERVER['REQUEST_URI'],'experience')!==false?'active':'' ?>" href="/pradip/admin/experience/index.php">
<i class="fa fa-briefcase"></i> Experience
</a>

<!-- SETTINGS GROUP -->
<!-- SETTINGS GROUP -->
<div class="nav-group" id="settingsGroup">

    <a href="javascript:void(0)" class="nav-parent" id="settingsToggle">
        <i class="fa fa-gear"></i> Settings
        <i class="fa fa-angle-down arrow"></i>
    </a>

    <div class="nav-child">

        <a href="/pradip/admin/settings/site-settings.php">
            Site Settings
        </a>

        <a href="/pradip/admin/maintenance-settings.php">
            Maintanance Setting
        </a>

        <a href="/pradip/admin/profile/index.php">
            Profile
        </a>

    </div>

</div>
</nav>

</aside>

<!-- CONTENT -->
<main class="content">
<script src="/pradip/admin/assets/js/header.js"></script>
