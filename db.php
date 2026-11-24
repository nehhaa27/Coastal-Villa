<?php
// =====================================================
//  db.php - Database connection file
//  Every other PHP page will include this file with:
//      require_once 'db.php';      (or '../db.php' inside admin/)
//  so we only write the connection code ONE time.
// =====================================================

// Connection settings for XAMPP's default MySQL
$db_host = "localhost";        // MySQL runs on your own computer
$db_user = "root";             // XAMPP's default username
$db_pass = "";                 // XAMPP's default password is empty
$db_name = "coastal_villa";    // The database created by db_structure.sql

// Use Sri Lankan time, so "today" in PHP matches the real date here
// (XAMPP's default timezone can be different).
date_default_timezone_set("Asia/Colombo");

// Try to connect using MySQLi (procedural style)
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// If the connection failed, stop the page and show a message
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Use UTF-8 so special characters are stored and shown correctly
mysqli_set_charset($conn, "utf8mb4");

// ---------- Helper function used by several pages ----------
// Is this text a real calendar date like 2026-12-05 ?
function is_valid_date($date) {
    $parts = explode("-", $date);              // "2026-12-05" -> ["2026","12","05"]
    if (count($parts) != 3) {
        return false;
    }
    // checkdate(month, day, year) is built into PHP
    return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}

// Start the session here once, so every page that includes db.php
// can use $_SESSION (needed for login). The "if" stops it starting twice.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
