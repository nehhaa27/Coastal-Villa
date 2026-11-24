<?php
// =====================================================
//  admin/bookings.php - View ALL bookings, then Confirm or Cancel them
// =====================================================
require_once '../db.php';
require_once '../includes/admin_check.php';
$page_title = "Manage Bookings - Admin";
$base_path = "../";

// ---------- Handle the Confirm / Cancel buttons (sent with POST) ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action     = $_POST['action'] ?? "";
    $booking_id = (int)($_POST['booking_id'] ?? 0);

    // Load the booking first
    $stmt = mysqli_prepare($conn,
        "SELECT room_id, check_in, check_out, booking_status FROM bookings WHERE booking_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $booking_id);
    mysqli_stmt_execute($stmt);
    $booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$booking) {
        $_SESSION['error'] = "Booking not found.";

    } elseif ($action == 'confirm') {
        if ($booking['booking_status'] != 'Pending') {
            $_SESSION['error'] = "Only Pending bookings can be confirmed.";
        } else {
            // Prevent double-booking: is there ANOTHER Confirmed booking for the same room
            // that overlaps these dates?  (booking_id != ? leaves out this booking itself)
            $sql = "SELECT COUNT(*) AS total FROM bookings
                    WHERE room_id = ? AND booking_status = 'Confirmed' AND booking_id != ?
                      AND check_in < ? AND check_out > ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iiss", $booking['room_id'], $booking_id,
                                   $booking['check_out'], $booking['check_in']);
            mysqli_stmt_execute($stmt);
            $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if ($row['total'] > 0) {
                $_SESSION['error'] = "Cannot confirm booking #" . $booking_id
                    . ": it overlaps with another Confirmed booking for the same room.";
            } else {
                $stmt = mysqli_prepare($conn,
                    "UPDATE bookings SET booking_status = 'Confirmed' WHERE booking_id = ? AND booking_status = 'Pending'");
                mysqli_stmt_bind_param($stmt, "i", $booking_id);
                mysqli_stmt_execute($stmt);
                $_SESSION['success'] = "Booking #" . $booking_id . " confirmed.";
            }
        }

    } elseif ($action == 'cancel') {
        if ($booking['booking_status'] == 'Cancelled') {
            $_SESSION['error'] = "That booking is already cancelled.";
        } else {
            $stmt = mysqli_prepare($conn,
                "UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $booking_id);
            mysqli_stmt_execute($stmt);
            $_SESSION['success'] = "Booking #" . $booking_id . " cancelled.";
        }
    }

    header("Location: bookings.php");
    exit();
}

// ---------- Get the bookings (optionally filtered by status) ----------
$filter = $_GET['status'] ?? "";
$allowed_filters = array("Pending", "Confirmed", "Cancelled");

// JOIN pulls in the guest's details (users table) and the room name (rooms table)
$sql = "SELECT b.booking_id, b.check_in, b.check_out, b.total_amount, b.booking_status, b.booking_date,
               u.name AS guest_name, u.email, u.contact_number, r.room_name
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN rooms r ON b.room_id = r.room_id";

if (in_array($filter, $allowed_filters)) {
    // Filter chosen: add a WHERE part (with a ? placeholder for safety)
    $stmt = mysqli_prepare($conn, $sql . " WHERE b.booking_status = ? ORDER BY b.booking_date DESC");
    mysqli_stmt_bind_param($stmt, "s", $filter);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $filter = "";
    $result = mysqli_query($conn, $sql . " ORDER BY b.booking_date DESC");
}

include '../includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1 class="mb-0">Manage Bookings</h1>
        <p class="mb-0">Confirm or cancel guest bookings.</p>
    </div>
</section>

<div class="container my-4">
    <?php include 'admin_nav.php'; ?>

    <!-- Filter buttons -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="bookings.php" class="btn btn-sm <?php echo $filter == '' ? 'btn-dark' : 'btn-outline-dark'; ?>">All</a>
        <a href="bookings.php?status=Pending" class="btn btn-sm <?php echo $filter == 'Pending' ? 'btn-dark' : 'btn-outline-dark'; ?>">Pending</a>
        <a href="bookings.php?status=Confirmed" class="btn btn-sm <?php echo $filter == 'Confirmed' ? 'btn-dark' : 'btn-outline-dark'; ?>">Confirmed</a>
        <a href="bookings.php?status=Cancelled" class="btn btn-sm <?php echo $filter == 'Cancelled' ? 'btn-dark' : 'btn-outline-dark'; ?>">Cancelled</a>
    </div>

    <?php if (mysqli_num_rows($result) == 0) { ?>
        <div class="alert alert-info">No bookings found.</div>
    <?php } else { ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Nights</th>
                    <th>Total (LKR)</th>
                    <th>Status</th>
                    <th>Booked on</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($b = mysqli_fetch_assoc($result)) {
                $nights = round((strtotime($b['check_out']) - strtotime($b['check_in'])) / 86400);
                $status = $b['booking_status'];
                if ($status == 'Confirmed') {
                    $badge = 'bg-success';
                } elseif ($status == 'Pending') {
                    $badge = 'bg-warning text-dark';
                } else {
                    $badge = 'bg-secondary';
                }
            ?>
                <tr>
                    <td>#<?php echo (int)$b['booking_id']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($b['guest_name']); ?><br>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($b['email']); ?><br>
                            <?php echo htmlspecialchars($b['contact_number']); ?>
                        </small>
                    </td>
                    <td><?php echo htmlspecialchars($b['room_name']); ?></td>
                    <td><?php echo date("d M Y", strtotime($b['check_in'])); ?></td>
                    <td><?php echo date("d M Y", strtotime($b['check_out'])); ?></td>
                    <td><?php echo $nights; ?></td>
                    <td><?php echo number_format($b['total_amount'], 2); ?></td>
                    <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                    <td><?php echo date("d M Y", strtotime($b['booking_date'])); ?></td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <?php if ($status == 'Pending') { ?>
                                <form method="post" action="bookings.php">
                                    <input type="hidden" name="action" value="confirm">
                                    <input type="hidden" name="booking_id" value="<?php echo (int)$b['booking_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Confirm</button>
                                </form>
                            <?php } ?>
                            <?php if ($status != 'Cancelled') { ?>
                                <form method="post" action="bookings.php"
                                      onsubmit="return confirm('Cancel this booking?');">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="booking_id" value="<?php echo (int)$b['booking_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                </form>
                            <?php } else { ?>
                                <span class="text-muted">-</span>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</div>

<?php include '../includes/footer.php'; ?>
