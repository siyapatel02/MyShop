async function loadWishlist() {
    try {
        const token = localStorage.getItem('token');
        if (!token) { window.location.href = 'login.php'; return; }

        const response = await fetch(API_URL + 'wishlist/view.php', { method: 'GET', headers: { Authorization: 'Bearer ' + token } });
        const result = await response.json();
        const container = document.getElementById('wishlistContainer');
        if (!container) return;
        container.innerHTML = '';

        if (!result.status) {
            container.innerHTML = `<div class="col-12"><div class="alert alert-danger">${result.message}</div></div>`;
            return;
        }

        if (result.data.length === 0) {
            container.innerHTML = `<div class="col-12"><div class="empty-cart"><h4>Your wishlist is empty</h4><p class="text-muted">Save your favourite products here.</p><a href="home.php" class="btn btn-dark mt-2">Explore Products</a></div></div>`;
            return;
        }

        result.data.forEach((item, index) => {
            const image = item.image_url ? item.image_url : (typeof UPLOAD_URL !== 'undefined' ? UPLOAD_URL + item.image : '../../backend/uploads/products/' + item.image);
            container.innerHTML += `
                <div class="col-6 col-md-4 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="${(index % 4) * 80}">
                    <div class="card product-card h-100 shadow-sm">
                        <div class="product-image-wrap" onclick="window.location.href='product-details.php?id=${item.product_id}'">
                            <img src="${image}" class="card-img-top" alt="${item.name}">
                            <button class="wishlist-float active" onclick="event.stopPropagation(); removeWishlist(${item.wishlist_id})" aria-label="Remove wishlist">
                                <i class="fa-solid fa-heart"></i>
                            </button>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="product-title">${item.name}</h5>
                            <p class="fw-bold product-price">₹${Number(item.price).toFixed(2)}</p>
                            <button class="btn btn-brand mt-auto" onclick="moveWishlistToCart(${item.product_id}, ${item.wishlist_id})"><i class="fa-solid fa-bag-shopping me-1"></i>Move to Cart</button>
                        </div>
                    </div>
                </div>`;
        });
        if (window.AOS) AOS.refresh();
    } catch (error) { console.log(error); }
}

async function addToWishlist(productId) {
    try {
        const token = localStorage.getItem('token');
        if (!token) { if (typeof showToast === 'function') showToast('Please login first','warning'); else alert('Please login first'); window.location.href = 'login.php'; return; }
        const response = await fetch(API_URL + 'wishlist/add.php', { method: 'POST', headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + token }, body: JSON.stringify({ product_id: productId }) });
        const result = await response.json();
        if (typeof showToast === 'function') showToast(result.message, result.status ? 'success' : 'danger'); else alert(result.message);
        if (result.status && typeof updateNavbarCounts === 'function') updateNavbarCounts();
        if (result.status && typeof refreshUserShopState === 'function') await refreshUserShopState();
        if (result.status && typeof updateWishlistButtons === 'function') updateWishlistButtons(productId);
        if (document.getElementById('wishlistContainer')) loadWishlist();
    } catch (error) { console.log(error); if (typeof showToast === 'function') showToast('Wishlist failed','danger'); else alert('Wishlist failed'); }
}

async function removeWishlist(wishlistId) {
    try {
        const token = localStorage.getItem('token');
        const removedProductId = (typeof userWishlistItems !== 'undefined')
            ? Object.keys(userWishlistItems || {}).find(pid => Number(userWishlistItems[pid]) === Number(wishlistId))
            : null;
        const response = await fetch(API_URL + 'wishlist/remove.php', { method: 'POST', headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + token }, body: JSON.stringify({ wishlist_id: wishlistId }) });
        const result = await response.json();
        if (typeof showToast === 'function') showToast(result.message, result.status ? 'success' : 'danger'); else alert(result.message);
        if (result.status) {
            if (typeof updateNavbarCounts === 'function') updateNavbarCounts();
            if (typeof refreshUserShopState === 'function') await refreshUserShopState();
            if (removedProductId && typeof updateWishlistButtons === 'function') updateWishlistButtons(removedProductId);
            if (document.getElementById('wishlistContainer')) loadWishlist();
        }
    } catch (error) { console.log(error); }
}

async function moveWishlistToCart(productId, wishlistId) {
    try {
        const token = localStorage.getItem('token');
        const cartResponse = await fetch(API_URL + 'cart/add.php', { method: 'POST', headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + token }, body: JSON.stringify({ product_id: productId, quantity: 1 }) });
        const cartResult = await cartResponse.json();
        if (!cartResult.status) { if (typeof showToast === 'function') showToast(cartResult.message,'danger'); else alert(cartResult.message); return; }
        await fetch(API_URL + 'wishlist/remove.php', { method: 'POST', headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + token }, body: JSON.stringify({ wishlist_id: wishlistId }) });
        if (typeof showToast === 'function') showToast('Product moved to cart','success'); else alert('Product moved to cart');
        if (typeof updateNavbarCounts === 'function') updateNavbarCounts();
        if (typeof refreshUserShopState === 'function') await refreshUserShopState();
        loadWishlist();
    } catch (error) { console.log(error); if (typeof showToast === 'function') showToast('Move to cart failed','danger'); else alert('Move to cart failed'); }
}

if (document.getElementById('wishlistContainer')) loadWishlist();
