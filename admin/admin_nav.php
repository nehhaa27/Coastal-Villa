<?php
// admin/admin_nav.php - small menu shown at the top of every admin page
$admin_page = basename($_SERVER['PHP_SELF']);    // which admin page is open?
?>
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link <?php if ($admin_page == 'index.php') echo 'active'; ?>" href="index.php">Dashboard</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php if ($admin_page == 'rooms.php' || $admin_page == 'room_form.php') echo 'active'; ?>" href="rooms.php">Manage Rooms</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php if ($admin_page == 'bookings.php') echo 'active'; ?>" href="bookings.php">Manage Bookings</a>
    </li>
</ul>
