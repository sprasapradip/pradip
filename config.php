<?php

// ==========================
// DATABASE CONNECTION
// ==========================
$conn = new mysqli("localhost", "root", "", "portfolio");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// ==========================
// PROFILE DATA
// ==========================
$profile = [
  "name"  => "Sprasapradip",
  "title" => "Electrical Engineer",
  "bio"   => "Specialized in electrical installation, solar systems & industrial maintenance.",
  "cv"    => "cv.pdf"
];

// ==========================
// GLOBAL SETTINGS
// ==========================
date_default_timezone_set("Asia/Kathmandu");

?>