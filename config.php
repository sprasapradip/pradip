<?php

// ==========================
// DATABASE CONNECTION
// ==========================
$conn = new mysqli("localhost", "root", "", "samikshya_portfolio2");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// ==========================
// PROFILE DATA
// ==========================
$profile = [
  "name"  => "Pradip Subedi",
  "title" => "Electrical Engineer",
  "bio"   => "Specialized in electrical installation, solar systems & industrial maintenance.",
  "cv"    => "cv.pdf"
];

// ==========================
// GLOBAL SETTINGS
// ==========================
date_default_timezone_set("Asia/Kathmandu");

?>