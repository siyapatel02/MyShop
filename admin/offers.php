<?php include 'includes/navbar.php'; ?>

<link rel="stylesheet" href="assets/css/pages/admin-offers.css?v=1">

<div class="admin-offers-page">
    <div class="offers-header">
        <div>
            <h2>Offers & Coupons</h2>
            <p>Create and manage discount coupons for customers.</p>
        </div>

        <button class="btn btn-dark" id="newOfferBtn">
            + Add Offer
        </button>
    </div>

    <div id="offersAlert"></div>

    <div class="offer-form-card d-none" id="offerFormCard">
        <h4 id="offerFormTitle">Add New Offer</h4>

        <form id="offerForm">
            <input type="hidden" id="offerId">

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Coupon Code</label>
                    <input type="text" id="code" class="form-control" placeholder="WELCOME10">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Title</label>
                    <input type="text" id="title" class="form-control" placeholder="Welcome Offer">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Status</label>
                    <select id="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="first_order_only"
                    >
                    <label class="form-check-label" for="first_order_only">
                        First order only
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea id="description" class="form-control" rows="2"></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Discount Type</label>
                    <select id="discount_type" class="form-control">
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed Amount</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Discount Value</label>
                    <input type="number" id="discount_value" class="form-control" placeholder="10">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Minimum Order</label>
                    <input type="number" id="minimum_order" class="form-control" value="0">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Max Discount</label>
                    <input type="number" id="max_discount" class="form-control" placeholder="Optional">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Start Date</label>
                    <input type="date" id="start_date" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Expiry Date</label>
                    <input type="date" id="expiry_date" class="form-control">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Save Offer</button>
                <button type="button" class="btn btn-outline-secondary" id="cancelOfferBtn">Cancel</button>
            </div>
        </form>
    </div>

    <div class="offers-list-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Discount</th>
                        <th>Min Order</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th width="160">Actions</th>
                    </tr>
                </thead>
                <tbody id="offersTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script src="assets/js/admin-api.js"></script>
<script src="assets/js/admin-offers.js?v=1"></script>