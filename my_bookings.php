<?php
// =====================================================
//  my_bookings.php - A logged-in guest sees their own bookings
//  and can cancel the ones that are still 'Pending'.
//  The Cancel button is sent to cancel_booking.php (back-end step).
// =====================================================
require_once 'db.php';

// ---------- 1. Login check ----------
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to see your bookings.";
    header("Location: login.php");
    exit();
}

$page_title = "My Bookings - Mirissa Sands Villa";
$user_id = $_SESSION['user_id'];

// ---------- 2. Get ONLY this guest's bookings ----------
// JOIN connects each booking to its room, so we can show the room name.
// "WHERE b.user_id = ?" makes sure a guest can never see someone else's bookings.
$sql = "SELECT b.booking_id, b.check_in, b.check_out, b.total_amount,
               b.booking_status, b.booking_date, r.room_name
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$booking_count = mysqli_num_rows($result);

include 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1 class="mb-0">My Bookings</h1>
        <p class="mb-0">Check the status of your reservations.</p>
    </div>
</section>

<div class="container my-4">

    <?php if ($booking_count == 0) { ?>
        <!-- Guest has not booked anything yet -->
        <div class="alert alert-info">
            You have no bookings yet. <a href="rooms.php" class="alert-link">Browse our rooms</a> to make your first booking.
        </div>

    <?php } else { ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white">
                <thead class="table-light">
                    <tr>
                        <th>Booking #</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Nights</th>
                        <th>Total (LKR)</th>
                        <th>Status</th>
                        <th>Booked on</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($booking = mysqli_fetch_assoc($result)) {

                    // Number of nights = (check-out - check-in) in seconds / seconds in one day
                    $nights = round((strtotime($booking['check_out']) - strtotime($booking['check_in'])) / 86400);

                    // Choose a Bootstrap colour for the status badge
                    $status = $booking['booking_status'];
                    if ($status == 'Confirmed') {
                        $badge = 'bg-success';
                    } elseif ($status == 'Pending') {
                        $badge = 'bg-warning text-dark';
                    } else {
                        $badge = 'bg-secondary';      // Cancelled
                    }
                ?>
                    <tr>
                        <td>#<?php echo (int)$booking['booking_id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                        <td><?php echo date("d M Y", strtotime($booking['check_in'])); ?></td>
                        <td><?php echo date("d M Y", strtotime($booking['check_out'])); ?></td>
                        <td><?php echo $nights; ?></td>
                        <td><?php echo number_format($booking['total_amount'], 2); ?></td>
                        <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                        <td><?php echo date("d M Y", strtotime($booking['booking_date'])); ?></td>
                        <td>
                            <?php if ($status == 'Pending') { ?>
                                <!-- Only Pending bookings can be cancelled.
                                     onsubmit asks "Are you sure?" before sending. -->
                                <form method="post" action="cancel_booking.php"
                                      onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_id" value="<?php echo (int)$booking['booking_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                </form>
                            <?php } else { ?>
                                <span class="text-muted">-</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } // end of while loop ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

</div>

<?php include 'includes/footer.php'; ?>
