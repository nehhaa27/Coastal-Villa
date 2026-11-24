<?php
// =====================================================
//  includes/footer.php
//  The BOTTOM part of every page: footer text, Bootstrap JavaScript
//  and (optionally) the page's own JavaScript file.
//  A page can set $extra_script = "js/booking.js"; before including this.
// =====================================================
if (!isset($base_path)) {
    $base_path = "";
}
?>

<!-- ============ FOOTER ============ -->
<footer class="site-footer mt-5 py-4">
    <div class="container text-center">
        <p class="mb-1 fw-bold">🌴 Mirissa Sands Villa</p>
        <p class="mb-1">Mirissa, Southern Province, Sri Lanka</p>
        <p class="mb-1">📞 +94 76 197 5385 &nbsp;|&nbsp; ✉️ info@coastalvilla.lk</p>
        <small>&copy; <?php echo date("Y"); ?> Mirissa Sands Villa.</small>
    </div>
</footer>

<!-- Bootstrap 5 JavaScript (needed for the mobile menu button) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php
// Load this page's own JavaScript file, if the page asked for one
if (isset($extra_script)) {
    echo '<script src="' . $base_path . $extra_script . '"></script>';
}
?>
</body>
</html>
