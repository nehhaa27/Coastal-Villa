<?php
// =====================================================
//  register.php - Guest registration
//  The same file shows the form AND processes it:
//   - first visit  -> shows the empty form
//   - after submit -> checks the data, saves the user, then goes to login.php
// =====================================================
require_once 'db.php';

// A person who is already logged in does not need to register again
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$page_title = "Register - Mirissa Sands Villa";
$extra_script = "js/register.js";

$errors = array();      // a list of error messages
$name = "";
$email = "";
$contact = "";

// ---------- Did the person press the Register button? ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // "??" means: use the posted value, or "" if it was not sent at all
    $name     = trim($_POST['name'] ?? "");
    $email    = trim($_POST['email'] ?? "");
    $contact  = trim($_POST['contact_number'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm  = $_POST['confirm_password'] ?? "";

    // ---------- Server-side validation (JavaScript can be bypassed) ----------
    if ($name == "" || strlen($name) > 100) {
        $errors[] = "Please enter your name (maximum 100 characters).";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errors[] = "Please enter a valid email address.";
    }
    // 9 to 15 characters made of digits, + or spaces
    if (!preg_match('/^[0-9+ ]{9,15}$/', $contact)) {
        $errors[] = "Contact number must have 9 to 15 digits (you may use + and spaces).";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if ($password != $confirm) {
        $errors[] = "The two passwords do not match.";
    }

    // ---------- Is this email already registered? ----------
    if (count($errors) == 0) {
        $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);      // "s" = string
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "This email is already registered. Please log in.";
        }
        mysqli_stmt_close($stmt);
    }

    // ---------- No errors: save the new guest ----------
    if (count($errors) == 0) {
        // password_hash() scrambles the password. We NEVER store the real password.
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // The role is written as 'guest' directly in the SQL,
        // so nobody can make themselves an admin by editing the form.
        $stmt = mysqli_prepare($conn,
            "INSERT INTO users (name, email, contact_number, password, role)
             VALUES (?, ?, ?, ?, 'guest')");
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $contact, $hashed_password);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Registration successful! You can now log in.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Something went wrong while saving your account. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1 class="mb-0">Create an Account</h1>
        <p class="mb-0">Register as a guest to book rooms and manage your bookings.</p>
    </div>
</section>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <!-- novalidate = let OUR JavaScript show the messages instead of the browser's pop-ups -->
            <form id="register_form" method="post" action="register.php" class="card card-body shadow-sm" novalidate>

                <!-- Messages from JavaScript appear here -->
                <div id="form_message" class="alert alert-danger d-none"></div>

                <!-- Messages from PHP appear here -->
                <?php if (count($errors) > 0) { ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error) { ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Full name</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="<?php echo htmlspecialchars($name); ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo htmlspecialchars($email); ?>">
                </div>

                <div class="mb-3">
                    <label for="contact_number" class="form-label">Contact number</label>
                    <input type="text" class="form-control" id="contact_number" name="contact_number"
                           placeholder="e.g. 0771234567" value="<?php echo htmlspecialchars($contact); ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password (at least 6 characters)</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    <!-- JavaScript writes "Passwords match" / "do not match" here while typing -->
                    <div id="match_message" class="form-text"></div>
                </div>

                <button type="submit" class="btn btn-coral btn-lg">Register</button>
                <p class="mt-3 mb-0 text-center">Already have an account? <a href="login.php">Log in</a></p>
            </form>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
