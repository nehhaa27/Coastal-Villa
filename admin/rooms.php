<?php
// =====================================================
//  admin/rooms.php - View all rooms, activate/deactivate or delete them
//  (Adding and editing is done in room_form.php)
// =====================================================
require_once '../db.php';
require_once '../includes/admin_check.php';
$page_title = "Manage Rooms - Admin";
$base_path = "../";

// ---------- Handle the buttons (they are sent with POST) ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action  = $_POST['action'] ?? "";
    $room_id = (int)($_POST['room_id'] ?? 0);

    if ($action == 'set_status' && $room_id > 0) {
        // Activate / Deactivate: only 'available' or 'unavailable' are allowed
        $new_status = $_POST['new_status'] ?? "";
        if ($new_status == 'available' || $new_status == 'unavailable') {
            $stmt = mysqli_prepare($conn, "UPDATE rooms SET room_status = ? WHERE room_id = ?");
            mysqli_stmt_bind_param($stmt, "si", $new_status, $room_id);
            mysqli_stmt_execute($stmt);
            $_SESSION['success'] = "Room status updated.";
        }

    } elseif ($action == 'delete' && $room_id > 0) {
        // A room that already has bookings must NOT be deleted
        // (it would break the booking history). Deactivate it instead.
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM bookings WHERE room_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $room_id);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($row['total'] > 0) {
            $_SESSION['error'] = "This room has bookings, so it cannot be deleted. Please deactivate it instead.";
        } else {
            $stmt = mysqli_prepare($conn, "DELETE FROM rooms WHERE room_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $room_id);
            mysqli_stmt_execute($stmt);
            $_SESSION['success'] = "Room deleted.";
        }
    }

    // Reload the page so refreshing the browser does not repeat the action
    header("Location: rooms.php");
    exit();
}

// ---------- Get ALL rooms (also the unavailable ones) ----------
$result = mysqli_query($conn, "SELECT * FROM rooms ORDER BY room_id");

include '../includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1 class="mb-0">Manage Rooms</h1>
        <p class="mb-0">Add, edit, deactivate or delete rooms.</p>
    </div>
</section>

<div class="container my-4">
    <?php include 'admin_nav.php'; ?>

    <a href="room_form.php" class="btn btn-coral mb-3">+ Add New Room</a>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Room name</th>
                    <th>Type</th>
                    <th>Price / night (LKR)</th>
                    <th>Max guests</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($room = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo (int)$room['room_id']; ?></td>
                    <td><?php echo htmlspecialchars($room['room_name']); ?></td>
                    <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                    <td><?php echo number_format($room['price_per_night'], 2); ?></td>
                    <td><?php echo (int)$room['max_guests']; ?></td>
                    <td>
                        <?php if ($room['room_status'] == 'available') { ?>
                            <span class="badge bg-success">Available</span>
                        <?php } else { ?>
                            <span class="badge bg-secondary">Unavailable</span>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="room_form.php?room_id=<?php echo (int)$room['room_id']; ?>"
                               class="btn btn-sm btn-outline-primary">Edit</a>

                            <!-- Activate / Deactivate -->
                            <form method="post" action="rooms.php">
                                <input type="hidden" name="action" value="set_status">
                                <input type="hidden" name="room_id" value="<?php echo (int)$room['room_id']; ?>">
                                <?php if ($room['room_status'] == 'available') { ?>
                                    <input type="hidden" name="new_status" value="unavailable">
                                    <button type="submit" class="btn btn-sm btn-outline-warning">Deactivate</button>
                                <?php } else { ?>
                                    <input type="hidden" name="new_status" value="available">
                                    <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                                <?php } ?>
                            </form>

                            <!-- Delete (asks "Are you sure?" first) -->
                            <form method="post" action="rooms.php"
                                  onsubmit="return confirm('Delete this room permanently?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="room_id" value="<?php echo (int)$room['room_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
