<?php
<<<<<<< HEAD
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}
=======
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin'])){
    header("Location:login.php");
    exit;
}
?>
>>>>>>> fe19f5faa741cfcbb315602c1db3bd7e772eac19
