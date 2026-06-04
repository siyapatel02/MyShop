<?php include '../components/header.php'; ?>
<?php include '../components/navbar.php'; ?>

<link rel="stylesheet" href="../assets/css/pages/home.css?v=4">

<main class="container page-shell home-page">

    <section class="home-banner-section" data-aos="fade-up">
        <div id="homeBannerSlider" class="home-banner-slider">
            <div class="home-banner-loading">Loading banners...</div>
        </div>
    </section>

    <section class="fashion-intro" data-aos="fade-up" data-aos-delay="100">
        <span class="eyebrow">New season edit</span>
        <h1>Discover refined fashion for every day.</h1>
        <p>Clean silhouettes, curated categories and smooth shopping — inspired by premium fashion ecommerce layouts.</p>
    </section>

    <div class="section-heading" data-aos="fade-up">
        <div>
            <span class="eyebrow">Shop now</span>
            <h2>Featured Products</h2>
        </div>
    </div>

    <div id="productAlert"></div>
    <div class="row g-4" id="productContainer"></div>
    <div class="text-center mt-4" data-aos="fade-up"><a href="product-list.php" class="btn btn-brand px-4">View All Products</a></div>

</main>

<script src="../assets/js/banners.js?v=1"></script>
<?php include '../components/footer.php'; ?>
