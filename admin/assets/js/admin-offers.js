let offers = [];

document.addEventListener('DOMContentLoaded', () => {
    loadOffers();

    document.getElementById('newOfferBtn').addEventListener('click', showCreateForm);
    document.getElementById('cancelOfferBtn').addEventListener('click', hideOfferForm);
    document.getElementById('offerForm').addEventListener('submit', saveOffer);
});

function getAdminToken() {
    return localStorage.getItem('admin_token') || localStorage.getItem('token');
}

function showOffersAlert(message, type = 'danger') {
    const box = document.getElementById('offersAlert');

    box.innerHTML = `
        <div class="alert alert-${type}">
            ${message}
        </div>
    `;

    setTimeout(() => {
        box.innerHTML = '';
    }, 3000);
}

function showCreateForm() {
    document.getElementById('offerFormTitle').innerText = 'Add New Offer';
    document.getElementById('offerForm').reset();
    document.getElementById('offerId').value = '';
    document.getElementById('minimum_order').value = 0;
    document.getElementById('first_order_only').checked = false;
    document.getElementById('offerFormCard').classList.remove('d-none');
}

function hideOfferForm() {
    document.getElementById('offerFormCard').classList.add('d-none');
    document.getElementById('offerForm').reset();
    document.getElementById('offerId').value = '';
    document.getElementById('first_order_only').checked = false;
}

async function loadOffers() {
    try {
        const response = await fetch('../backend/api/offers/list.php');
        const result = await response.json();

        if (!result.status) {
            showOffersAlert(result.message || 'Failed to load offers');
            return;
        }

        offers = result.data || [];
        renderOffers();

    } catch (error) {
        console.log(error);
        showOffersAlert('Failed to load offers');
    }
}

function renderOffers() {
    const tbody = document.getElementById('offersTableBody');

    if (!offers.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    No offers found
                </td>
            </tr>
        `;
        return;
    }

    let html = '';

    offers.forEach(offer => {
        html += `
            <tr>
                <td>
                    <span class="coupon-code">${offer.code}</span>
                </td>

                <td>
                    <strong>${offer.title}</strong>
                    <br>
                    <small class="text-muted">${offer.description || ''}</small>
                </td>

                <td>
                    ${formatDiscount(offer)}
                </td>

                <td>
                    ₹${Number(offer.minimum_order || 0).toFixed(2)}
                </td>

                <td>
                    ${offer.expiry_date || '-'}
                </td>

                <td>
                    <span class="status-badge ${offer.status === 'active' ? 'active' : 'inactive'}">
                        ${offer.status}
                    </span>
                </td>

                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editOffer(${offer.id})">
                        Edit
                    </button>

                    <button class="btn btn-sm btn-outline-danger" onclick="deleteOffer(${offer.id})">
                        Delete
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function formatDiscount(offer) {
    if (offer.discount_type === 'percentage') {
        let text = `${Number(offer.discount_value).toFixed(0)}% OFF`;

        if (offer.max_discount) {
            text += `<br><small class="text-muted">Max ₹${Number(offer.max_discount).toFixed(2)}</small>`;
        }

        return text;
    }

    return `₹${Number(offer.discount_value).toFixed(2)} OFF`;
}

function editOffer(id) {
    const offer = offers.find(item => Number(item.id) === Number(id));

    if (!offer) {
        showOffersAlert('Offer not found');
        return;
    }

    document.getElementById('offerFormTitle').innerText = 'Edit Offer';
    document.getElementById('offerId').value = offer.id;
    document.getElementById('code').value = offer.code;
    document.getElementById('title').value = offer.title;
    document.getElementById('description').value = offer.description || '';
    document.getElementById('discount_type').value = offer.discount_type;
    document.getElementById('discount_value').value = offer.discount_value;
    document.getElementById('minimum_order').value = offer.minimum_order || 0;
    document.getElementById('max_discount').value = offer.max_discount || '';
    document.getElementById('start_date').value = offer.start_date || '';
    document.getElementById('expiry_date').value = offer.expiry_date || '';
    document.getElementById('status').value = offer.status;
    document.getElementById('first_order_only').checked =
        Number(offer.first_order_only) === 1;

    document.getElementById('offerFormCard').classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function saveOffer(e) {
    e.preventDefault();

    const token = getAdminToken();

    if (!token) {
        showOffersAlert('Admin token missing. Please login again.');
        return;
    }

    const id = document.getElementById('offerId').value;

    const data = {
        code: document.getElementById('code').value.trim(),
        title: document.getElementById('title').value.trim(),
        description: document.getElementById('description').value.trim(),
        discount_type: document.getElementById('discount_type').value,
        discount_value: document.getElementById('discount_value').value,
        minimum_order: document.getElementById('minimum_order').value,
        max_discount: document.getElementById('max_discount').value,
        start_date: document.getElementById('start_date').value,
        expiry_date: document.getElementById('expiry_date').value,
        status: document.getElementById('status').value,
        first_order_only: document.getElementById('first_order_only').checked ? 1 : 0

    };

    if (id) {
        data.id = id;
    }

    if (!data.code || !data.title || !data.discount_value) {
        showOffersAlert('Code, title and discount value are required');
        return;
    }

    const url = id
        ? '../backend/api/offers/update.php'
        : '../backend/api/offers/create.php';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!result.status) {
            showOffersAlert(result.message || 'Save failed');
            return;
        }

        showOffersAlert(result.message, 'success');
        hideOfferForm();
        loadOffers();

    } catch (error) {
        console.log(error);
        showOffersAlert('Save failed');
    }
}

async function deleteOffer(id) {
    const token = getAdminToken();

    if (!token) {
        showOffersAlert('Admin token missing. Please login again.');
        return;
    }

    if (!confirm('Are you sure you want to delete this offer?')) {
        return;
    }

    try {
        const response = await fetch('../backend/api/offers/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ id })
        });

        const result = await response.json();

        if (!result.status) {
            showOffersAlert(result.message || 'Delete failed');
            return;
        }

        showOffersAlert(result.message, 'success');
        loadOffers();

    } catch (error) {
        console.log(error);
        showOffersAlert('Delete failed');
    }
}