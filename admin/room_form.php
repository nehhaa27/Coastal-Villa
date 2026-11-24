<?php
// =====================================================
//  admin/room_form.php - ONE form used for both:
//    Add room   -> room_form.php
//    Edit room  -> room_form.php?room_id=3
// =====================================================
require_once '../db.php';
require_once '../includes/admin_check.php';
$base_path = "../";

// Are we editing an existing room? (a room_id is in the URL)
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$editing = ($room_id > 0);
$page_title = $editing ? "Edit Room - Admin" : "Add Room - Admin";

// Empty values for the "Add" form
$room = array(
    "room_name" => "",
    "room_type" => "",
    "description" => "",
    "price_per_night" => "",
    "max_guests" => 2,
    "room_status" => "available"
);

// For "Edit": load the room from the database to fill the form
if ($editing) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM rooms WHERE room_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $room_id);
    mysqli_stmt_execute($stmt);
    $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$found) {
        $_SESSION['error'] = "That room was not found.";
        header("Location: rooms.php");
        exit();
    }
    $room = $found;
}

$errors = array();

// ---------- The form was submitted ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Put the submitted values back into $room (so the form keeps what was typed)
    $room['room_name']       = trim($_POST['room_name'] ?? "");
    $room['room_type']       = trim($_POST['room_type'] ?? "");
    $room['description']     = trim($_POST['description'] ?? "");
    $room['price_per_night'] = trim($_POST['price_per_night'] ?? "");
    $room['max_guests']      = trim($_POST['max_guests'] ?? "");
    $room['room_status']     = $_POST['room_status'] ?? "available";

    // Validation
    if ($room['room_name'] == "" || strlen($room['room_name']) > 100) {
        $errors[] = "Room name is required (maximum 100 characters).";
    }
    if ($room['room_type'] == "" || strlen($room['room_type']) > 50) {
        $errors[] = "Room type is required (maximum 50 characters).";
    }
    if ($room['description'] == "") {
        $errors[] = "Please write a description (you can add 'Facilities: ...' at the end).";
    }
    if (!is_numeric($room['price_per_night']) || $room['price_per_night'] <= 0 || $room['price_per_night'] > 1000000) {
        $errors[] = "Price per night must be a number greater than 0.";
    }
    if (!ctype_digit($room['max_guests']) || $room['max_guests'] < 1 || $room['max_guests'] > 20) {
        $errors[] = "Maximum guests must be a whole number from 1 to 20.";
    }
    if ($room['room_status'] != 'available' && $room['room_status'] != 'unavailable') {
        $errors[] = "Please choose a valid status.";
    }

    // No errors: save
    if (count($errors) == 0) {
        $price = (float)$room['price_per_night'];
        $max   = (int)$room['max_guests'];

        if ($editing) {
            $sql = "UPDATE rooms SET room_name = ?, room_type = ?, description = ?,
                           price_per_night = ?, max_guests = ?, room_status = ?
                    WHERE room_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            // s s s d i s i = string, string, string, decimal, integer, string, integer
            mysqli_stmt_bind_param($stmt, "sssdisi", $room['room_name'], $room['room_type'],
                $room['description'], $price, $max, $room['room_status'], $room_id);
            $message = "Room updated successfully.";
        } else {
            $sql = "INSERT INTO rooms (room_name, room_type, description, price_per_night, max_guests, room_status)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssdis", $room['room_name'], $room['room_type'],
                $room['description'], $price, $max, $room['room_status']);
            $message = "New room added successfully.";
        }

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = $message;
            header("Location: rooms.php");
            exit();
        } else {
            $errors[] = "Could not save the room. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1 class="mb-0"><?php echo $editing ? "Edit Room" : "Add New Room"; ?></h1>
    </div>
</section>

<div class="container my-4">
    <?php include 'admin_nav.php'; ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- No action attribute = the form is sent back to this same page -->
            <form method="post" class="card card-body shadow-sm">

                <?php if (count($errors) > 0) { ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error) { ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="room_name" class="form-label">Room name</label>
                        <input type="text" class="form-control" id="room_name" name="room_name"
                               value="<?php echo htmlspecialchars($room['room_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="room_type" class="form-label">Room type</label>
                        <input type="text" class="form-control" id="room_type" name="room_type"
                               placeholder="e.g. Deluxe, Family, Cabana"
                               value="<?php echo htmlspecialchars($room['room_type']); ?>" required>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($room['description']); ?></textarea>
                        <div class="form-text">Tip: end with "Facilities: AC, Wi-Fi, hot water." so the Rooms page can show the facilities separately.</div>
                    </div>
                    <div class="col-md-4">
                        <label for="price_per_night" class="form-label">Price per night (LKR)</label>
                        <input type="number" step="0.01" min="1" class="form-control" id="price_per_night" name="price_per_night"
                               value="<?php echo htmlspecialchars($room['price_per_night']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="max_guests" class="form-label">Maximum guests</label>
                        <input type="number" min="1" max="20" class="form-control" id="max_guests" name="max_guests"
                               value="<?php echo htmlspecialchars($room['max_guests']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="room_status" class="form-label">Status</label>
                        <select class="form-select" id="room_status" name="room_status">
                            <option value="available" <?php if ($room['room_status'] == 'available') echo 'selected'; ?>>Available</option>
                            <option value="unavailable" <?php if ($room['room_status'] == 'unavailable') echo 'selected'; ?>>Unavailable</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-coral"><?php echo $editing ? "Save Changes" : "Add Room"; ?></button>
                    <a href="rooms.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
