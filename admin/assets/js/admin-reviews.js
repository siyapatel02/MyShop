let allAdminReviews = [];
let currentReviewFilter = 'all';

document.addEventListener('DOMContentLoaded', () => {
    loadAdminReviews();
});

function showAdminReviewAlert(message, type = 'success') {

    const alertBox = document.getElementById('reviewAdminAlert');

    if (!alertBox) {
        return;
    }

    alertBox.innerHTML = `
        <div class="alert alert-${type}">
            ${message}
        </div>
    `;

    setTimeout(() => {
        alertBox.innerHTML = '';
    }, 3000);
}

async function loadAdminReviews() {

    const token = localStorage.getItem('admin_token');

    if (!token) {
        alert('Please login as admin');
        window.location.href = 'login.php';
        return;
    }

    try {
        const response = await fetch(
            API_URL + 'reviews/admin-list.php',
            {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            }
        );

        const result = await response.json();

        if (!result.status) {
            showAdminReviewAlert(result.message, 'danger');
            return;
        }

        allAdminReviews = result.data;

        renderAdminReviews();

    } catch (error) {
        console.log(error);
        showAdminReviewAlert('Failed to load reviews', 'danger');
    }
}

function filterAdminReviews(status) {

    currentReviewFilter = status;

    renderAdminReviews();
}

function renderAdminReviews() {

    const table = document.getElementById('adminReviewsTable');

    if (!table) {
        return;
    }

    let reviews = allAdminReviews;

    if (currentReviewFilter !== 'all') {
        reviews = allAdminReviews.filter(
            review => review.status === currentReviewFilter
        );
    }

    if (reviews.length === 0) {
        table.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">
                    No reviews found
                </td>
            </tr>
        `;
        return;
    }

    let html = '';

    reviews.forEach(review => {

        let badgeClass = 'bg-warning';

        if (review.status === 'active') {
            badgeClass = 'bg-success';
        }

        if (review.status === 'hidden') {
            badgeClass = 'bg-secondary';
        }

        html += `
            <tr>
                <td>${review.user_name ?? ''}</td>

                <td>${review.product_name ?? ''}</td>

                <td>
                    <span class="text-warning">
                        ${'★'.repeat(Number(review.rating))}
                    </span>
                    <span class="text-muted">
                        ${'☆'.repeat(5 - Number(review.rating))}
                    </span>
                </td>

                <td>${review.review ?? ''}</td>

                <td>
                    <span class="badge ${badgeClass}">
                        ${review.status}
                    </span>
                </td>

                <td>${review.created_at}</td>

                <td>
                    <button
                        class="btn btn-success btn-sm"
                        onclick="updateReviewStatus(${review.id}, 'active')"
                    >
                        Approve
                    </button>

                    <button
                        class="btn btn-secondary btn-sm"
                        onclick="updateReviewStatus(${review.id}, 'hidden')"
                    >
                        Hide
                    </button>
                </td>
            </tr>
        `;
    });

    table.innerHTML = html;
}

async function updateReviewStatus(reviewId, status) {

    const token = localStorage.getItem('admin_token');

    if (!token) {
        alert('Please login as admin');
        window.location.href = 'login.php';
        return;
    }

    try {
        const response = await fetch(
            API_URL + 'reviews/update-status.php',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({
                    review_id: reviewId,
                    status: status
                })
            }
        );

        const result = await response.json();

        showAdminReviewAlert(
            result.message,
            result.status ? 'success' : 'danger'
        );

        if (result.status) {
            loadAdminReviews();
        }

    } catch (error) {
        console.log(error);
        showAdminReviewAlert('Failed to update review', 'danger');
    }
}