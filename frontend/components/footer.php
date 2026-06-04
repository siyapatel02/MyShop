<?php
$currentPath = $_SERVER['PHP_SELF'] ?? '';
$isPagesFolder = strpos($currentPath, '/pages/') !== false;
$assetBase = $isPagesFolder ? '../' : '';
$footerBase = $isPagesFolder ? '' : 'pages/';
?>

<footer class="site-footer mt-5" data-aos="fade-up">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4 col-md-6">
                <a class="footer-brand" href="<?= $footerBase ?>home.php"><span class="brand-mark">M</span> MyShop</a>
                <p class="footer-text mt-3">Minimal fashion shopping experience with clean products, smooth checkout and curated styles.</p>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6>Shop</h6>
                <a href="<?= $footerBase ?>home.php">Latest Products</a>
                <a href="<?= $footerBase ?>wishlist.php">Wishlist</a>
                <a href="<?= $footerBase ?>cart.php">Cart</a>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6>Account</h6>
                <a href="<?= $footerBase ?>login.php">Login</a>
                <a href="<?= $footerBase ?>register.php">Register</a>
                <a href="<?= $footerBase ?>orders.php">Orders</a>
            </div>
            <div class="col-lg-4 col-md-6">
                <h6>Style updates</h6>
                <p class="footer-text">Get fresh fashion picks, offers and new arrivals.</p>
                <div class="footer-subscribe">
                    <input type="email" placeholder="Email address">
                    <button type="button">Subscribe</button>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> MyShop. All rights reserved.</span>
            <span>Designed for modern fashion shopping.</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= $assetBase ?>assets/js/api.js"></script>
<script src="<?= $assetBase ?>assets/js/navbar.js"></script>
<script src="<?= $assetBase ?>assets/js/cart.js"></script>
<script src="<?= $assetBase ?>assets/js/wishlist.js"></script>
<script src="<?= $assetBase ?>assets/js/products.js"></script>
<script src="<?= $assetBase ?>assets/js/orders.js"></script>
<script src="<?= $assetBase ?>assets/js/ui-effects.js?v=6"></script>

</body>
</html>
