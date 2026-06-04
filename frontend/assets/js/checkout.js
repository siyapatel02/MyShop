let checkoutSubtotalAmount = 0;
let appliedCouponCode = '';
let appliedDiscountAmount = 0;
let savedAddressList = [];
let editingAddressId = null;

document.addEventListener('DOMContentLoaded', () => {
    const checkoutForm = document.getElementById('checkoutForm');

    if (checkoutForm) {
        loadCheckoutCart();
        loadSavedAddresses();
        loadAvailableOffers();

        document.querySelectorAll('#checkoutForm input').forEach(input => {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') e.preventDefault();
            });
        });

        checkoutForm.addEventListener('submit', placeOrder);
        const applyCouponBtn = document.getElementById('applyCouponBtn');

        if (applyCouponBtn) {
            applyCouponBtn.addEventListener('click', applyCoupon);
        }
    }
});

function showCheckoutAlert(message, type = 'danger') {
    const alertBox = document.getElementById('checkoutAlert');
    if (!alertBox) return;

    alertBox.innerHTML = `
        <div class="alert alert-${type}">
            ${message}
        </div>
    `;
}

async function loadSavedAddresses() {
    try {
        const token = localStorage.getItem('token');

        const response = await fetch(API_URL + 'addresses/list.php', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        });

        const result = await response.json();

        if (!result.status) {
            showCheckoutAlert(result.message || 'Failed to load saved addresses');
            return;
        }

        savedAddressList = result.data || [];

        renderSelectedAddress();
        renderAddressList();

    } catch (error) {
        console.log(error);
        showCheckoutAlert('Failed to load saved addresses');
    }
}

function renderSelectedAddress() {
    const selectedAddressBox = document.getElementById('selectedAddressBox');

    if (!selectedAddressBox) return;

    if (savedAddressList.length === 0) {
        selectedAddressBox.innerHTML = `
            <div class="alert alert-info">
                No saved address found. Add new address below.
            </div>
        `;
        clearAddressFields();
        return;
    }

    const address = savedAddressList[0];

    fillAddressFields(address);

    selectedAddressBox.innerHTML = `
        <div class="card border-success mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">
                            Delivering to:
                            <span class="badge bg-success">${address.address_type}</span>
                        </h6>

                        <strong>${address.fullname}</strong><br>

                        <small class="text-muted">
                            ${address.phone}<br>
                            ${address.address_line}, ${address.city}, ${address.state} - ${address.pincode}
                        </small>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAddressList()">
                        Change
                    </button>
                </div>
            </div>
        </div>
    `;
}

function renderAddressList() {
    const savedAddresses = document.getElementById('savedAddresses');

    if (!savedAddresses) return;

    savedAddresses.innerHTML = '';

    if (savedAddressList.length === 0) {
        savedAddresses.innerHTML = '';
        return;
    }

    savedAddresses.innerHTML = `
        <div class="card p-3 mb-3">
            <h6 class="mb-3">Choose delivery address</h6>
            <div id="allAddressCards"></div>

            <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="addNewAddressMode()">
                + Add New Address
            </button>
        </div>
    `;

    const allAddressCards = document.getElementById('allAddressCards');

    savedAddressList.forEach(address => {
        allAddressCards.innerHTML += `
            <div class="border rounded p-2 mb-2">
                <div class="form-check">
                    <input 
                        class="form-check-input"
                        type="radio"
                        name="selected_address"
                        id="address_${address.id}"
                        ${address.id == savedAddressList[0].id ? 'checked' : ''}
                        onchange="selectAddress(${address.id})"
                    >

                    <label class="form-check-label w-100" for="address_${address.id}">
                        <strong>${address.fullname}</strong>
                        <span class="badge bg-secondary">${address.address_type}</span>
                        <br>
                        <small>
                            ${address.phone}<br>
                            ${address.address_line}, ${address.city}, ${address.state} - ${address.pincode}
                        </small>
                    </label>
                </div>

                <div class="mt-2 ms-4">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editAddress(${address.id})">
                        Edit
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAddress(${address.id})">
                        Delete
                    </button>
                </div>
            </div>
        `;
    });
}

function toggleAddressList() {
    const savedAddresses = document.getElementById('savedAddresses');

    if (!savedAddresses) return;

    savedAddresses.style.display =
        savedAddresses.style.display === 'none' ? 'block' : 'none';
}

function selectAddress(id) {
    const address = savedAddressList.find(item => item.id == id);

    if (!address) return;

    savedAddressList = [
        address,
        ...savedAddressList.filter(item => item.id != id)
    ];

    fillAddressFields(address);
    renderSelectedAddress();

    document.getElementById('savedAddresses').style.display = 'none';
}

