<!DOCTYPE html>
<html>
<head>
<title>Admin Orders</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

<h2 class="mb-4">Orders Management</h2>

<div id="adminOrdersAlert"></div>

<div class="card p-3 mb-4">

<div class="row">

<div class="col-md-5 mb-2">

<input
type="text"
id="orderSearch"
class="form-control"
placeholder="Search order id, customer name, email"
>

</div>

<div class="col-md-4 mb-2">

<select
id="orderStatusFilter"
class="form-control"
>

<option value="">All Status</option>
<option value="pending">Pending</option>
<option value="shipped">Shipped</option>
<option value="delivered">Delivered</option>

</select>

</div>

<div class="col-md-3 mb-2">

<button
class="btn btn-dark w-100"
onclick="applyOrderFilters()"
>

Search

</button>

</div>

</div>

</div>

<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">

<thead>
<tr>
<th>Order ID</th>
<th>Customer</th>
<th>Email</th>
<th>Total</th>
<th>Payment</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>

<tbody id="adminOrdersTable"></tbody>

</table>

</div>

<div
id="ordersPagination"
class="mt-3 d-flex gap-2 flex-wrap"
></div>

</div>

<script src="assets/js/admin-api.js"></script>
<script src="assets/js/admin-orders.js"></script>

</body>
</html>