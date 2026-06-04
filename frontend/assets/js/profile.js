let profileAddresses = [];
let profileOrders = [];
let profileWishlist = [];

document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('profileForm')) {
        return;
    }

    const token = localStorage.getItem('token');

    if (!token) {
        window.location.href = 'login.php';
        return;
    }

    setupProfileNavigation();
    setupProfileForm();
    setupChangePasswordForm();
    setupLogout();

    loadProfileData();
    loadProfileOrders();
    loadProfileAddresses();
    loadProfileWishlist();

    const dobInput = document.getElementById('dob');
    if (dobInput) {
        dobInput.setAttribute('max', new Date().toISOString().split('T')[0]);
    }
});

function getToken() {
    return localStorage.getItem('token');
}

function showProfileAlert(message, type = 'danger') {
    const alertBox = document.getElementById('profileAlert');

    if (!alertBox) {
        showProfileToast(message, type === 'danger' ? 'error' : 'success');
        return;
    }

    alertBox.innerHTML = `
        <div class="alert alert-${type}">
            ${message}
        </div>
    `;

    setTimeout(() => {
        alertBox.innerHTML = '';
    }, 3500);
}

function showProfileToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = type === 'error' ? 'profile-toast error' : 'profile-toast';
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function setupProfileNavigation() {
    const links = document.querySelectorAll('.profile-nav-link');
    const sections = document.querySelectorAll('.profile-section');

    links.forEach(link => {
        link.addEventListener('click', function (e) {
            if (this.id === 'logoutBtn') {
                return;
            }

            e.preventDefault();

            const targetId = this.getAttribute('href').replace('#', '');

            links.forEach(item => item.classList.remove('active'));
            this.classList.add('active');

            sections.forEach(section => {
                section.classList.add('d-none');
            });

            const target = document.getElementById(targetId);

            if (target) {
                target.classList.remove('d-none');
            }

            if (targetId === 'orders') {
                renderProfileOrders();
            }

            if (targetId === 'addresses') {
                renderProfileAddresses();
            }

            if (targetId === 'wishlist') {
                renderProfileWishlist();
            }
        });
    });
}

function setupProfileForm() {
    const editBtn = document.getElementById('editProfileBtn');
    const saveBtn = document.getElementById('saveProfileBtn');
    const form = document.getElementById('profileForm');

    if (editBtn) {
        editBtn.addEventListener('click', () => {
            document.querySelectorAll('#profileForm input, #profileForm select').forEach(input => {
                if (input.id !== 'email') {
                    input.disabled = false;
                }
            });

            saveBtn.classList.remove('d-none');
        });
    }

    if (form) {
        form.addEventListener('submit', updateProfile);
    }
}

function setupLogout() {
    const logoutBtn = document.getElementById('logoutBtn');

    if (!logoutBtn) return;

    logoutBtn.addEventListener('click', e => {
        e.preventDefault();

        if (!confirm('Are you sure you want to logout?')) {
            return;
        }

        localStorage.removeItem('token');
        localStorage.removeItem('user');

        showProfileToast('Logged out successfully');

        setTimeout(() => {
            window.location.href = 'login.php';
        }, 600);
    });
}

async function loadProfileData() {
    try {
        const response = await fetch(API_URL + 'user/profile.php', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken()
            }
        });

        const result = await response.json();

        if (!result.status) {
            showProfileAlert(result.message || 'Failed to load profile');
            return;
        }

        const user = result.data;

        document.getElementById('profileName').innerText = user.name || 'User';
        document.getElementById('profileEmail').innerText = user.email || '';
        document.getElementById('name').value = user.name || '';
        document.getElementById('email').value = user.email || '';
        document.getElementById('phone').value = user.phone || '';
        document.getElementById('dob').value = user.dob || '';
        document.getElementById('gender').value = user.gender || '';

        if (user.created_at) {
            document.getElementById('profileJoined').innerHTML =
                `<i class="far fa-calendar-alt me-2"></i>Member since ${formatDate(user.created_at)}`;
        }

        const savedUser = JSON.parse(localStorage.getItem('user') || '{}');
        savedUser.name = user.name;
        savedUser.email = user.email;
        localStorage.setItem('user', JSON.stringify(savedUser));

    } catch (error) {
        console.log(error);
        showProfileAlert('Failed to load profile');
    }
}

