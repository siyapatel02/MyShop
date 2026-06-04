<?php include 'includes/navbar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/pages/admin-banners.css?v=1">

<div class="admin-banners-page">

    <div class="banners-header">
        <div>
            <h2>Banner Management</h2>
            <p>Create and manage homepage banners.</p>
        </div>

        <button class="btn btn-dark" id="newBannerBtn">
            + Add Banner
        </button>
    </div>

    <div id="bannersAlert"></div>

    <div class="banner-form-card d-none" id="bannerFormCard">
        <h4 id="bannerFormTitle">Add New Banner</h4>

        <form id="bannerForm" enctype="multipart/form-data">
            <input type="hidden" id="bannerId">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Title</label>
                    <input type="text" id="title" class="form-control" placeholder="Summer Fashion Sale">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select id="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label>Subtitle</label>
                <textarea id="subtitle" class="form-control" rows="2" placeholder="Up to 50% off on latest collection"></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Button Text</label>
                    <input type="text" id="button_text" class="form-control" placeholder="Shop Now">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Button Link</label>
                    <input type="text" id="button_link" class="form-control" placeholder="../frontend/pages/home.php">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Banner Image</label>
                    <input type="file" id="image" class="form-control" accept="image/*">
                </div>
            </div>

            <div id="bannerPreviewBox" class="d-none mb-3">
                <label>Current Image</label>
                <br>
                <img id="bannerPreview" class="banner-preview-img">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Save Banner</button>
                <button type="button" class="btn btn-outline-secondary" id="cancelBannerBtn">Cancel</button>
            </div>
        </form>
    </div>

    <div class="banners-list-card">
        <div class="row" id="bannersList"></div>
    </div>

</div>

<script src="assets/js/admin-api.js"></script>
<script src="assets/js/admin-banners.js?v=1"></script>