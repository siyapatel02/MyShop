<!DOCTYPE html>
<html>
<head>
<title>Edit Product</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

<h2 class="mb-4">Edit Product</h2>

<div id="editProductAlert"></div>

<div class="card p-4 shadow-sm">

<form id="editProductForm" enctype="multipart/form-data">

<input
type="hidden"
name="id"
>

<div class="mb-3">

<label class="form-label">Product Name</label>

<input
type="text"
name="name"
class="form-control"
>

</div>

<div class="mb-3">

<label class="form-label">Description</label>

<textarea
name="description"
class="form-control"
rows="4"
></textarea>

</div>

<div class="mb-3">

<label class="form-label">Price</label>

<input
type="number"
name="price"
class="form-control"
>

</div>

<div class="mb-3">

<label class="form-label">Category</label>

<select
name="category_id"
id="editProductCategory"
class="form-control"
>

<option value="">Select Category</option>

</select>

</div>

<div class="mb-3">

<label class="form-label">Current Image</label>

<br>

<img
id="currentProductImage"
src=""
width="120"
height="120"
style="object-fit:cover;"
class="border rounded"
>

</div>

<div class="mb-3">

<label class="form-label">Change Image</label>

<input
type="file"
name="image"
class="form-control"
accept="image/*"
>

</div>

<button
type="submit"
class="btn btn-primary"
>

Update Product

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
<script src="assets/js/admin-edit-product.js"></script>

</body>
</html>