<!DOCTYPE html>
<html>
<head>
<title>Admin Products</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>Products</h2>

<a href="add-product.php" class="btn btn-success">
Add Product
</a>

</div>

<div id="productsAlert"></div>
<div class="card p-3 mb-4">

<div class="row">

<div class="col-md-5 mb-2">

<input
type="text"
id="productSearch"
class="form-control"
placeholder="Search products..."
>

</div>

<div class="col-md-4 mb-2">

<select
id="productCategoryFilter"
class="form-control"
>

<option value="">All Categories</option>

</select>

</div>

<div class="col-md-3 mb-2">

<button
class="btn btn-dark w-100"
onclick="applyProductFilters()"
>

Search

</button>

</div>

</div>

</div>

<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead>
<tr>
<th>ID</th>
<th>Image</th>
<th>Name</th>
<th>Price</th>
<th>Category</th>
<th>Action</th>
</tr>
</thead>

<tbody id="adminProductsTable"></tbody>

</table>

<div
id="productsPagination"
class="mt-3 d-flex gap-2 flex-wrap"
></div>
</div>

</div>

<script src="assets/js/admin-api.js"></script>
<script src="assets/js/admin-products.js"></script>

</body>
</html>