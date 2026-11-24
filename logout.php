<?php
// =====================================================
//  logout.php - Logs the user out
// =====================================================
session_start();        // open the current session so we can end it
session_unset();        // remove all stored values (user_id, name, role)
session_destroy();      // destroy the session itself

// Go to the login page (it shows a "You have been logged out" message)
header("Location: login.php?logged_out=1");
exit();
?>
