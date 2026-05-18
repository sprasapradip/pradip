<?php

// SECURITY CHECK
if (!isset($conn)) {
    die('Database connection not found.');
}

// GET MAINTENANCE MODE
$result = $conn->query(
    "SELECT setting_value 
     FROM settings 
     WHERE setting_key='maintenance_mode'"
);

$maintenance = $result->fetch_assoc();

$maintenance_mode = $maintenance['setting_value'] ?? 'off';

// CURRENT PAGE
$current_page = basename($_SERVER['PHP_SELF']);

// ALLOWED PAGES
$allowed_pages = [
    'admin-login.php',
    'admin-dashboard.php',
    'maintenance-settings.php',
    'maintenance.php'
];

// SHOW MAINTENANCE PAGE
if (
    $maintenance_mode === 'on' &&
    !in_array($current_page, $allowed_pages)
) {
    include 'maintenance.php';
    exit;
}

?>