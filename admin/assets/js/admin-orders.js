const adminToken =
    localStorage.getItem(
        'admin_token'
    );

if (!adminToken) {

    window.location.href =
        'login.php';
}

let currentPage = 1;
let currentSearch = '';
let currentStatus = '';
let orderLimit = 10;

function adminLogout() {

    localStorage.removeItem(
        'admin_token'
    );

    window.location.href =
        'login.php';
}

function showAdminOrdersAlert(
    message,
    type = 'danger'
) {

    const alertBox =
        document.getElementById(
            'adminOrdersAlert'
        );

    if (!alertBox) {
        return;
    }

    alertBox.innerHTML = `

        <div class="alert alert-${type}">

            ${message}

        </div>
    `;
}

document.addEventListener(
    'DOMContentLoaded',
    () => {

        if (
            document.getElementById(
                'adminOrdersTable'
            )
        ) {

            loadAdminOrders();
        }
    }
);

function applyOrderFilters() {

    currentSearch =
        document.getElementById(
            'orderSearch'
        ).value.trim();

    currentStatus =
        document.getElementById(
            'orderStatusFilter'
        ).value;

    currentPage = 1;

    loadAdminOrders();
}

async function loadAdminOrders(
    page = currentPage
) {

    try {

        currentPage =
            page;

        const params =
            new URLSearchParams();

        params.append(
            'page',
            currentPage
        );

        params.append(
            'limit',
            orderLimit
        );

        if (currentSearch) {

            params.append(
                'search',
                currentSearch
            );
        }

        if (currentStatus) {

            params.append(
                'status',
                currentStatus
            );
        }

        const response =
            await fetch(

                ADMIN_API_URL +
                'admin/orders.php?' +
                params.toString(),

                {

                    method: 'GET',

                    headers: {

                        'Authorization':
                            'Bearer ' + adminToken
                    }
                }
            );

        const result =
            await response.json();

        console.log(result);

        const table =
            document.getElementById(
                'adminOrdersTable'
            );

        table.innerHTML = '';

        if (!result.status) {

            showAdminOrdersAlert(
                result.message
            );

            return;
        }

        const orders =
            result.data.orders;

        const pagination =
            result.data.pagination;

        if (orders.length === 0) {

            table.innerHTML = `

                <tr>

                    <td colspan="8" class="text-center">

                        No orders found

                    </td>

                </tr>
            `;

            renderOrdersPagination(
                pagination
            );

            return;
        }

        orders.forEach(order => {

            table.innerHTML += `

                <tr>

                    <td>#${order.id}</td>

                    <td>${order.customer_name ?? '-'}</td>

                    <td>${order.customer_email ?? '-'}</td>

                    <td>₹${Number(order.total).toFixed(2)}</td>

                    <td>${order.payment_method}</td>

                    <td>

                        <select
                        class="form-select form-select-sm"
                        onchange="updateOrderStatus(${order.id}, this.value)"
                        >

                            <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>
                                Pending
                            </option>

                            <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>
                                Shipped
                            </option>

                            <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>
                                Delivered
                            </option>

                        </select>

                    </td>

                    <td>${order.created_at}</td>

                    <td>

                        <a
                        href="order-details.php?id=${order.id}"
                        class="btn btn-sm btn-dark"
                        target="_blank"
                        >

                            View

                        </a>

                    </td>

                </tr>
            `;
        });

        renderOrdersPagination(
            pagination
        );

    } catch (error) {

        console.log(error);

        showAdminOrdersAlert(
            'Failed to load orders'
        );
    }
}

function renderOrdersPagination(
    pagination
) {

    const container =
        document.getElementById(
            'ordersPagination'
        );

    if (!container) {
        return;
    }

    container.innerHTML = '';

    if (
        !pagination ||
        pagination.total_pages <= 1
    ) {
        return;
    }

    for (
        let i = 1;
        i <= pagination.total_pages;
        i++
    ) {

        container.innerHTML += `

            <button
            class="btn btn-sm ${Number(pagination.page) === i ? 'btn-dark' : 'btn-outline-dark'}"
            onclick="loadAdminOrders(${i})"
            >

                ${i}

            </button>
        `;
    }
}

async function updateOrderStatus(
    orderId,
    status
) {

    try {

        const response =
            await fetch(

                ADMIN_API_URL +
                'admin/update-order-status.php',

                {

                    method: 'POST',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Authorization':
                            'Bearer ' + adminToken
                    },

                    body:
                        JSON.stringify({

                            order_id: orderId,

                            status: status
                        })
                }
            );

        const result =
            await response.json();

        if (result.status) {

            showAdminOrdersAlert(
                result.message,
                'success'
            );

            loadAdminOrders(
                currentPage
            );

            return;
        }

        showAdminOrdersAlert(
            result.message
        );

    } catch (error) {

        console.log(error);

        showAdminOrdersAlert(
            'Failed to update status'
        );
    }
}