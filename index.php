<?php
// =====================================================
//  index.php - Home page
//  Mostly HTML. PHP is only used to load the shared header/footer.
// =====================================================
require_once 'db.php';                       // connects to DB and starts the session
$page_title = "Mirissa Sands Villa - Beachfront Stay in Mirissa";
include 'includes/header.php';               // top of the page + navigation bar
?>

<!-- ============ HERO BANNER ============ -->
<section class="hero">
    <div class="container">
        <h1 class="display-4 fw-bold">Mirissa Sands Villa</h1>
        <p class="lead">
            A relaxed beachfront villa on the southern coast of Sri Lanka.
            Wake up to the sound of the waves, enjoy a fresh Sri Lankan breakfast
            and walk to the sand in under a minute.
        </p>
        <a href="rooms.php" class="btn btn-coral btn-lg">Book Now</a>
    </div>
</section>

<!-- ============ SHORT INTRODUCTION ============ -->
<section class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <h2 class="mb-3">Welcome to our villa</h2>
            <p>
                Mirissa Sands Villa is a small, family-run guesthouse with four
                comfortable rooms. Whether you are visiting for a quiet honeymoon,
                a family holiday or a whale-watching trip with friends, our team
                will help you plan your stay. Choose a room, pick your dates and
                book online in a few minutes.
            </p>
        </div>
    </div>
</section>

<!-- ============ PHOTO GALLERY ============ -->
<section class="container my-5">
    <h2 class="text-center mb-4">A Glimpse of the Villa</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php
        // file name
        $gallery = array(
            "Gallery_1" => "Mirissa Beach at sunrise",
            "Gallery_2" => "Sunset from the villa terrace",
            "Gallery_3" => "Our tropical garden"
        );

        // foreach repeats the block once for every item in the list above
        foreach ($gallery as $file => $caption) {
            // Use our own photo (gallery_1.jpg)
            $image = "images/" . $file . ".jpg";
            if (!file_exists($image)) {
                $image = "images/" . $file . ".svg";
            }
        ?>
        <div class="col">
            <div class="card room-card h-100">
                <img src="<?php echo $image; ?>" class="room-img card-img-top"
                     alt="<?php echo htmlspecialchars($caption); ?>">
                <div class="card-body text-center">
                    <p class="mb-0"><?php echo htmlspecialchars($caption); ?></p>
                </div>
            </div>
        </div>
        <?php } // end of foreach ?>
    </div>
</section>

<!-- ============ COASTAL LOCATION HIGHLIGHTS ============ -->
<section class="container my-5">
    <h2 class="text-center mb-4">Why Mirissa?</h2>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">

        <div class="col">
            <div class="card feature-card text-center p-3">
                <div class="feature-icon">🏖️</div>
                <h5 class="mt-2">Golden Beaches</h5>
                <p class="mb-0">A wide, calm bay for swimming, sunbathing and sunset walks.</p>
            </div>
        </div>

        <div class="col">
            <div class="card feature-card text-center p-3">
                <div class="feature-icon">🐋</div>
                <h5 class="mt-2">Whale Watching</h5>
                <p class="mb-0">Boat trips to see blue whales and dolphins (best from November to April).</p>
            </div>
        </div>

        <div class="col">
            <div class="card feature-card text-center p-3">
                <div class="feature-icon">🌴</div>
                <h5 class="mt-2">Coconut Tree Hill</h5>
                <p class="mb-0">A famous viewpoint, perfect for sunrise photos.</p>
            </div>
        </div>

        <div class="col">
            <div class="card feature-card text-center p-3">
                <div class="feature-icon">🏰</div>
                <h5 class="mt-2">Close to Galle Fort</h5>
                <p class="mb-0">A short drive to the historic fort, cafes and shops.</p>
            </div>
        </div>

    </div>
</section>

<!-- ============ MAIN FACILITIES ============ -->
<section class="container my-5">
    <h2 class="text-center mb-4">Our Facilities</h2>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3 text-center">
        <div class="col"><div class="p-3 bg-white rounded shadow-sm">📶<br>Free Wi-Fi</div></div>
        <div class="col"><div class="p-3 bg-white rounded shadow-sm">❄️<br>Air-conditioned rooms</div></div>
        <div class="col"><div class="p-3 bg-white rounded shadow-sm">🍳<br>Sri Lankan breakfast</div></div>
        <div class="col"><div class="p-3 bg-white rounded shadow-sm">🚗<br>Free parking</div></div>
        <div class="col"><div class="p-3 bg-white rounded shadow-sm">🏊<br>Swimming pool</div></div>
        <div class="col"><div class="p-3 bg-white rounded shadow-sm">🚐<br>Airport transfers</div></div>
    </div>
</section>

<!-- ============ CALL TO ACTION ============ -->
<section class="container my-5 text-center">
    <h3>Ready for your beach holiday?</h3>
    <p>See which rooms are free on your dates.</p>
    <a href="rooms.php" class="btn btn-coral btn-lg">Book Now</a>
</section>

<?php include 'includes/footer.php'; ?>
