<!DOCTYPE html>
<html>
<head>
<title>Add Product</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

<h2 class="mb-4">Add Product</h2>

<div id="addProductAlert"></div>

<div class="card p-4 shadow-sm">

<form id="addProductForm" enctype="multipart/form-data">

<input
type="text"
name="name"
class="form-control mb-3"
placeholder="Product Name"
>

<textarea
name="description"
class="form-control mb-3"
placeholder="Product Description"
rows="4"
></textarea>

<input
type="number"
name="price"
class="form-control mb-3"
placeholder="Price"
>

<select
name="category_id"
id="productCategory"
class="form-control mb-3"
>

<option value="">Select Category</option>

</select>

<input
type="file"
name="image[]"
class="form-control mb-3"
accept="image/*"
multiple
>

<button
type="submit"
class="btn btn-success"
>

Create Product

</button>

<a
href="products.php"
class="btn btn-secondary"
>

Back

</a>

</form>

</div>

</div>

<script src="assets/js/admin-api.js"></script>
<script src="assets/js/admin-add-product.js"></script>

</body>
</html>