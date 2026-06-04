<?php include '../components/header.php'; ?>
<?php include '../components/navbar.php'; ?>

<link rel="stylesheet" href="../assets/css/pages/orders.css?v=5">

<main class="container page-shell order-details-page" data-aos="fade-up">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Order Details</h2>

        <a href="orders.php" class="btn btn-outline-dark btn-sm">
            Back to Orders
        </a>
    </div>

    <div id="ordersAlert"></div>

    <div id="detailsContainer">
        <div class="text-center text-muted py-5">
            Loading order details...
        </div>
    </div>

</main>

<?php include '../components/footer.php'; ?>