<?php
// ==========================
// BLOCK DIRECT ACCESS
// ==========================
if(!defined('APP_INIT')){
    exit('Direct access not allowed');
}

// ==========================
// ENVIRONMENT
// ==========================
define('ENV', 'local'); // change to 'production' on live

// ==========================
// ERROR HANDLING
// ==========================
if(ENV === 'production'){
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// ==========================
// DATABASE CONFIG
// ==========================
$db_config = [
     'local' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'samikshya_portfolio2'
    ],
    'production' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'samikshya_portfolio2'
    ]
];

$db = $db_config[ENV];

// ==========================
// DATABASE CONNECTION
// ==========================
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli(
    $db['host'],
    $db['user'],
    $db['pass'],
    $db['name']
);

if($conn->connect_error){
    if(ENV === 'production'){
        error_log("DB Error: ".$conn->connect_error);
        exit("Database connection error");
    } else {
        exit("DB Error: ".$conn->connect_error);
    }
}

// Prevent encoding issues & injection edge cases
$conn->set_charset("utf8mb4");

// ==========================
// SESSION SECURITY
// ==========================
if(session_status() === PHP_SESSION_NONE){

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false, // set TRUE when using HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

// ==========================
// SECURITY HELPERS
// ==========================
function clean($data){
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// CSRF token generator
if(empty($_SESSION['csrf'])){
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// ==========================
// GLOBAL PROFILE
// ==========================
$profile = [
    "name"  => "Pradip Subedi",
    "title" => "Electrical Engineer",
    "bio"   => "Cable Car Electrical Engineer | Power Systems | Maintenance",
    "cv"    => "cv.pdf"
];

// ==========================
// TIMEZONE
// ==========================
date_default_timezone_set("Asia/Kathmandu");