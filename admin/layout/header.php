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

</head>
<body>
<div class="app">

    <!-- TOP BAR -->
    <header class="topbar">
        <div class="logo">My Admin</div>
        <div class="top-actions">
            <a href="/pradip/admin/logout.php">Logout</a>
        </div>
    </header>

    <div class="layout">

        <!-- SIDEBAR -->
        <aside class="sidebar">

            <a href="/pradip/admin/index.php"
               class="<?= ($current=='index.php')?'active':'' ?>">
               Dashboard
            </a>

            <a href="/pradip/admin/messages/index.php"
               class="<?= (strpos($_SERVER['REQUEST_URI'],'messages')!==false)?'active':'' ?>">
               Messages
            </a>

            <a href="/pradip/admin/projects/index.php"
               class="<?= (strpos($_SERVER['REQUEST_URI'],'projects')!==false)?'active':'' ?>">
               Projects
            </a>

        </aside>

        <!-- MAIN -->
        <main class="content">