let currentProductPage = 1;
let currentProductSearch = '';
let currentProductCategory = '';
let productLimit = 8;
let userCartItems = {};
let userWishlistProductIds = new Set();
let userWishlistItems = {};

const isProductListPage = () => document.body && window.location.pathname.includes('product-list.php');

function toast(message, type = 'success') {
    if (typeof showToast === 'function') {
        showToast(message, type);
        return;
    }
    alert(message);
}

document.addEventListener('DOMContentLoaded', async () => {
    const productContainer = document.getElementById('productContainer');

    if (productContainer) {
        const urlParams = new URLSearchParams(window.location.search);
        currentProductSearch = urlParams.get('search') ?? '';
        currentProductCategory = urlParams.get('category_id') ?? '';

        const searchInput = document.getElementById('productSearch');
        if (searchInput) searchInput.value = currentProductSearch;

        const minInput = document.getElementById('minPrice');
        const maxInput = document.getElementById('maxPrice');
        const ratingInput = document.getElementById('ratingFilter');
        const sortInput = document.getElementById('sortFilter');
        if (minInput) minInput.value = urlParams.get('min_price') ?? '';
        if (maxInput) maxInput.value = urlParams.get('max_price') ?? '';
        if (ratingInput) ratingInput.value = urlParams.get('rating') ?? '';
        if (sortInput) sortInput.value = urlParams.get('sort') ?? 'default';

        productLimit = isProductListPage() ? 12 : 8;

        await loadFrontendCategories();
        const categorySelect = document.getElementById('productCategoryFilter');
        if (categorySelect && currentProductCategory) categorySelect.value = currentProductCategory;

        await refreshUserShopState();
        fetchProducts();
    }

    if (document.getElementById('productDetails')) {
        await refreshUserShopState();
        loadProductDetails();
    }
});

function showProductAlert(message, type = 'danger') {
    const alertBox = document.getElementById('productAlert');
    if (!alertBox) return;
    alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
}

async function refreshUserShopState() {
    userCartItems = {};
    userWishlistProductIds = new Set();
    userWishlistItems = {};
    const token = localStorage.getItem('token');
    if (!token) return;

    try {
        const [cartRes, wishRes] = await Promise.allSettled([
            fetch(API_URL + 'cart/view.php', { headers: { Authorization: 'Bearer ' + token } }),
            fetch(API_URL + 'wishlist/view.php', { headers: { Authorization: 'Bearer ' + token } })
        ]);

        if (cartRes.status === 'fulfilled') {
            const cart = await cartRes.value.json();
            (cart.data?.items || []).forEach(item => {
                userCartItems[Number(item.product_id)] = {
                    cart_id: Number(item.id),
                    quantity: Number(item.quantity)
                };
            });
        }

        if (wishRes.status === 'fulfilled') {
            const wish = await wishRes.value.json();
            (wish.data || []).forEach(item => {
                userWishlistProductIds.add(Number(item.product_id));
                userWishlistItems[Number(item.product_id)] = Number(item.wishlist_id);
            });
        }
    } catch (e) {
        console.log(e);
    }
}

async function loadFrontendCategories() {
    try {
        const response = await fetch(API_URL + 'categories/list.php');
        const result = await response.json();
        if (!result.status) return;

        const select = document.getElementById('productCategoryFilter');
        if (select) {
            select.innerHTML = '<option value="">All Categories</option>';
            renderCategoryOptions(result.data, select);
        }

        if (typeof buildSearchCategoryDropdown === 'function') {
            buildSearchCategoryDropdown(result.data);
        }
    } catch (error) {
        console.log(error);
    }
}

function renderCategoryOptions(categories, select) {
    const parents = categories.filter(category => !category.parent_id);
    const children = categories.filter(category => category.parent_id);

    parents.forEach(parent => {
        select.innerHTML += `<option value="${parent.id}">${parent.name}</option>`;
        children.filter(child => Number(child.parent_id) === Number(parent.id)).forEach(child => {
            select.innerHTML += `<option value="${child.id}">— ${child.name}</option>`;
        });
    });
}

function applyProductFilters() {
    const searchInput = document.getElementById('productSearch');
    const categorySelect = document.getElementById('productCategoryFilter');
    currentProductSearch = searchInput ? searchInput.value.trim() : '';
    currentProductCategory = categorySelect ? categorySelect.value : '';
    currentProductPage = 1;
    fetchProducts();
}

