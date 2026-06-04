document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('ordersContainer')) loadOrders();
    if (document.getElementById('detailsContainer')) loadOrderDetails();
});

function showOrdersAlert(message, type = 'danger') {
    const alertBox = document.getElementById('ordersAlert');
    if (!alertBox) return;
    alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
}

function getStatusBadge(status) {
    switch ((status || '').toLowerCase()) {
        case 'pending': return 'bg-warning text-dark';
        case 'processing': return 'bg-info text-dark';
        case 'shipped': return 'bg-primary';
        case 'delivered': return 'bg-success';
        case 'cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

async function loadOrders() {
    try {
        const token = localStorage.getItem('token');
        if (!token) { window.location.href = 'login.php'; return; }

        const response = await fetch(API_URL + 'orders/history.php', { headers: { Authorization: 'Bearer ' + token } });
        const result = await response.json();
        const container = document.getElementById('ordersContainer');
        container.innerHTML = '';

        if (!result.status) { showOrdersAlert(result.message || 'Failed to load orders'); return; }
        if (!result.data || result.data.length === 0) {
            container.innerHTML = `<div class="empty-cart"><h4>No orders found</h4><p class="text-muted">Your shopping history will appear here.</p><a class="btn btn-brand" href="home.php">Shop Now</a></div>`;
            return;
        }

        container.innerHTML = result.data.map(order => `
            <div class="order-list-card" data-aos="fade-up">
                <div class="order-list-main">
                    <div>
                        <span class="eyebrow">Order #${order.id}</span>
                        <h5>₹${Number(order.total).toFixed(2)}</h5>
                        <div class="order-meta">
                            <span><i class="fa-regular fa-calendar"></i>${order.created_at}</span>
                            <span><i class="fa-regular fa-credit-card"></i>${(order.payment_method || '').toUpperCase()}</span>
                            ${order.coupon_code ? `<span><i class="fa-solid fa-tag"></i>${order.coupon_code}</span>` : ''}
                        </div>
                    </div>
                    <span class="badge ${getStatusBadge(order.status)}">${order.status}</span>
                </div>
                <div class="order-list-footer">
                    <span class="text-muted">${order.address ? String(order.address).slice(0, 80) : 'Delivery address saved'}</span>
                    <a href="order-details.php?id=${order.id}" class="btn btn-outline-dark btn-sm">View Details</a>
                </div>
            </div>`).join('');
        if (window.AOS) AOS.refresh();
    } catch (error) {
        console.log(error);
        showOrdersAlert('Failed to load orders');
    }
}

function renderProgress(status) {
    const order = ['pending', 'processing', 'shipped', 'delivered'];
    const labels = {
        pending: ['Order Placed', 'Package confirmed'],
        processing: ['Processing', 'Seller is preparing your order'],
        shipped: ['Shipped', 'On the way to your address'],
        delivered: ['Delivered', 'Successfully delivered']
    };
    const icons = { pending: 'fa-box', processing: 'fa-gears', shipped: 'fa-truck-fast', delivered: 'fa-circle-check' };
    const currentIndex = order.indexOf((status || '').toLowerCase());
    return `<div class="status-timeline">${order.map((s, i) => `<div class="timeline-step ${i <= currentIndex ? 'active' : ''}"><div class="timeline-icon"><i class="fa-solid ${icons[s]}"></i></div><div><strong>${labels[s][0]}</strong><small>${labels[s][1]}</small></div></div>`).join('')}</div>`;
}

async function loadOrderDetails() {
    try {
        const token = localStorage.getItem('token');
        if (!token) { window.location.href = 'login.php'; return; }
        const id = new URLSearchParams(window.location.search).get('id');
        if (!id) { showOrdersAlert('Invalid order id'); return; }

        const response = await fetch(API_URL + 'orders/detail.php?id=' + id, { headers: { Authorization: 'Bearer ' + token } });
        const result = await response.json();
        const container = document.getElementById('detailsContainer');
        container.innerHTML = '';
        if (!result.status) { showOrdersAlert(result.message || 'Failed to load order details'); return; }

        const order = result.data.order;
        const items = result.data.items || [];
        const itemsHtml = items.map(item => {
            const itemTotal = Number(item.price) * Number(item.quantity);
            const img = item.image_url || (typeof UPLOAD_URL !== 'undefined' ? UPLOAD_URL + item.image : '../../backend/uploads/products/' + item.image);
            return `<tr><td><div class="order-product"><img src="${img}" class="order-details-image" onerror="this.src='../../backend/uploads/products/no-image.jpg'"><span>${item.name}</span></div></td><td>${item.quantity}</td><td>₹${Number(item.price).toFixed(2)}</td><td><strong>₹${itemTotal.toFixed(2)}</strong></td></tr>`;
        }).join('');

        container.innerHTML = `
            <div class="row g-4 mb-4" data-aos="fade-up">
                <div class="col-lg-7">
                    <div class="order-card">
                        <div class="order-card-header"><i class="fa-solid fa-truck-fast me-2"></i>Order Status</div>
                        <div class="order-status-body">${renderProgress(order.status)}</div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="order-card h-100">
                        <div class="order-card-header"><i class="fa-regular fa-credit-card me-2"></i>Payment & Delivery</div>
                        <div class="order-status-body">
                            <div class="payment-box"><small>Order ID</small><strong>#${order.id}</strong></div>
                            <div class="payment-box"><small>Payment Method</small><strong>${order.payment_method}</strong></div>
                            <div class="payment-box"><small>Total</small><strong>₹${Number(order.total).toFixed(2)}</strong></div>
                            ${order.coupon_code ? `<div class="payment-box"><small>Coupon</small><strong>${order.coupon_code}</strong></div>` : ''}
                        </div>
                    </div>
                </div>
            </div>
            <div class="order-card" data-aos="fade-up">
                <div class="order-card-header">Products</div>
                <div class="table-responsive">
                    <table class="table order-items-table mb-0">
                        <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                        <tbody>${itemsHtml}</tbody>
                    </table>
                </div>
            </div>`;
        if (window.AOS) AOS.refresh();
    } catch (error) {
        console.log(error);
        showOrdersAlert('Failed to load order details');
    }
}
