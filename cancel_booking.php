<?php
// =====================================================
//  cancel_booking.php - A guest cancels one of THEIR Pending bookings
//  The Cancel button in my_bookings.php sends the booking_id here.
// =====================================================
require_once 'db.php';

// 1. Must be logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in first.";
    header("Location: login.php");
    exit();
}

// 2. Must come from the form (POST)
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: my_bookings.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$booking_id = (int)($_POST['booking_id'] ?? 0);

// 3. Cancel the booking.
// The WHERE part does the security checks for us:
//   booking_id = ?                 -> the booking the guest clicked
//   user_id = ?                    -> it must belong to THIS guest (nobody can cancel other guests' bookings)
//   booking_status = 'Pending'     -> only Pending bookings can be cancelled by the guest
$sql = "UPDATE bookings SET booking_status = 'Cancelled'
        WHERE booking_id = ? AND user_id = ? AND booking_status = 'Pending'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
mysqli_stmt_execute($stmt);

// affected_rows tells us how many rows were changed: 1 = success, 0 = nothing matched
if (mysqli_stmt_affected_rows($stmt) == 1) {
    $_SESSION['success'] = "Booking #" . $booking_id . " has been cancelled.";
} else {
    $_SESSION['error'] = "This booking could not be cancelled. Only your own Pending bookings can be cancelled.";
}

header("Location: my_bookings.php");
exit();
?>
