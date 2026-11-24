<?php
// =====================================================
//  book.php - Booking form
//  Only logged-in guests can open this page.
//  The form is sent to process_booking.php (we build that in the back-end step).
//  JavaScript (js/booking.js) shows the number of nights and total price live.
// =====================================================
require_once 'db.php';

// ---------- 1. Login check ----------
// If there is no user_id in the session, the person is not logged in.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to book a room.";
    header("Location: login.php");     // send the visitor to the login page
    exit();                            // stop running the rest of this page
}

$page_title = "Book a Room - Mirissa Sands Villa";
$extra_script = "js/booking.js";

// ---------- 2. Get the logged-in guest's details (to show on the form) ----------
$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT name, email, contact_number FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);      // "i" = integer
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);

// If the account no longer exists, log the person out
if (!$user) {
    header("Location: logout.php");
    exit();
}

// ---------- 3. Get all available rooms for the drop-down list ----------
$rooms_result = mysqli_query($conn, "SELECT * FROM rooms WHERE room_status = 'available' ORDER BY price_per_night");

// ---------- 4. Values that may come from the Rooms page (in the URL) ----------
$selected_room = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$check_in      = isset($_GET['check_in'])  ? htmlspecialchars($_GET['check_in'])  : "";
$check_out     = isset($_GET['check_out']) ? htmlspecialchars($_GET['check_out']) : "";
$guests        = isset($_GET['guests']) && ctype_digit($_GET['guests']) ? (int)$_GET['guests'] : 1;

include 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1 class="mb-0">Book Your Stay</h1>
        <p class="mb-0">Fill in your dates and we will calculate the total for you.</p>
    </div>
</section>

<div class="container my-4">
    <div class="row g-4">

        <!-- ============ BOOKING FORM ============ -->
        <div class="col-lg-7">
            <form id="booking_form" method="post" action="process_booking.php" class="card card-body shadow-sm">

                <!-- JavaScript shows error messages here (hidden until needed) -->
                <div id="form_message" class="alert alert-danger d-none"></div>

                <h5 class="mb-3">Guest details</h5>
                <!-- These come from the guest's account, so they are read-only -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Full name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact number</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['contact_number']); ?>" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                </div>

                <h5 class="mb-3">Stay details</h5>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="room_id" class="form-label">Room</label>
                        <select class="form-select" id="room_id" name="room_id" required>
                            <?php while ($room = mysqli_fetch_assoc($rooms_result)) { ?>
                                <!-- data-price and data-max store extra info that JavaScript reads -->
                                <option value="<?php echo (int)$room['room_id']; ?>"
                                        data-price="<?php echo $room['price_per_night']; ?>"
                                        data-max="<?php echo (int)$room['max_guests']; ?>"
                                        <?php if ($room['room_id'] == $selected_room) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($room['room_name']); ?>
                                    - LKR <?php echo number_format($room['price_per_night'], 2); ?> / night
                                    (max <?php echo (int)$room['max_guests']; ?> guests)
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="check_in" class="form-label">Check-in</label>
                        <input type="date" class="form-control" id="check_in" name="check_in"
                               value="<?php echo $check_in; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="check_out" class="form-label">Check-out</label>
                        <input type="date" class="form-control" id="check_out" name="check_out"
                               value="<?php echo $check_out; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="guests" class="form-label">Guests</label>
                        <input type="number" class="form-control" id="guests" name="guests"
                               min="1" value="<?php echo $guests; ?>" required>
                    </div>
                </div>

                <!-- The total calculated by JavaScript. The server will RE-CALCULATE it,
                     so changing this value in the browser will not help a cheater. -->
                <input type="hidden" id="total_amount" name="total_amount" value="0">

                <button type="submit" class="btn btn-coral btn-lg mt-4">Confirm Booking Request</button>
            </form>
        </div>

        <!-- ============ LIVE PRICE SUMMARY ============ -->
        <div class="col-lg-5">
            <div class="total-box">
                <h5 class="mb-3">Price summary</h5>
                <p class="mb-1">Price per night: <strong id="price_display">LKR 0.00</strong></p>
                <p class="mb-1">Number of nights: <strong id="nights_display">0</strong></p>
                <hr>
                <p class="mb-0 fs-5">Total: <strong id="total_display">LKR 0.00</strong></p>
            </div>
            <p class="small text-muted mt-3">
                Your booking starts as <strong>Pending</strong>. The villa will confirm it shortly.
                You can cancel a Pending booking from the My Bookings page.
            </p>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
