
async function refreshCartEverywhere() {
    if (typeof refreshUserShopState === 'function') await refreshUserShopState();
    if (typeof syncVisibleCartActions === 'function') syncVisibleCartActions();
    if (document.getElementById('cartContainer')) loadCart();
    if (typeof updateNavbarCounts === 'function') updateNavbarCounts();
}
async function addToCart(productId) {

    try {

        const token =
            localStorage.getItem('token');

        if (!token) {
            if (typeof showToast === 'function') showToast('Please login first','warning'); else alert('Please login first');
            window.location.href = 'login.php';
            return;
        }

        const response =
            await fetch(
                API_URL + 'cart/add.php',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: 1
                    })
                }
            );

        const result =
            await response.json();

        if (typeof showToast === 'function') showToast(result.message, result.status ? 'success' : 'danger'); else if (typeof showToast === 'function') showToast(result.message,'danger'); else alert(result.message);

        if (result.status) {
            await refreshCartEverywhere();
        }

    } catch (error) {
        console.log(error);
        if (typeof showToast === 'function') showToast('Cart failed','danger'); else alert('Cart Failed');
    }
}

async function loadCart() {

    try {

        const token =
            localStorage.getItem('token');

        if (!token) {
            window.location.href = 'login.php';
            return;
        }

        const response =
            await fetch(
                API_URL + 'cart/view.php',
                {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    }
                }
            );

        const result =
            await response.json();

        console.log(result);

        const cartContainer =
            document.getElementById('cartContainer');

        const cartSubtotal =
            document.getElementById('cartSubtotal');

        const cartDelivery =
            document.getElementById('cartDelivery');

        const cartTotal =
            document.getElementById('cartTotal');

        const checkoutBtn =
            document.getElementById('checkoutBtn');

        cartContainer.innerHTML = '';

        if (!result.status) {
            cartContainer.innerHTML =
                `<div class="alert alert-danger">${result.message}</div>`;
            return;
        }

        const items =
            result.data.items ?? [];

        if (items.length === 0) {

            cartContainer.innerHTML = `
                <div class="empty-cart">
                    <h4>Your cart is empty</h4>
                    <p class="text-muted">Looks like you haven’t added anything yet.</p>
                    <a href="home.php" class="btn btn-dark mt-2">Shop Now</a>
                </div>
            `;

            cartSubtotal.innerText = '0.00';
            cartDelivery.innerText = '0.00';
            cartTotal.innerText = '0.00';

            checkoutBtn.classList.add('disabled');
            checkoutBtn.href = '#';

            return;
        }

        let subtotal = 0;

        items.forEach(item => {

            const total =
                Number(item.price) * Number(item.quantity);

            subtotal += total;

            cartContainer.innerHTML += `
                <div class="card cart-card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">

                            <div class="col-md-2">
                                <img
                                src="${item.image_url}"
                                class="cart-product-img"
                                >
                            </div>

                            <div class="col-md-4">
                                <h5 class="mb-1">${item.name}</h5>
                                <p class="text-muted mb-0">₹${Number(item.price).toFixed(2)}</p>
                            </div>

                            <div class="col-md-3">
                                <div class="qty-box">
                                    <button
                                    onclick="updateCart(${item.id}, ${item.quantity - 1})"
                                    class="btn btn-outline-danger btn-sm"
                                    >
                                    -
                                    </button>

                                    <span class="fw-bold">${item.quantity}</span>

                                    <button
                                    onclick="updateCart(${item.id}, ${item.quantity + 1})"
                                    class="btn btn-outline-success btn-sm"
                                    >
                                    +
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>₹${total.toFixed(2)}</strong>
                            </div>

                            <div class="col-md-1 text-end">
                                <button
                                onclick="removeCart(${item.id})"
                                class="btn btn-sm btn-outline-danger"
                                >
                                <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            `;
        });

        const delivery =
            subtotal > 0 ? 50 : 0;

        cartSubtotal.innerText =
            subtotal.toFixed(2);

        cartDelivery.innerText =
            delivery.toFixed(2);

        cartTotal.innerText =
            (subtotal + delivery).toFixed(2);

        checkoutBtn.classList.remove('disabled');
        checkoutBtn.href = 'checkout.php';

    } catch (error) {
        console.log(error);
    }
}

async function updateCart(cartId, quantity) {

    if (quantity < 1) {
        removeCart(cartId);
        return;
    }

    try {

        const token =
            localStorage.getItem('token');

        const response =
            await fetch(
                API_URL + 'cart/update.php',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({
                        cart_id: cartId,
                        quantity: quantity
                    })
                }
            );

        const result =
            await response.json();

        console.log(result);

        if (result.status) {
            await refreshCartEverywhere();
        } else {
            if (typeof showToast === 'function') showToast(result.message, result.status ? 'success' : 'danger'); else if (typeof showToast === 'function') showToast(result.message,'danger'); else alert(result.message);
        }

    } catch (error) {
        console.log(error);
    }
}

async function removeCart(cartId) {

    try {

        const token =
            localStorage.getItem('token');

        const response =
            await fetch(
                API_URL + 'cart/remove.php',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({
                        cart_id: cartId
                    })
                }
            );

        const result =
            await response.json();

        console.log(result);

        if (result.status) {
            await refreshCartEverywhere();
        } else {
            if (typeof showToast === 'function') showToast(result.message, result.status ? 'success' : 'danger'); else if (typeof showToast === 'function') showToast(result.message,'danger'); else alert(result.message);
        }

    } catch (error) {
        console.log(error);
    }
}

if (document.getElementById('cartContainer')) {
    loadCart();
}