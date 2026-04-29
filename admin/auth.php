<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin'])){
    header("Location: /admin/login.php");
    exit;
}
?>