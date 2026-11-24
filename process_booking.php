<?php
// =====================================================
//  process_booking.php - Receives the form from book.php
//  This file shows NO page. It checks the data, saves the booking
//  and then sends the guest to another page.
// =====================================================
require_once 'db.php';

// 1. Only logged-in guests can book
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to book a room.";
    header("Location: login.php");
    exit();
}

// 2. This page only accepts a submitted form (POST), not someone typing the address
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: rooms.php");
    exit();
}

// 3. Read the values from the form
$user_id       = $_SESSION['user_id'];
$room_id       = (int)($_POST['room_id'] ?? 0);
$check_in      = trim($_POST['check_in'] ?? "");
$check_out     = trim($_POST['check_out'] ?? "");
$guests_text   = trim($_POST['guests'] ?? "");
$posted_total  = $_POST['total_amount'] ?? "";     // what JavaScript calculated

$errors = array();

// 4. Validate the dates
$today = date("Y-m-d");
if (!is_valid_date($check_in) || !is_valid_date($check_out)) {
    $errors[] = "Please choose valid check-in and check-out dates.";
} else {
    if ($check_in < $today) {
        $errors[] = "Check-in date cannot be in the past.";
    }
    if ($check_out <= $check_in) {
        $errors[] = "Check-out date must be after the check-in date.";
    }
}

// 5. Validate the number of guests
if (!ctype_digit($guests_text) || (int)$guests_text < 1) {
    $errors[] = "Please enter at least 1 guest.";
}
$guests = (int)$guests_text;

// 6. Get the room from the database (it must exist and be 'available')
$stmt = mysqli_prepare($conn,
    "SELECT room_name, price_per_night, max_guests FROM rooms
     WHERE room_id = ? AND room_status = 'available'");
mysqli_stmt_bind_param($stmt, "i", $room_id);
mysqli_stmt_execute($stmt);
$room_result = mysqli_stmt_get_result($stmt);
$room = mysqli_fetch_assoc($room_result);

if (!$room) {
    $errors[] = "The selected room is not available.";
} elseif ($guests > $room['max_guests']) {
    $errors[] = "This room allows a maximum of " . $room['max_guests'] . " guests.";
}

// 7. Calculate nights and total price ON THE SERVER, and check for double-booking
$total = 0;
$nights = 0;
if (count($errors) == 0) {
    // Number of nights = difference between the two dates, in days
    $nights = round((strtotime($check_out) - strtotime($check_in)) / 86400);
    $total = $nights * $room['price_per_night'];

    // Is there already a 'Confirmed' booking for this room that overlaps these dates?
    // Overlap rule:  old_check_in < new_check_out  AND  old_check_out > new_check_in
    $sql = "SELECT COUNT(*) AS total FROM bookings
            WHERE room_id = ? AND booking_status = 'Confirmed'
              AND check_in < ? AND check_out > ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $room_id, $check_out, $check_in);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($row['total'] > 0) {
        $errors[] = "Sorry, this room is already booked for some of these dates. Please choose different dates.";
    }
}

// 8. If anything is wrong: go back to the form with the messages
if (count($errors) > 0) {
    $_SESSION['error'] = implode(" ", $errors);
    header("Location: book.php?room_id=" . $room_id
         . "&check_in=" . urlencode($check_in)
         . "&check_out=" . urlencode($check_out)
         . "&guests=" . max(1, $guests));
    exit();
}

// 9. Everything is fine: save the booking as 'Pending' (the admin will confirm it later)
$sql = "INSERT INTO bookings (user_id, room_id, check_in, check_out, total_amount, booking_status)
        VALUES (?, ?, ?, ?, ?, 'Pending')";
$stmt = mysqli_prepare($conn, $sql);
// "iissd" = integer, integer, string, string, decimal number
mysqli_stmt_bind_param($stmt, "iissd", $user_id, $room_id, $check_in, $check_out, $total);

if (mysqli_stmt_execute($stmt)) {
    $booking_id = mysqli_insert_id($conn);     // the id MySQL gave to the new booking

    // If the browser's total was different, tell the guest the server's total was used
    $note = "";
    if (!is_numeric($posted_total) || abs($posted_total - $total) > 0.01) {
        $note = " (The total was re-calculated by the server.)";
    }

    $_SESSION['success'] = "Booking request #" . $booking_id . " sent for " . $room['room_name']
        . " (" . $nights . " night(s), total LKR " . number_format($total, 2) . "). "
        . "Status: Pending." . $note;
    header("Location: my_bookings.php");
} else {
    $_SESSION['error'] = "Sorry, we could not save your booking. Please try again.";
    header("Location: book.php?room_id=" . $room_id);
}
exit();
?>
