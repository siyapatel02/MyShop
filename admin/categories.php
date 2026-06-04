<!DOCTYPE html>
<html>
<head>
<title>Admin Categories</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

<h2 class="mb-4">Category Management</h2>

<div id="categoryAlert"></div>

<div class="card p-4 shadow-sm mb-4">

<h5 id="formTitle">Add Category</h5>

<form id="categoryForm">

<input
type="hidden"
name="id"
>

<div class="row">

<div class="col-md-4 mb-3">

<input
type="text"
name="name"
class="form-control"
placeholder="Category Name"
>

</div>

<div class="col-md-4 mb-3">

<select
name="parent_id"
class="form-control"
id="parentCategory"
>

<option value="">No Parent</option>

</select>

</div>

<div class="col-md-2 mb-3">

<select
name="status"
class="form-control"
>

<option value="active">Active</option>

<option value="inactive">Inactive</option>

</select>

</div>

<div class="col-md-2 mb-3">

<button
type="submit"
class="btn btn-success w-100"
id="submitBtn"
>

Save

</button>

</div>

</div>

<button
type="button"
class="btn btn-secondary btn-sm"
onclick="resetCategoryForm()"
>

Reset

</button>

</form>

</div>

<div class="card shadow-sm">

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered align-middle">

<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Slug</th>
<th>Parent</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody id="categoriesTable"></tbody>

</table>

</div>

</div>

</div>

</div>

<script src="assets/js/admin-api.js"></script>
<script src="assets/js/admin-categories.js"></script>

</body>
</html>