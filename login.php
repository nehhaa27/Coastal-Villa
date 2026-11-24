<?php
// =====================================================
//  login.php - Login page
//  Checks the email and password, then stores the user's details in $_SESSION.
// =====================================================
require_once 'db.php';

// Already logged in? Go to the right page.
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

// logout.php sends people here with ?logged_out=1 so we can say goodbye
if (isset($_GET['logged_out'])) {
    $_SESSION['success'] = "You have been logged out.";
}

$page_title = "Login - Mirissa Sands Villa";
$error = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($email == "" || $password == "") {
        $error = "Please enter your email and password.";
    } else {
        // Find the user with this email
        $stmt = mysqli_prepare($conn, "SELECT user_id, name, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);      // false if no user has this email

        // password_verify() compares the typed password with the stored hash
        if ($user && password_verify($password, $user['password'])) {

            // Login is correct: make a fresh session id (protects against session hijacking)
            session_regenerate_id(true);

            // Remember who is logged in. Other pages read these values.
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            // We do not say WHICH one was wrong (email or password) - it helps attackers.
            $error = "Invalid email or password.";
        }
    }
}

include 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1 class="mb-0">Login</h1>
        <p class="mb-0">Log in to book rooms and see your bookings.</p>
    </div>
</section>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">

            <form method="post" action="login.php" class="card card-body shadow-sm">

                <?php if ($error != "") { ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php } ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-coral btn-lg">Login</button>
                <p class="mt-3 mb-0 text-center">New guest? <a href="register.php">Create an account</a></p>
            </form>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