function resetProductFilters() {
    ['productSearch', 'productCategoryFilter', 'minPrice', 'maxPrice', 'ratingFilter', 'sortFilter'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = id === 'sortFilter' ? 'default' : '';
    });
    currentProductSearch = '';
    currentProductCategory = '';
    currentProductPage = 1;
    fetchProducts();
}

async function fetchProducts(page = currentProductPage) {
    try {
        currentProductPage = page;
        const params = new URLSearchParams();
        params.append('page', currentProductPage);
        params.append('limit', productLimit);

        const minPrice = document.getElementById('minPrice')?.value || '';
        const maxPrice = document.getElementById('maxPrice')?.value || '';
        const rating = document.getElementById('ratingFilter')?.value || '';
        const sort = document.getElementById('sortFilter')?.value || 'default';

        if (currentProductSearch) params.append('search', currentProductSearch);
        if (currentProductCategory) params.append('category_id', currentProductCategory);
        if (minPrice) params.append('min_price', minPrice);
        if (maxPrice) params.append('max_price', maxPrice);
        if (rating) params.append('rating', rating);
        if (sort) params.append('sort', sort);

        const response = await fetch(API_URL + 'products/list.php?' + params.toString());
        const result = await response.json();
        const productContainer = document.getElementById('productContainer');
        productContainer.innerHTML = '';

        if (!result.status) {
            showProductAlert(result.message);
            return;
        }

        const products = result.data.products || [];
        const pagination = result.data.pagination;

        if (products.length === 0) {
            productContainer.innerHTML = `<div class="col-12"><div class="alert alert-warning text-center">No products found</div></div>`;
            renderProductPagination(pagination);
            return;
        }

        productContainer.innerHTML = products.map((product, index) => renderProductCard(product, index)).join('');
        renderProductPagination(isProductListPage() ? pagination : null);
        if (window.AOS) AOS.refresh();
    } catch (error) {
        console.log(error);
        showProductAlert('Failed to load products');
    }
}

function renderProductCard(product, index = 0) {
    const inCart = userCartItems[Number(product.id)];
    const inWishlist = userWishlistProductIds.has(Number(product.id));
    const delay = (index % 4) * 80;
    return `
        <div class="col-6 col-md-4 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="${delay}">
            <div class="card product-card h-100 shadow-sm">
                <div class="product-image-wrap" onclick="openProductDetails(${product.id})">
                    <img src="${product.image}" class="card-img-top" alt="${escapeHtml(product.name)}">
                    <button type="button" class="wishlist-float ${inWishlist ? 'active' : ''}" data-wishlist-product="${product.id}" onclick="event.stopPropagation(); toggleWishlist(${product.id})" aria-label="Wishlist">
                        <i class="fa-${inWishlist ? 'solid' : 'regular'} fa-heart"></i>
                    </button>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted small mb-1">${product.category_name ?? ''}</p>
                    <h5 class="product-title" onclick="openProductDetails(${product.id})">${escapeHtml(product.name)}</h5>
                    ${renderRatingSummary(product.average_rating, product.total_reviews)}
                    <div class="mb-2"><span class="product-price">₹${Number(product.price).toFixed(2)}</span></div>
                    <div class="mt-auto" id="productCartAction-${product.id}">
                        ${renderInlineCartAction(product.id, inCart)}
                    </div>
                </div>
            </div>
        </div>`;
}

function renderInlineCartAction(productId, cartItem) {
    if (!cartItem) {
        return `<button class="btn btn-brand w-100" onclick="addToCart(${productId})"><i class="fa-solid fa-bag-shopping me-1"></i>Cart</button>`;
    }
    return `
        <div class="compact-cart-control">
            <button type="button" onclick="updateCart(${cartItem.cart_id}, ${cartItem.quantity - 1})">−</button>
            <span>${cartItem.quantity}</span>
            <button type="button" onclick="updateCart(${cartItem.cart_id}, ${cartItem.quantity + 1})">+</button>
            <button type="button" class="trash" onclick="removeCart(${cartItem.cart_id})"><i class="fa-regular fa-trash-can"></i></button>
        </div>
        <small class="in-cart-label">In cart</small>`;
}

