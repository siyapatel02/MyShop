<!DOCTYPE html>
<html>
<head>
<title>User Details</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>User Details</h2>

<a href="users.php" class="btn btn-secondary">
Back
</a>

</div>

<div id="userDetailsAlert"></div>

<div id="userDetailsContainer"></div>

</div>

<script src="assets/js/admin-api.js"></script>
<script src="assets/js/admin-user-details.js"></script>

</body>
</html>