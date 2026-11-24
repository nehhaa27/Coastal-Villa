<?php
// =====================================================
//  includes/header.php
//  The TOP part of every page: <head>, Bootstrap CSS,
//  the navigation bar and the green/red message boxes.
//  Every page does:   include 'includes/header.php';
//  so we write the menu only ONE time.
// =====================================================

// A page can set $page_title BEFORE including this file.
// If it did not, we use this default title.
if (!isset($page_title)) {
    $page_title = "Mirissa Sands Villa";
}

// $base_path is the "way back" to the main folder.
// Normal pages use "" ; pages inside admin/ will set it to "../"
if (!isset($base_path)) {
    $base_path = "";
}

// Which file is open right now? (used to highlight the active menu link)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Bootstrap 5 CSS from a CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Our own CSS (loaded after Bootstrap so it can change Bootstrap's look) -->
    <link href="<?php echo $base_path; ?>css/style.css" rel="stylesheet">
</head>
<body>

<!-- ============ NAVIGATION BAR ============ -->
<nav class="navbar navbar-expand-lg navbar-dark bg-ocean sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $base_path; ?>index.php">🌴 Mirissa Sands Villa</a>

        <!-- Button that appears on small screens to open the menu -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'index.php' && $base_path == "") echo 'active'; ?>"
                       href="<?php echo $base_path; ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'rooms.php' && $base_path == "") echo 'active'; ?>"
                       href="<?php echo $base_path; ?>rooms.php">Rooms</a>
                </li>

                <?php if (isset($_SESSION['user_id'])) { ?>
                    <!-- Menu for a LOGGED-IN user -->
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_page == 'my_bookings.php') echo 'active'; ?>"
                           href="<?php echo $base_path; ?>my_bookings.php">My Bookings</a>
                    </li>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
                        <!-- Only admins see this link -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_path; ?>admin/index.php">Admin Panel</a>
                        </li>
                    <?php } ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>logout.php">
                            Logout (<?php echo htmlspecialchars($_SESSION['name']); ?>)
                        </a>
                    </li>
                <?php } else { ?>
                    <!-- Menu for a VISITOR who is not logged in -->
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_page == 'login.php') echo 'active'; ?>"
                           href="<?php echo $base_path; ?>login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_page == 'register.php') echo 'active'; ?>"
                           href="<?php echo $base_path; ?>register.php">Register</a>
                    </li>
                <?php } ?>

                <li class="nav-item ms-lg-2">
                    <a class="btn btn-coral btn-sm" href="<?php echo $base_path; ?>rooms.php">Book Now</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php
// ============ FLASH MESSAGES ============
// Other pages can leave a one-time message in $_SESSION['success'] or
// $_SESSION['error'] (for example after a booking). We show it once
// here and then delete it so it does not appear again on refresh.
if (isset($_SESSION['success']) || isset($_SESSION['error'])) {
    echo '<div class="container mt-3">';
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    echo '</div>';
}
?>