function openProductDetails(productId) {
    window.location.href = 'product-details.php?id=' + productId;
}

function updateProductCartAction(productId) {
    const box = document.getElementById('productCartAction-' + productId);
    if (box) box.innerHTML = renderInlineCartAction(productId, userCartItems[Number(productId)]);
    const detailBox = document.getElementById('detailCartAction');
    if (detailBox && Number(detailBox.dataset.productId) === Number(productId)) {
        detailBox.innerHTML = renderDetailCartAction(productId, userCartItems[Number(productId)]);
    }
}

function syncVisibleCartActions() {
    document.querySelectorAll('[id^="productCartAction-"]').forEach(box => {
        const productId = Number(box.id.replace('productCartAction-', ''));
        if (productId) box.innerHTML = renderInlineCartAction(productId, userCartItems[productId]);
    });

    const detailBox = document.getElementById('detailCartAction');
    if (detailBox && detailBox.dataset.productId) {
        const productId = Number(detailBox.dataset.productId);
        detailBox.innerHTML = renderDetailCartAction(productId, userCartItems[productId]);
    }
}

function updateWishlistButtons(productId) {
    const active = userWishlistProductIds.has(Number(productId));
    document.querySelectorAll(`[data-wishlist-product="${productId}"]`).forEach(btn => {
        btn.classList.toggle('active', active);
        btn.innerHTML = `<i class="fa-${active ? 'solid' : 'regular'} fa-heart${btn.classList.contains('wishlist-detail-btn') ? ' me-1' : ''}"></i>${btn.classList.contains('wishlist-detail-btn') ? (active ? 'Wishlisted' : 'Wishlist') : ''}`;
    });
}

