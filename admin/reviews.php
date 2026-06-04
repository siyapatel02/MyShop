<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Reviews</h2>
    </div>

    <div id="reviewAdminAlert"></div>

    <div class="mb-3">
        <button class="btn btn-dark btn-sm" onclick="filterAdminReviews('all')">All</button>
        <button class="btn btn-warning btn-sm" onclick="filterAdminReviews('pending')">Pending</button>
        <button class="btn btn-success btn-sm" onclick="filterAdminReviews('active')">Active</button>
        <button class="btn btn-secondary btn-sm" onclick="filterAdminReviews('hidden')">Hidden</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>User</th>
                    <th>Product</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th width="180">Action</th>
                </tr>
            </thead>

            <tbody id="adminReviewsTable">
                <tr>
                    <td colspan="7" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
    const API_URL = window.location.origin + '/' + window.location.pathname.split('/').filter(Boolean)[0] + '/backend/api/';
</script>


<script src="assets/js/admin-reviews.js"></script>