function fillAddressFields(address) {
    const form = document.getElementById('checkoutForm');

    form.fullname.value = address.fullname || '';
    form.phone.value = address.phone || '';
    form.pincode.value = address.pincode || '';
    form.address_line.value = address.address_line || '';
    form.city.value = address.city || '';
    form.state.value = address.state || '';
    form.address_type.value = address.address_type || 'home';
}

function clearAddressFields() {
    const form = document.getElementById('checkoutForm');

    form.fullname.value = '';
    form.phone.value = '';
    form.pincode.value = '';
    form.address_line.value = '';
    form.city.value = '';
    form.state.value = '';
    form.address_type.value = 'home';

    editingAddressId = null;
}

function addNewAddressMode() {
    clearAddressFields();
    editingAddressId = null;

    showCheckoutAlert('Add new address below and place order.', 'info');

    const savedAddresses = document.getElementById('savedAddresses');
    if (savedAddresses) savedAddresses.style.display = 'none';
}

function editAddress(id) {
    const address = savedAddressList.find(item => item.id == id);

    if (!address) return;

    editingAddressId = id;
    fillAddressFields(address);

    showCheckoutAlert('Edit address below. Changes will save automatically.', 'info');

    const savedAddresses = document.getElementById('savedAddresses');
    if (savedAddresses) savedAddresses.style.display = 'none';
}

async function updateAddressBeforeOrder() {
    if (!editingAddressId) return true;

    const token = localStorage.getItem('token');
    const form = document.getElementById('checkoutForm');

    const data = {
        id: editingAddressId,
        fullname: form.fullname.value.trim(),
        phone: form.phone.value.trim(),
        pincode: form.pincode.value.trim(),
        address_line: form.address_line.value.trim(),
        city: form.city.value.trim(),
        state: form.state.value.trim(),
        country: 'India',
        address_type: form.address_type.value,
        label: form.address_type.value
    };

    const response = await fetch(API_URL + 'addresses/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify(data)
    });

    const result = await response.json();

    if (!result.status) {
        showCheckoutAlert(result.message || 'Address update failed');
        return false;
    }

    editingAddressId = null;
    return true;
}

async function deleteAddress(id) {
    if (!confirm('Are you sure you want to delete this address?')) {
        return;
    }

    try {
        const token = localStorage.getItem('token');

        const response = await fetch(API_URL + 'addresses/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();

        if (result.status) {
            showCheckoutAlert(result.message, 'success');
            loadSavedAddresses();
            return;
        }

        showCheckoutAlert(result.message || 'Delete failed');

    } catch (error) {
        console.log(error);
        showCheckoutAlert('Delete failed');
    }
}

async function loadCheckoutCart() {
    try {
        const token = localStorage.getItem('token');

        if (!token) {
            window.location.href = 'login.php';
            return;
        }

        const response = await fetch(API_URL + 'cart/view.php', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        });

        const result = await response.json();

        const checkoutItems = document.getElementById('checkoutItems');
        const checkoutSubtotal = document.getElementById('checkoutSubtotal');
        const checkoutTotal = document.getElementById('checkoutTotal');

        checkoutItems.innerHTML = '';

        if (
            !result.status ||
            !result.data ||
            !result.data.items ||
            result.data.items.length === 0
        ) {
            showCheckoutAlert('Cart is empty', 'warning');
            checkoutItems.innerHTML = '<p class="text-muted">No items in cart</p>';
            checkoutSubtotalAmount = 0;
            appliedCouponCode = '';
            appliedDiscountAmount = 0;
            updateCheckoutTotal();
            checkoutSubtotal.innerText = '0.00';
            checkoutTotal.innerText = '0.00';
            return;
        }

        let subtotal = 0;

        result.data.items.forEach(item => {
            const itemTotal = Number(item.price) * Number(item.quantity);
            subtotal += itemTotal;

            checkoutItems.innerHTML += `
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <strong>${item.name}</strong>
                        <br>
                        <small>Qty: ${item.quantity}</small>
                    </div>
                    <div>₹${itemTotal.toFixed(2)}</div>
                </div>
            `;
        });


        checkoutSubtotal.innerText = subtotal.toFixed(2);
        checkoutSubtotalAmount = subtotal;
        updateCheckoutTotal();
    } catch (error) {
        console.log(error);
        showCheckoutAlert('Failed to load checkout data');
    }
}

