<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4 admin-dashboard-page">

<div class="admin-dashboard-header">
    <h2 class="admin-dashboard-title">Admin Dashboard</h2>
    <p class="admin-dashboard-subtitle">Overview of users, products, orders and sales performance.</p>
</div>

<div class="row g-4">

<div class="col-xl-3 col-md-6">
<div class="card shadow-sm admin-stat-card">
<div class="card-body">
<div class="admin-stat-icon">👥</div>
<p class="admin-stat-label">Total Users</p>
<h2 class="admin-stat-value" id="totalUsers">0</h2>
</div>
</div>
</div>

<div class="col-xl-3 col-md-6">
<div class="card shadow-sm admin-stat-card">
<div class="card-body">
<div class="admin-stat-icon">🛍️</div>
<p class="admin-stat-label">Products</p>
<h2 class="admin-stat-value" id="totalProducts">0</h2>
</div>
</div>
</div>

<div class="col-xl-3 col-md-6">
<div class="card shadow-sm admin-stat-card">
<div class="card-body">
<div class="admin-stat-icon">📦</div>
<p class="admin-stat-label">Orders</p>
<h2 class="admin-stat-value" id="totalOrders">0</h2>
</div>
</div>
</div>

<div class="col-xl-3 col-md-6">
<div class="card shadow-sm admin-stat-card">
<div class="card-body">
<div class="admin-stat-icon">₹</div>
<p class="admin-stat-label">Total Sales</p>
<h2 class="admin-stat-value" id="totalSales">₹0</h2>
</div>
</div>
</div>

</div>

<div class="row g-4 mt-2">

<div class="col-lg-6">
<div class="card shadow-sm admin-chart-card">
<h5 class="admin-section-title">Order Status</h5>
<canvas id="orderStatusChart"></canvas>
</div>
</div>

<div class="col-lg-6">
<div class="card shadow-sm admin-chart-card">
<h5 class="admin-section-title">Monthly Sales</h5>
<canvas id="monthlySalesChart"></canvas>
</div>
</div>

</div>

</div>

<script src="assets/js/admin-api.js"></script>
<script src="assets/js/admin-dashboard.js"></script>

</body>
</html>