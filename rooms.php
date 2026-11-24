<?php
// =====================================================
//  rooms.php - Shows the rooms and lets the guest search
//  by check-in date, check-out date and number of guests.
// =====================================================
require_once 'db.php';
$page_title = "Rooms - Mirissa Sands Villa";
$extra_script = "js/rooms.js";     // small JS file that blocks past dates in the search form

// (The helper function is_valid_date() lives in db.php, so every page can use it.)

// ---------- 1. Read the search form values ----------
// The form uses method="get", so the values arrive in the URL (rooms.php?check_in=...)
$searching = isset($_GET['search']);           // true only if the Search button was pressed
$check_in  = isset($_GET['check_in'])  ? trim($_GET['check_in'])  : "";
$check_out = isset($_GET['check_out']) ? trim($_GET['check_out']) : "";
$guests    = isset($_GET['guests'])    ? trim($_GET['guests'])    : "";
$search_error = "";

// ---------- 2. Validate the search on the SERVER ----------
// (JavaScript can be bypassed, so PHP must check again.)
if ($searching) {
    $today = date("Y-m-d");

    if (!is_valid_date($check_in) || !is_valid_date($check_out)) {
        $search_error = "Please choose a valid check-in and check-out date.";
    } elseif ($check_in < $today) {
        $search_error = "Check-in date cannot be in the past.";
    } elseif ($check_out <= $check_in) {
        $search_error = "Check-out date must be after the check-in date.";
    } elseif (!ctype_digit($guests) || (int)$guests < 1) {
        $search_error = "Please enter at least 1 guest.";
    }
}

// ---------- 3. Get the rooms from the database ----------
if ($searching && $search_error == "") {
    // SEARCH: show rooms that
    //   (a) are 'available' (the admin has not switched them off),
    //   (b) can hold enough guests, and
    //   (c) have NO 'Confirmed' booking that overlaps the chosen dates.
    //
    // Two stays overlap when:  old_check_in < new_check_out  AND  old_check_out > new_check_in
    // The "?" marks are placeholders - this is a PREPARED STATEMENT, which stops SQL injection.
    $sql = "SELECT * FROM rooms
            WHERE room_status = 'available'
              AND max_guests >= ?
              AND room_id NOT IN (
                    SELECT room_id FROM bookings
                    WHERE booking_status = 'Confirmed'
                      AND check_in < ?
                      AND check_out > ?
              )
            ORDER BY price_per_night";
    $stmt = mysqli_prepare($conn, $sql);
    $guests_number = (int)$guests;
    // "iss" = one integer, then two strings (dates)
    mysqli_stmt_bind_param($stmt, "iss", $guests_number, $check_out, $check_in);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    // NO SEARCH (or a bad search): show every available room.
    // No user input is used here, so a normal query is safe.
    $sql = "SELECT * FROM rooms WHERE room_status = 'available' ORDER BY price_per_night";
    $result = mysqli_query($conn, $sql);
}
$room_count = mysqli_num_rows($result);

// If the search was valid, we pass the dates on to the booking page through the URL
$booking_extra = "";
if ($searching && $search_error == "") {
    $booking_extra = "&check_in=" . urlencode($check_in)
                   . "&check_out=" . urlencode($check_out)
                   . "&guests=" . urlencode($guests);
}

include 'includes/header.php';
?>

<!-- ============ PAGE TITLE ============ -->
<section class="page-banner">
    <div class="container">
        <h1 class="mb-0">Our Rooms</h1>
        <p class="mb-0">Choose your room and check availability for your dates.</p>
    </div>
</section>

