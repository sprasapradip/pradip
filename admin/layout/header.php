<?php
if (!defined('APP_INIT')) {
    die("Direct access not allowed.");
}

$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>

<link rel="stylesheet" href="/pradip/admin/assets/admin.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>

<body>

<div class="app">

    <!-- =========================
         TOPBAR
    ========================= -->

    <header class="topbar">

        <!-- LEFT -->
        <div class="topbar-left">

            <!-- MOBILE MENU -->
            <button class="menu-toggle" id="menuToggle">
                ☰
            </button><div class="logo">
                Pradip Admin
            </div>
        </div>

        <!-- RIGHT -->
        <div class="top-actions">

            <a href="/pradip" target="_blank" class="visit-btn">
                Visit Site
            </a>

            <a href="/pradip/admin/logout.php" class="logout-btn">
                Logout
            </a>

        </div>

    </header>

    <!-- =========================
         LAYOUT
    ========================= -->

    <div class="layout">

        <!-- =========================
             SIDEBAR
        ========================= -->

        <aside class="sidebar" id="sidebar">

            <a href="/pradip/admin/index.php"
               class="<?= ($current=='index.php')?'active':'' ?>">
               🏠 Dashboard
            </a>

            <a href="/pradip/admin/messages/index.php"
               class="<?= (strpos($_SERVER['REQUEST_URI'],'messages')!==false)?'active':'' ?>">
               📩 Messages
            </a>

            <a href="/pradip/admin/projects/index.php"
               class="<?= (strpos($_SERVER['REQUEST_URI'],'projects')!==false)?'active':'' ?>">
               📁 Projects
            </a>

            <a href="/pradip/admin/blogs/index.php"
               class="<?= (strpos($_SERVER['REQUEST_URI'],'blogs')!==false)?'active':'' ?>">
               ✍️ Blogs
            </a>

            <a href="/pradip/admin/services/index.php"
               class="<?= (strpos($_SERVER['REQUEST_URI'],'services')!==false)?'active':'' ?>">
               ⚡ Services
            </a>

            <a href="/pradip/admin/experience/index.php"
               class="<?= (strpos($_SERVER['REQUEST_URI'],'experience')!==false)?'active':'' ?>">
               💼 Experience
            </a>

            <a href="/pradip/admin/maintenance-settings.php"
               class="<?= (strpos($_SERVER['REQUEST_URI'],'maintenance-settings')!==false)?'active':'' ?>">
               🛠 Settings
            </a>

            <a href="/pradip/admin/profile/edit.php"
               class="<?= (strpos($_SERVER['REQUEST_URI'],'profile')!==false)?'active':'' ?>">
               👤 CV Profile
            </a>

        </aside>

        <!-- =========================
             MAIN CONTENT
        ========================= -->

        <main class="content">