async function placeOrder(e) {
    e.preventDefault();

    try {
        const token = localStorage.getItem('token');

        if (!token) {
            window.location.href = 'login.php';
            return;
        }

        const form = document.getElementById('checkoutForm');

        const paymentMethod =
            form.querySelector('input[name="payment_method"]:checked');

        const data = {
            fullname: form.fullname.value.trim(),
            phone: form.phone.value.trim(),
            pincode: form.pincode.value.trim(),
            address_line: form.address_line.value.trim(),
            city: form.city.value.trim(),
            state: form.state.value.trim(),
            country: 'India',
            address_type: form.address_type.value,
            label: form.address_type.value,
            payment_method: paymentMethod ? paymentMethod.value : '',
            coupon_code: appliedCouponCode,
            discount_amount: appliedDiscountAmount
        };

        if (
            !data.fullname ||
            !data.phone ||
            !data.pincode ||
            !data.address_line ||
            !data.city ||
            !data.state ||
            !data.payment_method
        ) {
            showCheckoutAlert('Please fill all required fields');
            return;
        }

        const addressUpdated = await updateAddressBeforeOrder();

        if (!addressUpdated) {
            return;
        }

        const response = await fetch(API_URL + 'orders/place.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.status) {
            showCheckoutAlert(result.message, 'success');

            setTimeout(() => {
                window.location.href = 'orders.php';
            }, 800);

            return;
        }

        showCheckoutAlert(result.message || 'Order failed');

    } catch (error) {
        console.log(error);
        showCheckoutAlert('Order failed');
    }
}
function updateCheckoutTotal() {
    const checkoutTotal = document.getElementById('checkoutTotal');
    const checkoutDiscount = document.getElementById('checkoutDiscount');
    const discountRow = document.getElementById('discountRow');

    const delivery = 50;
    const finalTotal = checkoutSubtotalAmount - appliedDiscountAmount + delivery;

    if (checkoutDiscount) {
        checkoutDiscount.innerText = appliedDiscountAmount.toFixed(2);
    }

    if (discountRow) {
        discountRow.style.setProperty(
            'display',
            appliedDiscountAmount > 0 ? 'flex' : 'none',
            'important'
        );
    }

    if (checkoutTotal) {
        checkoutTotal.innerText = finalTotal.toFixed(2);
    }
}

async function applyCoupon() {
    try {
        const token = localStorage.getItem('token');
        const couponInput = document.getElementById('couponCode');
        const couponMessage = document.getElementById('couponMessage');

        const code = couponInput.value.trim();

        if (!code) {
            couponMessage.innerHTML = '<span class="text-danger">Please enter coupon code</span>';
            return;
        }

        const response = await fetch(API_URL + 'offers/apply.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                code: code,
                subtotal: checkoutSubtotalAmount
            })
        });

        const result = await response.json();

        if (!result.status) {
            appliedCouponCode = '';
            appliedDiscountAmount = 0;
            updateCheckoutTotal();

            couponMessage.innerHTML = `<span class="text-danger">${result.message}</span>`;
            return;
        }

        appliedCouponCode = result.data.code;
        appliedDiscountAmount = Number(result.data.discount_amount);

        couponMessage.innerHTML = `
            <span class="text-success">
                ${result.message} - You saved ₹${appliedDiscountAmount.toFixed(2)}
            </span>
        `;

        updateCheckoutTotal();

    } catch (error) {
        console.log(error);
        document.getElementById('couponMessage').innerHTML =
            '<span class="text-danger">Coupon apply failed</span>';
    }
}
async function loadAvailableOffers() {
    try {
        const response = await fetch(API_URL + 'offers/active.php');
        const result = await response.json();

        const box = document.getElementById('availableOffers');

        if (!box) return;

        if (!result.status || !result.data || result.data.length === 0) {
            box.innerHTML = '';
            return;
        }

        let html = `
            <div class="border rounded p-2 bg-light">
                <div class="fw-semibold mb-2">Available Offers</div>
        `;

        result.data.forEach(offer => {
            html += `
                <div class="d-flex justify-content-between align-items-start border-bottom py-2">
                    <div>
                        <span class="badge bg-dark">${offer.code}</span>
                        <div class="small mt-1">${getOfferText(offer)}</div>
                        <div class="text-muted small">${offer.title}</div>
                    </div>

                    <button 
                        type="button" 
                        class="btn btn-sm btn-outline-success"
                        onclick="applySelectedOffer('${offer.code}')"
                    >
                        Apply
                    </button>
                </div>
            `;
        });

        html += `</div>`;

        box.innerHTML = html;

    } catch (error) {
        console.log(error);
    }
}

function getOfferText(offer) {
    if (offer.discount_type === 'percentage') {
        let text = `${Number(offer.discount_value).toFixed(0)}% OFF`;

        if (offer.max_discount) {
            text += ` up to ₹${Number(offer.max_discount).toFixed(0)}`;
        }

        if (Number(offer.minimum_order) > 0) {
            text += ` on orders above ₹${Number(offer.minimum_order).toFixed(0)}`;
        }

        return text;
    }

    let text = `₹${Number(offer.discount_value).toFixed(0)} OFF`;

    if (Number(offer.minimum_order) > 0) {
        text += ` on orders above ₹${Number(offer.minimum_order).toFixed(0)}`;
    }

    return text;
}

function applySelectedOffer(code) {
    const couponInput = document.getElementById('couponCode');

    if (couponInput) {
        couponInput.value = code;
    }

    applyCoupon();
}