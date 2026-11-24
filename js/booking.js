// =====================================================
//  js/booking.js
//  Used by book.php. It does three things:
//   1. Blocks past dates and impossible check-out dates.
//   2. Calculates the number of nights and the total price live.
//   3. Checks the form again just before it is sent.
//  (The server will check everything AGAIN in PHP - never trust the browser alone.)
// =====================================================

// ---------- Get the HTML elements we need (by their id) ----------
var roomSelect = document.getElementById("room_id");
var checkInInput = document.getElementById("check_in");
var checkOutInput = document.getElementById("check_out");
var guestsInput = document.getElementById("guests");
var totalHidden = document.getElementById("total_amount");
var priceDisplay = document.getElementById("price_display");
var nightsDisplay = document.getElementById("nights_display");
var totalDisplay = document.getElementById("total_display");
var form = document.getElementById("booking_form");
var messageBox = document.getElementById("form_message");

// ---------- Small helper functions ----------

// Turn a Date into "2026-12-05". We build it by hand because toISOString()
// uses UTC time and could show yesterday's date in Sri Lanka (UTC+5:30).
function formatDate(date) {
    var month = ("0" + (date.getMonth() + 1)).slice(-2);
    var day = ("0" + date.getDate()).slice(-2);
    return date.getFullYear() + "-" + month + "-" + day;
}

// Return a new date that is "days" days after the given date
function addDays(date, days) {
    var newDate = new Date(date);
    newDate.setDate(newDate.getDate() + days);
    return newDate;
}

// Show numbers like 25000 as "LKR 25,000.00"
function formatMoney(amount) {
    return "LKR " + amount.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Count the nights between two date texts (returns 0 if something is missing/invalid)
function countNights(checkIn, checkOut) {
    if (checkIn === "" || checkOut === "") {
        return 0;
    }
    var milliseconds = new Date(checkOut) - new Date(checkIn);
    var nights = Math.round(milliseconds / (1000 * 60 * 60 * 24));   // ms -> days
    if (nights < 1) {
        return 0;
    }
    return nights;
}

// ---------- 1. Block past dates ----------
var today = new Date();
var todayText = formatDate(today);
checkInInput.min = todayText;                          // check-in: today or later
checkOutInput.min = formatDate(addDays(today, 1));     // check-out: tomorrow or later

// If a past date came in through the URL, clear it
if (checkInInput.value !== "" && checkInInput.value < todayText) {
    checkInInput.value = "";
}

// Make the check-out minimum depend on the chosen check-in
function updateCheckOutMin() {
    if (checkInInput.value !== "") {
        var earliest = formatDate(addDays(new Date(checkInInput.value), 1));
        checkOutInput.min = earliest;
        if (checkOutInput.value !== "" && checkOutInput.value < earliest) {
            checkOutInput.value = "";                  // old check-out is no longer valid
        }
    }
}

// ---------- 2. Live nights + total price ----------
function calculateTotal() {
    // Find the room that is selected in the drop-down
    var selectedOption = roomSelect.options[roomSelect.selectedIndex];
    var price = 0;
    var maxGuests = 1;
    if (selectedOption) {
        price = parseFloat(selectedOption.getAttribute("data-price"));
        maxGuests = parseInt(selectedOption.getAttribute("data-max"));
    }

    // The guests box can not be more than the room's maximum
    guestsInput.max = maxGuests;

    var nights = countNights(checkInInput.value, checkOutInput.value);
    var total = nights * price;

    // Show the results on the page
    priceDisplay.textContent = formatMoney(price);
    nightsDisplay.textContent = nights;
    totalDisplay.textContent = formatMoney(total);

    // Save the total in the hidden field so it is sent with the form
    totalHidden.value = total.toFixed(2);
}

// Run the calculation whenever the guest changes something
roomSelect.addEventListener("change", calculateTotal);
checkInInput.addEventListener("change", function () {
    updateCheckOutMin();
    calculateTotal();
});
checkOutInput.addEventListener("change", calculateTotal);

// Run once when the page opens (the values may already be filled in)
updateCheckOutMin();
calculateTotal();

// ---------- 3. Check the form before sending ----------
form.addEventListener("submit", function (event) {
    var errors = [];

    var selectedOption = roomSelect.options[roomSelect.selectedIndex];
    if (!selectedOption) {
        errors.push("Please choose a room.");
    }
    if (checkInInput.value === "" || checkOutInput.value === "") {
        errors.push("Please choose both check-in and check-out dates.");
    } else {
        if (checkInInput.value < todayText) {
            errors.push("Check-in date cannot be in the past.");
        }
        if (checkOutInput.value <= checkInInput.value) {
            errors.push("Check-out date must be after the check-in date.");
        }
    }
    if (selectedOption) {
        var maxGuests = parseInt(selectedOption.getAttribute("data-max"));
        var guests = parseInt(guestsInput.value);
        if (isNaN(guests) || guests < 1) {
            errors.push("Please enter at least 1 guest.");
        } else if (guests > maxGuests) {
            errors.push("This room allows a maximum of " + maxGuests + " guests.");
        }
    }

    // If there are errors: stop the form and show them
    if (errors.length > 0) {
        event.preventDefault();                       // cancel the sending
        messageBox.innerHTML = errors.join("<br>");
        messageBox.classList.remove("d-none");        // un-hide the red box
        window.scrollTo(0, 0);
    }
});
