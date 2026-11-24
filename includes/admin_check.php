<?php
// =====================================================
//  includes/admin_check.php
//  Put this at the TOP of every admin page (right after db.php).
//  It stops anyone who is not a logged-in admin.
// =====================================================

// Not logged in at all -> go to the login page
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in as the admin to open that page.";
    header("Location: ../login.php");
    exit();
}

// Logged in, but only a normal guest -> go to the home page
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = "Access denied. That area is only for the villa admin.";
    header("Location: ../index.php");
    exit();
}
?>