async function updateProfile(e) {
    e.preventDefault();

    try {
        const data = {
            name: document.getElementById('name').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            dob: document.getElementById('dob').value,
            gender: document.getElementById('gender').value
        };

        const response = await fetch(API_URL + 'user/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + getToken()
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!result.status) {
            showProfileAlert(result.message || 'Profile update failed');
            return;
        }

        showProfileAlert(result.message, 'success');

        document.querySelectorAll('#profileForm input, #profileForm select').forEach(input => {
            input.disabled = true;
        });

        document.getElementById('saveProfileBtn').classList.add('d-none');

        loadProfileData();

    } catch (error) {
        console.log(error);
        showProfileAlert('Profile update failed');
    }
}

async function loadProfileOrders() {
    try {
        const response = await fetch(API_URL + 'orders/history.php', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken()
            }
        });

        const result = await response.json();

        if (result.status) {
            profileOrders = result.data || [];
            document.getElementById('ordersCount').innerText = profileOrders.length;
            renderProfileOrders();
        }

    } catch (error) {
        console.log(error);
    }
}

function renderProfileOrders() {
    const ordersList = document.getElementById('profileOrdersList');

    if (!ordersList) return;

    if (!profileOrders.length) {
        ordersList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <h3>No orders yet</h3>
                <p>Your orders will appear here.</p>
                <a href="home.php" class="btn btn-dark mt-3">Start Shopping</a>
            </div>
        `;
        return;
    }

    let html = '';

    profileOrders.slice(0, 5).forEach(order => {
        html += `
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span class="order-id">Order #${order.id}</span>
                        <span class="order-date ms-3">${formatDate(order.created_at)}</span>
                    </div>

                    <span class="order-status ${getProfileStatusClass(order.status)}">
                        ${order.status}
                    </span>
                </div>

                <p class="mb-1">
                    <strong>Payment:</strong> ${String(order.payment_method).toUpperCase()}
                </p>

                <div class="order-total">
                    Total: ₹${Number(order.total).toFixed(2)}
                </div>

                <a href="order-details.php?id=${order.id}" class="btn btn-sm btn-outline-dark mt-3">
                    View Details
                </a>
            </div>
        `;
    });

    ordersList.innerHTML = html;
}

async function loadProfileAddresses() {
    try {
        const response = await fetch(API_URL + 'addresses/list.php', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken()
            }
        });

        const result = await response.json();

        if (result.status) {
            profileAddresses = result.data || [];
            document.getElementById('addressCount').innerText = profileAddresses.length;
            renderProfileAddresses();
        }

    } catch (error) {
        console.log(error);
    }
}

function renderProfileAddresses() {
    const addressesList = document.getElementById('profileAddressesList');

    if (!addressesList) return;

    if (!profileAddresses.length) {
        addressesList.innerHTML = `
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>No saved addresses</h3>
                    <p>Add an address during checkout.</p>
                    <a href="checkout.php" class="btn btn-dark mt-3">Add Address</a>
                </div>
            </div>
        `;
        return;
    }

    let html = '';

    profileAddresses.forEach((address, index) => {
        html += `
            <div class="col-md-6 mb-3">
                <div class="address-card ${index === 0 ? 'default' : ''}">
                    ${index === 0 ? '<span class="address-default-badge">Default</span>' : ''}

                    <h5>${address.label || address.address_type || 'Address'}</h5>
                    <p class="mb-2">${address.fullname}</p>
                    <p class="text-muted mb-2">${address.address_line}</p>
                    <p class="text-muted mb-2">${address.city}, ${address.state} - ${address.pincode}</p>
                    <p class="text-muted mb-2">${address.country || 'India'}</p>
                    <p class="mb-2">${address.phone}</p>

                    <div class="address-actions">
                        <a href="checkout.php" class="action-btn text-decoration-none">Edit</a>
                        <button class="action-btn delete" onclick="deleteProfileAddress(${address.id})">Delete</button>
                    </div>
                </div>
            </div>
        `;
    });

    addressesList.innerHTML = html;
}

async function deleteProfileAddress(id) {
    if (!confirm('Are you sure you want to delete this address?')) {
        return;
    }

    try {
        const response = await fetch(API_URL + 'addresses/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + getToken()
            },
            body: JSON.stringify({ id })
        });

        const result = await response.json();

        if (!result.status) {
            showProfileAlert(result.message || 'Address delete failed');
            return;
        }

        showProfileAlert(result.message, 'success');
        loadProfileAddresses();

    } catch (error) {
        console.log(error);
        showProfileAlert('Address delete failed');
    }
}

async function loadProfileWishlist() {
    try {
        const response = await fetch(API_URL + 'wishlist/view.php', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getToken()
            }
        });

        const result = await response.json();

        if (result.status) {
            profileWishlist = result.data || [];
            document.getElementById('wishlistCount').innerText = profileWishlist.length;
            renderProfileWishlist();
        }

    } catch (error) {
        console.log(error);
    }
}

function renderProfileWishlist() {
    const wishlistList = document.getElementById('profileWishlistList');

    if (!wishlistList) return;

    if (!profileWishlist.length) {
        wishlistList.innerHTML = `
            <div class="empty-state">
                <i class="far fa-heart"></i>
                <h3>Your wishlist is empty</h3>
                <p>Save items you love for later.</p>
                <a href="home.php" class="btn btn-dark mt-3">Start Shopping</a>
            </div>
        `;
        return;
    }

    let html = '';

    profileWishlist.forEach(item => {
        const imagePath = item.image
            ? `../../backend/uploads/products/${item.image}`
            : '../../backend/uploads/products/no-image.jpg';

        html += `
            <div class="wishlist-item">
                <div class="wishlist-remove" onclick="removeProfileWishlist(${item.wishlist_id})">
                    <i class="fas fa-times"></i>
                </div>

                <img src="${imagePath}" class="wishlist-img" alt="${item.name}" onerror="this.src='../../backend/uploads/products/no-image.jpg'">

                <div class="wishlist-info">
                    <h5 class="wishlist-name">${item.name}</h5>
                    <p class="wishlist-price">₹${Number(item.price || 0).toFixed(2)}</p>

                    <a href="product-details.php?id=${item.product_id || item.id}" class="btn btn-sm btn-dark w-100 mt-2">
                        View Product
                    </a>
                </div>
            </div>
        `;
    });

    wishlistList.innerHTML = html;
}

async function removeProfileWishlist(productId) {
    try {
        const response = await fetch(API_URL + 'wishlist/remove.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + getToken()
            },
            body: JSON.stringify({
                wishlist_id: productId
            })
        });

        const result = await response.json();

        if (!result.status) {
            showProfileAlert(result.message || 'Wishlist remove failed');
            return;
        }

        showProfileAlert(result.message, 'success');
        loadProfileWishlist();

    } catch (error) {
        console.log(error);
        showProfileAlert('Wishlist remove failed');
    }
}

function getProfileStatusClass(status) {
    status = String(status || '').toLowerCase();

    if (status === 'delivered') return 'status-delivered';
    if (status === 'processing') return 'status-processing';
    if (status === 'shipped') return 'status-shipped';
    if (status === 'cancelled') return 'status-cancelled';

    return 'status-pending';
}

function formatDate(dateString) {
    if (!dateString) return '-';

    const date = new Date(dateString.replace(' ', 'T'));

    if (isNaN(date.getTime())) {
        return dateString;
    }

    return date.toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function setupChangePasswordForm() {

    const form = document.getElementById('changePasswordForm');

    if (!form) {
        return;
    }

    form.addEventListener('submit', changePassword);
}

async function changePassword(e) {

    e.preventDefault();

    const currentPassword =
        document.getElementById('currentPassword').value.trim();

    const newPassword =
        document.getElementById('newPassword').value.trim();

    const confirmPassword =
        document.getElementById('confirmPassword').value.trim();

    if (!currentPassword || !newPassword || !confirmPassword) {
        showProfileAlert('All password fields are required');
        return;
    }

    if (newPassword.length < 6) {
        showProfileAlert('New password must be at least 6 characters');
        return;
    }

    if (newPassword !== confirmPassword) {
        showProfileAlert('New password and confirm password do not match');
        return;
    }

    try {
        const response = await fetch(
            API_URL + 'user/change-password.php',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_password: confirmPassword
                })
            }
        );

        const result = await response.json();

        if (!result.status) {
            showProfileAlert(result.message || 'Password change failed');
            return;
        }

        showProfileAlert(result.message, 'success');

        document.getElementById('changePasswordForm').reset();

    } catch (error) {
        console.log(error);
        showProfileAlert('Password change failed');
    }
}