<div class="container my-4">

    <!-- ============ AVAILABILITY SEARCH FORM ============ -->
    <form method="get" action="rooms.php" class="card card-body shadow-sm mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="check_in" class="form-label">Check-in</label>
                <input type="date" class="form-control" id="check_in" name="check_in"
                       value="<?php echo htmlspecialchars($check_in); ?>" required>
            </div>
            <div class="col-md-3">
                <label for="check_out" class="form-label">Check-out</label>
                <input type="date" class="form-control" id="check_out" name="check_out"
                       value="<?php echo htmlspecialchars($check_out); ?>" required>
            </div>
            <div class="col-md-2">
                <label for="guests" class="form-label">Guests</label>
                <input type="number" class="form-control" id="guests" name="guests"
                       min="1" max="10" value="<?php echo $guests != '' ? htmlspecialchars($guests) : 1; ?>" required>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" name="search" value="1" class="btn btn-coral flex-fill">Search Rooms</button>
                <a href="rooms.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <!-- ============ SEARCH MESSAGES ============ -->
    <?php if ($search_error != "") { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($search_error); ?></div>
    <?php } elseif ($searching) { ?>
        <div class="alert alert-info">
            Showing <?php echo $room_count; ?> room(s) free from
            <strong><?php echo htmlspecialchars($check_in); ?></strong> to
            <strong><?php echo htmlspecialchars($check_out); ?></strong>
            for <strong><?php echo (int)$guests; ?></strong> guest(s).
        </div>
    <?php } ?>

    <!-- ============ ROOM CARDS ============ -->
    <?php if ($room_count == 0) { ?>
        <div class="alert alert-warning">
            Sorry, no rooms are available for these dates. Please try different dates.
        </div>
    <?php } ?>

    <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php
        // Loop: each time through the loop, $room holds ONE row from the database
        while ($room = mysqli_fetch_assoc($result)) {

            
           if ($room['room_id'] == 1) {
    $image = "images/ocean_view.jpg";
    } elseif ($room['room_id'] == 2) {
    $image = "images/family_room.jpg";
    } elseif ($room['room_id'] == 3) {
    $image = "images/beachside_cabana.jpg";
    } elseif ($room['room_id'] == 4) {
    $image = "images/twin_room.jpg";
    } else {
    $image = "images/default_room.svg";   
    }

            // The description in the database looks like: "About text. Facilities: a, b, c."
            // We split it at the word "Facilities:" to show the two parts separately.
            $parts = explode("Facilities:", $room['description']);
            $about = trim($parts[0]);
            $facilities = isset($parts[1]) ? trim($parts[1]) : "";

            // Turn "AC, Wi-Fi, hot water." into a list: ["AC", " Wi-Fi", " hot water"]
            // (used for the bullet list inside the "View Details" pop-up)
            $facility_list = array();
            if ($facilities != "") {
                $facility_list = explode(",", rtrim($facilities, "."));
            }
        ?>
        <div class="col">
            <div class="card room-card h-100">
                <img src="<?php echo $image; ?>" class="room-img card-img-top"
                     alt="<?php echo htmlspecialchars($room['room_name']); ?>">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title"><?php echo htmlspecialchars($room['room_name']); ?></h4>
                        <span class="badge bg-info text-dark"><?php echo htmlspecialchars($room['room_type']); ?></span>
                    </div>

                    <p class="card-text"><?php echo htmlspecialchars($about); ?></p>

                    <?php if ($facilities != "") { ?>
                        <p class="small text-muted mb-2">
                            <strong>Facilities:</strong> <?php echo htmlspecialchars($facilities); ?>
                        </p>
                    <?php } ?>

                    <p class="mb-3">👥 Up to <strong><?php echo (int)$room['max_guests']; ?></strong> guests</p>

                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="price-tag">
                            LKR <?php echo number_format($room['price_per_night'], 2); ?>
                            <small class="text-muted fs-6">/ night</small>
                        </span>
                        <!-- This button opens the pop-up window (modal) with full room details -->
                        <button type="button" class="btn btn-outline-secondary ms-auto me-2"
                                data-bs-toggle="modal"
                                data-bs-target="#roomModal<?php echo (int)$room['room_id']; ?>">View Details</button>
                        <a href="book.php?room_id=<?php echo (int)$room['room_id'] . htmlspecialchars($booking_extra); ?>"
                           class="btn btn-coral">Book</a>
                    </div>
                </div>
            </div>

            <!-- ===== "View Details" pop-up window (Bootstrap modal) for this room ===== -->
            <div class="modal fade" id="roomModal<?php echo (int)$room['room_id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?php echo htmlspecialchars($room['room_name']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <img src="<?php echo $image; ?>" class="img-fluid rounded mb-3"
                                 alt="<?php echo htmlspecialchars($room['room_name']); ?>">
                            <p><span class="badge bg-info text-dark"><?php echo htmlspecialchars($room['room_type']); ?></span></p>
                            <p><?php echo htmlspecialchars($about); ?></p>

                            <?php if (count($facility_list) > 0) { ?>
                                <h6>Facilities</h6>
                                <ul>
                                    <?php foreach ($facility_list as $item) { ?>
                                        <li><?php echo htmlspecialchars(trim($item)); ?></li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>

                            <p>👥 Maximum guests: <strong><?php echo (int)$room['max_guests']; ?></strong></p>
                            <p class="price-tag mb-0">
                                LKR <?php echo number_format($room['price_per_night'], 2); ?> / night
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="book.php?room_id=<?php echo (int)$room['room_id'] . htmlspecialchars($booking_extra); ?>"
                               class="btn btn-coral">Book this room</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php } // end of while loop ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
