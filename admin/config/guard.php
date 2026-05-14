<?php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If admin not logged in
if (!isset($_SESSION['admin'])) {

    // Redirect to login page
    header("Location: /admin/login.php");
    exit();
}