// =====================================================
//  js/register.js
//  Used by register.php. Checks the registration form in the browser
//  and uses the DOM to show messages. (PHP checks everything again.)
// =====================================================

// ---------- Get the HTML elements we need ----------
var form = document.getElementById("register_form");
var nameInput = document.getElementById("name");
var emailInput = document.getElementById("email");
var contactInput = document.getElementById("contact_number");
var passwordInput = document.getElementById("password");
var confirmInput = document.getElementById("confirm_password");
var messageBox = document.getElementById("form_message");
var matchMessage = document.getElementById("match_message");

// ---------- Live message: do the two passwords match? ----------
function checkPasswordMatch() {
    if (confirmInput.value === "") {
        matchMessage.textContent = "";                       // nothing typed yet
    } else if (passwordInput.value === confirmInput.value) {
        matchMessage.textContent = "Passwords match ✔";
        matchMessage.className = "form-text text-success";   // green text
    } else {
        matchMessage.textContent = "Passwords do not match";
        matchMessage.className = "form-text text-danger";    // red text
    }
}
passwordInput.addEventListener("input", checkPasswordMatch);
confirmInput.addEventListener("input", checkPasswordMatch);

// ---------- Check the form when the Register button is pressed ----------
form.addEventListener("submit", function (event) {
    var errors = [];

    // Patterns (regular expressions) describe what a valid value looks like
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;    // something@something.something
    var phonePattern = /^[0-9+ ]{9,15}$/;               // 9-15 digits, + or spaces

    if (nameInput.value.trim() === "") {
        errors.push("Please enter your name.");
    }
    if (!emailPattern.test(emailInput.value.trim())) {
        errors.push("Please enter a valid email address.");
    }
    if (!phonePattern.test(contactInput.value.trim())) {
        errors.push("Contact number must have 9 to 15 digits (you may use + and spaces).");
    }
    if (passwordInput.value.length < 6) {
        errors.push("Password must be at least 6 characters long.");
    }
    if (passwordInput.value !== confirmInput.value) {
        errors.push("The two passwords do not match.");
    }

    // If there are errors: stop the form and show them in the red box
    if (errors.length > 0) {
        event.preventDefault();                       // cancel the sending
        messageBox.innerHTML = errors.join("<br>");
        messageBox.classList.remove("d-none");        // un-hide the red box
        window.scrollTo(0, 0);
    }
});
