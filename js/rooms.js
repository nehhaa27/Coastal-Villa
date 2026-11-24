// =====================================================
//  js/rooms.js
//  Used by rooms.php. Stops the guest from choosing a past date
//  in the availability search form.
// =====================================================

var checkInInput = document.getElementById("check_in");
var checkOutInput = document.getElementById("check_out");

// Turn a Date object into text like "2026-12-05" (the format <input type="date"> uses).
// NOTE: we build the text ourselves instead of using toISOString(),
// because toISOString() uses UTC time and can give yesterday's date in Sri Lanka.

function formatDate(date) {
    var month = ("0" + (date.getMonth() + 1)).slice(-2);   // getMonth() starts at 0
    var day = ("0" + date.getDate()).slice(-2);
    return date.getFullYear() + "-" + month + "-" + day;
}

// Add a number of days to a date and return the new date
function addDays(date, days) {
    var newDate = new Date(date);
    newDate.setDate(newDate.getDate() + days);
    return newDate;
}

var today = new Date();

// 1. Check-in can not be before today
checkInInput.min = formatDate(today);

// 2. Check-out can not be before tomorrow (at least 1 night)
checkOutInput.min = formatDate(addDays(today, 1));

// 3. When the guest picks a check-in date, check-out must be at least the next day
checkInInput.addEventListener("change", function () {
    if (checkInInput.value !== "") {
        var checkInDate = new Date(checkInInput.value);
        var earliestCheckOut = formatDate(addDays(checkInDate, 1));
        checkOutInput.min = earliestCheckOut;

        // If the old check-out is now too early, clear it so the guest picks again
        if (checkOutInput.value !== "" && checkOutInput.value < earliestCheckOut) {
            checkOutInput.value = "";
        }
    }
});