async function addToCart(productId) {
    try {
        const token = localStorage.getItem('token');
        if (!token) {
            toast('Please login first', 'warning');
            window.location.href = 'login.php';
            return;
        }

        const response = await fetch(API_URL + 'cart/add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + token },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        });
        const result = await response.json();
        toast(result.message, result.status ? 'success' : 'danger');
        if (result.status) {
            await refreshUserShopState();
            updateProductCartAction(productId);
            syncVisibleCartActions();
            if (typeof updateNavbarCounts === 'function') updateNavbarCounts();
            if (document.getElementById('cartContainer') && typeof loadCart === 'function') loadCart();
        }
    } catch (error) {
        console.log(error);
        toast('Cart failed', 'danger');
    }
}

async function toggleWishlist(productId) {
    const productKey = Number(productId);
    if (userWishlistProductIds.has(productKey)) {
        const wishlistId = userWishlistItems[productKey];
        if (!wishlistId) {
            await refreshUserShopState();
        }
        const idToRemove = userWishlistItems[productKey];
        if (idToRemove && typeof removeWishlist === 'function') {
            await removeWishlist(idToRemove);
            await refreshUserShopState();
            updateWishlistButtons(productId);
            if (document.getElementById('wishlistContainer') && typeof loadWishlist === 'function') loadWishlist();
            return;
        }
        toast('Wishlist item not found. Please refresh once.', 'warning');
        return;
    }

    if (typeof addToWishlist === 'function') {
        await addToWishlist(productId);
        await refreshUserShopState();
        updateWishlistButtons(productId);
    }
}

async function loadProductDetails() {
    const id = new URLSearchParams(window.location.search).get('id');
    try {
        const response = await fetch(API_URL + 'products/details.php?id=' + id);
        const result = await response.json();
        if (!result.status) {
            const alertBox = document.getElementById('productDetailsAlert');
            if (alertBox) alertBox.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
            return;
        }

        const product = result.data;
        const images = (product.images && product.images.length ? product.images : [product.image]).filter(Boolean);
        const inWishlist = userWishlistProductIds.has(Number(product.id));
        const inCart = userCartItems[Number(product.id)];

        document.getElementById('productDetails').innerHTML = `
            <div class="row g-4 product-detail-modern" data-aos="fade-up">
                <div class="col-md-5">
                    <div class="product-gallery card-soft">
                        <img src="${images[0]}" id="mainProductImage" class="main-product-image" alt="${escapeHtml(product.name)}">
                        ${images.length > 1 ? `<div class="product-thumbs">${images.map((img, i) => `<button class="${i === 0 ? 'active' : ''}" onclick="setMainProductImage('${img}', this)"><img src="${img}" alt="thumb"></button>`).join('')}</div>` : ''}
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card-soft p-4 h-100 product-detail-info">
                        <p class="text-muted mb-1">${product.category_name ?? ''}</p>
                        <h2>${escapeHtml(product.name)}</h2>
                        ${renderRatingSummary(product.average_rating, product.total_reviews)}
                        <div class="mb-3"><span class="fs-3 fw-bold text-success">₹${Number(product.price).toFixed(2)}</span></div>
                        <span class="badge bg-success mb-3" style="width:max-content;">In Stock</span>
                        <p class="mt-3">${product.description ?? ''}</p>
                        <div class="d-flex flex-wrap gap-2 mt-4 align-items-center">
                            <div id="detailCartAction" data-product-id="${product.id}">${renderDetailCartAction(product.id, inCart)}</div>
                            <button class="btn wishlist-detail-btn ${inWishlist ? 'active' : ''}" data-wishlist-product="${product.id}" onclick="toggleWishlist(${product.id})">
                                <i class="fa-${inWishlist ? 'solid' : 'regular'} fa-heart me-1"></i>${inWishlist ? 'Wishlisted' : 'Wishlist'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        renderProductReviews();
        loadProductReviews(product.id);
        loadRelatedProducts(product.category_id, product.id);
        if (window.AOS) AOS.refresh();
    } catch (error) {
        console.log(error);
    }
}

function setMainProductImage(src, btn) {
    document.getElementById('mainProductImage').src = src;
    document.querySelectorAll('.product-thumbs button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function renderDetailCartAction(productId, cartItem) {
    if (!cartItem) return `<button class="btn btn-brand btn-lg" onclick="addToCart(${productId})"><i class="fa-solid fa-bag-shopping me-1"></i>Add To Cart</button>`;
    return `<div class="detail-cart-control">
        <button onclick="updateCart(${cartItem.cart_id}, ${cartItem.quantity - 1})">−</button>
        <span>${cartItem.quantity}</span>
        <button onclick="updateCart(${cartItem.cart_id}, ${cartItem.quantity + 1})">+</button>
        <button class="trash" onclick="removeCart(${cartItem.cart_id})"><i class="fa-regular fa-trash-can"></i></button>
    </div>`;
}

async function addToCartWithQty(productId) { await addToCart(productId); }
function increaseProductQty() {}
function decreaseProductQty() {}

async function loadRelatedProducts(categoryId, currentProductId) {
    try {
        if (!categoryId) return;
        const response = await fetch(API_URL + 'products/list.php?category_id=' + categoryId + '&page=1&limit=4');
        const result = await response.json();
        if (!result.status) return;
        const container = document.getElementById('relatedProducts');
        if (!container) return;
        const products = (result.data.products || []).filter(item => Number(item.id) !== Number(currentProductId));
        container.innerHTML = products.map((p, index) => renderProductCard(p, index)).join('');
    } catch (error) { console.log(error); }
}

function renderProductPagination(pagination) {
    const container = document.getElementById('productPagination');
    if (!container) return;
    container.innerHTML = '';
    if (!pagination || pagination.total_pages <= 1) return;

    if (currentProductPage > 1) container.innerHTML += `<button class="btn btn-outline-dark btn-sm" onclick="fetchProducts(${currentProductPage - 1})">Previous</button>`;
    for (let i = 1; i <= pagination.total_pages; i++) {
        container.innerHTML += `<button class="btn btn-sm ${Number(pagination.page) === i ? 'btn-dark' : 'btn-outline-dark'}" onclick="fetchProducts(${i})">${i}</button>`;
    }
    if (currentProductPage < pagination.total_pages) container.innerHTML += `<button class="btn btn-outline-dark btn-sm" onclick="fetchProducts(${currentProductPage + 1})">Next</button>`;
}

function renderProductReviews() {
    const reviewBox = document.getElementById('productReviews');
    if (!reviewBox) return;
    reviewBox.innerHTML = `<div class="card shadow-sm border-0 p-4"><h4 class="mb-3">Customer Reviews</h4><div class="alert alert-light border mb-0">No reviews yet.</div></div>`;
}

async function loadProductReviews(productId) {
    const reviewBox = document.getElementById('productReviews');
    if (!reviewBox) return;
    try {
        const response = await fetch(API_URL + 'reviews/list.php?product_id=' + productId);
        const result = await response.json();
        if (!result.status) return;
        const summary = result.data.summary;
        const reviews = result.data.reviews;
        const canReview = await checkCanReview(productId);
        let html = `<div class="card shadow-sm border-0 p-4"><h4 class="mb-3">Customer Reviews</h4><div class="mb-3"><strong>${summary.average_rating}</strong> / 5 <span class="text-muted">(${summary.total_reviews} reviews)</span></div>${renderReviewForm(productId, canReview)}`;
        if (reviews.length === 0) html += `<div class="alert alert-light border mb-0">No approved reviews yet.</div>`;
        else reviews.forEach(item => { html += `<div class="border-bottom py-3"><div class="fw-bold">${item.user_name}</div><div class="text-warning">${'★'.repeat(Number(item.rating))}${'☆'.repeat(5 - Number(item.rating))}</div><p class="mb-1">${item.review ?? ''}</p><small class="text-muted">${item.created_at}</small></div>`; });
        html += `</div>`;
        reviewBox.innerHTML = html;
    } catch (error) { console.log(error); }
}

async function submitProductReview(productId) {
    const token = localStorage.getItem('token');
    if (!token) { toast('Please login first', 'warning'); window.location.href = 'login.php'; return; }
    const rating = document.getElementById('reviewRating').value;
    const review = document.getElementById('reviewText').value.trim();
    if (!rating) { toast('Please select rating', 'warning'); return; }
    try {
        const response = await fetch(API_URL + 'reviews/add.php', { method: 'POST', headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + token }, body: JSON.stringify({ product_id: productId, rating, review }) });
        const result = await response.json();
        toast(result.message, result.status ? 'success' : 'danger');
        if (result.status) { document.getElementById('reviewRating').value = ''; document.getElementById('reviewText').value = ''; }
    } catch (error) { console.log(error); toast('Review submit failed', 'danger'); }
}

function renderRatingSummary(averageRating, totalReviews) {
    averageRating = Number(averageRating) || 0;
    totalReviews = Number(totalReviews) || 0;
    if (totalReviews === 0) return `<div class="text-muted small mb-2">No ratings yet</div>`;
    const fullStars = Math.floor(averageRating);
    const emptyStars = 5 - fullStars;
    return `<div class="product-rating mb-2"><span class="text-warning">${'★'.repeat(fullStars)}${'☆'.repeat(emptyStars)}</span><span class="text-muted small"> ${averageRating.toFixed(1)} (${totalReviews} reviews)</span></div>`;
}

async function checkCanReview(productId) {
    const token = localStorage.getItem('token');
    try {
        const response = await fetch(API_URL + 'reviews/can-review.php?product_id=' + productId, { headers: token ? { Authorization: 'Bearer ' + token } : {} });
        const result = await response.json();
        if (!result.status) return { can_review: false, reason: 'error' };
        return result.data;
    } catch (error) { console.log(error); return { can_review: false, reason: 'error' }; }
}

function renderReviewForm(productId, canReview) {
    if (!canReview || canReview.reason === 'login_required') return `<div class="alert alert-light border">Please login to write a review.</div>`;
    if (canReview.reason === 'not_delivered_buyer') return `<div class="alert alert-light border">Buy and receive this product to write a review.</div>`;
    if (!canReview.can_review) return `<div class="alert alert-light border">You are not eligible to review this product.</div>`;
    return `<div class="mb-4 border rounded p-3"><h5>Add Your Review</h5><select id="reviewRating" class="form-select mb-2"><option value="">Select Rating</option><option value="5">5 - Excellent</option><option value="4">4 - Good</option><option value="3">3 - Average</option><option value="2">2 - Poor</option><option value="1">1 - Bad</option></select><textarea id="reviewText" class="form-control mb-2" rows="3" placeholder="Write your review"></textarea><button class="btn btn-dark" onclick="submitProductReview(${productId})">Submit Review</button></div>`;
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));
}
