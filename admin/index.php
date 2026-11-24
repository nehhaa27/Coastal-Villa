<?php
// =====================================================
//  admin/index.php - Admin dashboard (summary numbers)
// =====================================================
require_once '../db.php';
require_once '../includes/admin_check.php';     // only admins can pass this line
$page_title = "Admin Dashboard - Mirissa Sands Villa";
$base_path = "../";                              // we are one folder deeper than the main pages

//
function get_count($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// These queries have no user input, so normal mysqli_query() is safe here
$total_rooms     = get_count($conn, "SELECT COUNT(*) AS total FROM rooms");
$active_rooms    = get_count($conn, "SELECT COUNT(*) AS total FROM rooms WHERE room_status = 'available'");
$pending_count   = get_count($conn, "SELECT COUNT(*) AS total FROM bookings WHERE booking_status = 'Pending'");
$confirmed_count = get_count($conn, "SELECT COUNT(*) AS total FROM bookings WHERE booking_status = 'Confirmed'");
$guest_count     = get_count($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'guest'");

include '../includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1 class="mb-0">Admin Panel</h1>
        <p class="mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>
    </div>
</section>

<div class="container my-4">
    <?php include 'admin_nav.php'; ?>

    <div class="row row-cols-2 row-cols-md-5 g-3 text-center">
        <div class="col"><div class="card feature-card p-3"><div class="fs-2"><?php echo $total_rooms; ?></div>Total rooms</div></div>
        <div class="col"><div class="card feature-card p-3"><div class="fs-2"><?php echo $active_rooms; ?></div>Active rooms</div></div>
        <div class="col"><div class="card feature-card p-3"><div class="fs-2"><?php echo $pending_count; ?></div>Pending bookings</div></div>
        <div class="col"><div class="card feature-card p-3"><div class="fs-2"><?php echo $confirmed_count; ?></div>Confirmed bookings</div></div>
        <div class="col"><div class="card feature-card p-3"><div class="fs-2"><?php echo $guest_count; ?></div>Registered guests</div></div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <a href="bookings.php?status=Pending" class="btn btn-coral">Review pending bookings</a>
        <a href="room_form.php" class="btn btn-outline-secondary">Add a new room</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
