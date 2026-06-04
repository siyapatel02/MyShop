<!DOCTYPE html>
<html>
<head>
<title>Admin Users</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>Users Management</h2>

<h5>
Total Users:
<span id="totalUsersCount" class="badge bg-dark">0</span>
</h5>

</div>

<div id="adminUsersAlert"></div>

<div class="card p-3 mb-4">
    <div class="row">
        <div class="col-md-5 mb-2">
            <input type="text" id="userSearch" class="form-control" placeholder="Search by name, email, phone...">
        </div>

        <div class="col-md-4 mb-2">
            <select id="userStatusFilter" class="form-control">
                <option value="">All Users</option>
                <option value="active">Active</option>
                <option value="blocked">Blocked</option>
            </select>
        </div>

        <div class="col-md-3 mb-2">
            <button class="btn btn-dark w-100" onclick="applyUserFilters()">Search</button>
        </div>
    </div>
</div>

<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">

<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>DOB</th>
<th>Gender</th>
<th>Orders</th>
<th>Total Spent</th>
<th>Joined</th>
<th>Action</th>
</tr>
</thead>

<tbody id="adminUsersTable"></tbody>

</table>

</div>
<div id="usersPagination" class="mt-3 d-flex gap-2 flex-wrap"></div>
</div>

<script src="assets/js/admin-api.js"></script>
<script src="assets/js/admin-users.js"></script>

</body>
</html>