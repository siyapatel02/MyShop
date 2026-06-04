<!DOCTYPE html>
<html>
<head>
<title>Admin Order Details</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>Order Details</h2>

<a href="orders.php" class="btn btn-secondary">
Back
</a>

</div>

<div id="adminOrderDetailsAlert"></div>

<div id="adminOrderDetailsContainer"></div>

</div>

<script src="assets/js/admin-api.js"></script>
<script src="assets/js/admin-order-details.js"></script>

</body>
</html>