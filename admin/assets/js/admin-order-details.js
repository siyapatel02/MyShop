const adminToken =
    localStorage.getItem(
        'admin_token'
    );

if (!adminToken) {

    window.location.href =
        'login.php';
}

function adminLogout() {

    localStorage.removeItem(
        'admin_token'
    );

    window.location.href =
        'login.php';
}

function showAdminOrderDetailsAlert(
    message,
    type = 'danger'
) {

    const alertBox =
        document.getElementById(
            'adminOrderDetailsAlert'
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
                'adminOrderDetailsContainer'
            )
        ) {

            loadAdminOrderDetails();
        }
    }
);

function getOrderId() {

    const params =
        new URLSearchParams(
            window.location.search
        );

    return params.get('id');
}

async function loadAdminOrderDetails() {

    try {

        const orderId =
            getOrderId();

        if (!orderId) {

            showAdminOrderDetailsAlert(
                'Invalid order id'
            );

            return;
        }

        const response =
            await fetch(

                ADMIN_API_URL +
                'admin/order-detail.php?id=' +
                orderId,

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

        if (!result.status) {

            showAdminOrderDetailsAlert(
                result.message
            );

            return;
        }

        const order =
            result.data.order;

        const items =
            result.data.items;

        let html = `

            <div class="card mb-4 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <h4>

                                Order #${order.id}

                            </h4>

                            <p class="mb-1">

                                Customer:
                                ${order.customer_name ?? '-'}

                            </p>

                            <p class="mb-1">

                                Email:
                                ${order.customer_email ?? '-'}

                            </p>

                            <p class="mb-1">

                                Payment:
                                ${order.payment_method}

                            </p>

                            <p class="mb-1">

                                Address:
                                ${order.address}

                            </p>

                            <p class="mb-1">

                                Date:
                                ${order.created_at}

                            </p>

                        </div>

                        <div style="min-width:180px;">

                            <label class="form-label">

                                Status

                            </label>

                            <select
                            class="form-select"
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

                        </div>

                    </div>

                    <hr>

                    <h5>

                        Total:
                        ₹${Number(order.total).toFixed(2)}

                    </h5>

                </div>

            </div>

            <h4 class="mb-3">

                Items

            </h4>

            <div class="row">
        `;

        items.forEach(item => {

            html += `

                <div class="col-md-4 mb-4">

                    <div class="card h-100 shadow-sm">

                        <img
                        src="${item.image_url}"
                        class="card-img-top"
                        height="220"
                        style="object-fit:cover;"
                        >

                        <div class="card-body">

                            <h5>

                                ${item.name ?? 'Product removed'}

                            </h5>

                            <p>

                                Price:
                                ₹${Number(item.price).toFixed(2)}

                            </p>

                            <p>

                                Quantity:
                                ${item.quantity}

                            </p>

                            <p class="fw-bold">

                                Total:
                                ₹${Number(item.line_total).toFixed(2)}

                            </p>

                        </div>

                    </div>

                </div>
            `;
        });

        html += `</div>`;

        document.getElementById(
            'adminOrderDetailsContainer'
        ).innerHTML = html;

    } catch (error) {

        console.log(error);

        showAdminOrderDetailsAlert(
            'Failed to load order details'
        );
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

            showAdminOrderDetailsAlert(
                result.message,
                'success'
            );

            loadAdminOrderDetails();

            return;
        }

        showAdminOrderDetailsAlert(
            result.message
        );

    } catch (error) {

        console.log(error);

        showAdminOrderDetailsAlert(
            'Failed to update order status'
        );
    }
}