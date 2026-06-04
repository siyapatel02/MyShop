<?php include '../components/header.php'; ?>
<?php include '../components/navbar.php'; ?>

<link rel="stylesheet" href="../assets/css/pages/home.css?v=6">

<main class="container page-shell product-list-page">
    <section class="product-list-hero card-soft" data-aos="fade-up">
        <div>
            <span class="eyebrow">Shop collection</span>
            <h1>All Products</h1>
            <p>Search, filter and sort products from every category.</p>
        </div>
    </section>

    <div class="row g-4 mt-2">
        <aside class="col-lg-3" data-aos="fade-up" data-aos-delay="100">
            <div class="filter-sidebar card-soft">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">Filters</h5>
                    <button class="btn btn-link p-0 filter-clear" type="button" onclick="resetProductFilters()">Clear</button>
                </div>

                <label class="form-label">Search</label>
                <input type="text" id="productSearch" class="form-control mb-3" placeholder="Search products...">

                <label class="form-label">Category</label>
                <select id="productCategoryFilter" class="form-select mb-3">
                    <option value="">All Categories</option>
                </select>

                <label class="form-label">Price Range</label>
                <div class="row g-2 mb-3">
                    <div class="col-6"><input type="number" id="minPrice" class="form-control" placeholder="Min"></div>
                    <div class="col-6"><input type="number" id="maxPrice" class="form-control" placeholder="Max"></div>
                </div>

                <label class="form-label">Rating</label>
                <select id="ratingFilter" class="form-select mb-4">
                    <option value="">Any Rating</option>
                    <option value="4">4★ & above</option>
                    <option value="3">3★ & above</option>
                    <option value="2">2★ & above</option>
                    <option value="1">1★ & above</option>
                </select>

                <button class="btn btn-brand w-100" onclick="applyProductFilters()">Apply Filters</button>
            </div>
        </aside>

        <section class="col-lg-9">
            <div class="list-toolbar" data-aos="fade-up" data-aos-delay="150">
                <div>
                    <span class="eyebrow">Results</span>
                    <h3 class="mb-0">Products</h3>
                </div>
                <select id="sortFilter" class="form-select sort-select" onchange="applyProductFilters()">
                    <option value="default">Sort by default</option>
                    <option value="price_low_high">Price: low to high</option>
                    <option value="price_high_low">Price: high to low</option>
                    <option value="newest">Newest first</option>
                    <option value="high_rated">High rated</option>
                </select>
            </div>

            <div id="productAlert"></div>
            <div class="row g-4" id="productContainer"></div>
            <div id="productPagination" class="mt-4 d-flex gap-2 flex-wrap justify-content-center"></div>
        </section>
    </div>
</main>

<?php include '../components/footer.php'; ?>
