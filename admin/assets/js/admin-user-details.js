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

function showUserDetailsAlert(
    message,
    type = 'danger'
) {

    const alertBox =
        document.getElementById(
            'userDetailsAlert'
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
                'userDetailsContainer'
            )
        ) {

            loadUserDetails();
        }
    }
);

function getUserId() {

    const params =
        new URLSearchParams(
            window.location.search
        );

    return params.get('id');
}

async function loadUserDetails() {

    try {

        const userId =
            getUserId();

        if (!userId) {

            showUserDetailsAlert(
                'Invalid user id'
            );

            return;
        }

        const response =
            await fetch(

                ADMIN_API_URL +
                'admin/user-detail.php?id=' +
                userId,

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

            showUserDetailsAlert(
                result.message
            );

            return;
        }

        const user =
            result.data.user;

        const orders =
            result.data.orders;

        const addresses =
            result.data.addresses;

        let html = `

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <h4>

                                ${user.name ?? '-'}

                            </h4>

                            <p class="mb-1">

                                Email:
                                ${user.email ?? '-'}

                            </p>

                            <p class="mb-1">

                                Phone:
                                ${user.phone ?? '-'}

                            </p>

                            <p class="mb-1">

                                DOB:
                                ${user.dob ?? '-'}

                            </p>

                            <p class="mb-1">

                                Gender:
                                ${user.gender ?? '-'}

                            </p>

                            <p class="mb-1">

                                Joined:
                                ${user.created_at ?? '-'}

                            </p>

                        </div>

                        <div>

                            <span class="badge ${user.status === 'active' ? 'bg-success' : 'bg-danger'} mb-2">

                                ${user.status}

                            </span>

                            <br>

                            <button
                            class="btn btn-sm ${user.status === 'active' ? 'btn-danger' : 'btn-success'}"
                            onclick="toggleUserStatus(${user.id}, '${user.status}')"
                            >

                                ${user.status === 'active' ? 'Block User' : 'Unblock User'}

                            </button>

                        </div>

                    </div>

                </div>

            </div>

            <h4 class="mb-3">

                Addresses

            </h4>
        `;

        if (addresses.length === 0) {

            html += `

                <div class="alert alert-warning">

                    No addresses found

                </div>
            `;

        } else {

            html += `<div class="row">`;

            addresses.forEach(address => {

                html += `

                    <div class="col-md-4 mb-3">

                        <div class="card h-100">

                            <div class="card-body">

                                <h6>

                                    ${address.label ?? address.address_type ?? 'Address'}

                                </h6>

                                <p class="mb-1">

                                    ${address.fullname ?? '-'}

                                </p>

                                <p class="mb-1">

                                    ${address.phone ?? '-'}

                                </p>

                                <p class="mb-1">

                                    ${address.address_line ?? '-'}

                                </p>

                                <p class="mb-1">

                                    ${address.city ?? '-'},
                                    ${address.state ?? '-'}

                                </p>

                                <p class="mb-1">

                                    ${address.pincode ?? '-'}

                                </p>

                                <p class="mb-0">

                                    ${address.country ?? 'India'}

                                </p>

                            </div>

                        </div>

                    </div>
                `;
            });

            html += `</div>`;
        }

        html += `

            <h4 class="mb-3 mt-4">

                Orders

            </h4>
        `;

        if (orders.length === 0) {

            html += `

                <div class="alert alert-warning">

                    No orders found

                </div>
            `;

        } else {

            html += `

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead>

                            <tr>

                                <th>Order ID</th>

                                <th>Total</th>

                                <th>Status</th>

                                <th>Payment</th>

                                <th>Date</th>

                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>
            `;

            orders.forEach(order => {

                html += `

                    <tr>

                        <td>#${order.id}</td>

                        <td>₹${Number(order.total).toFixed(2)}</td>

                        <td>${order.status}</td>

                        <td>${order.payment_method}</td>

                        <td>${order.created_at}</td>

                        <td>

                            <a
                            href="order-details.php?id=${order.id}"
                            class="btn btn-sm btn-dark"
                            >

                                View

                            </a>

                        </td>

                    </tr>
                `;
            });

            html += `

                        </tbody>

                    </table>

                </div>
            `;
        }

        document.getElementById(
            'userDetailsContainer'
        ).innerHTML = html;

    } catch (error) {

        console.log(error);

        showUserDetailsAlert(
            'Failed to load user details'
        );
    }
}

async function toggleUserStatus(
    userId,
    currentStatus
) {

    const newStatus =
        currentStatus === 'active'
            ? 'blocked'
            : 'active';

    if (!confirm('Are you sure?')) {
        return;
    }

    try {

        const response =
            await fetch(

                ADMIN_API_URL +
                'admin/update-user-status.php',

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

                            user_id: userId,

                            status: newStatus
                        })
                }
            );

        const result =
            await response.json();

        if (result.status) {

            showUserDetailsAlert(
                result.message,
                'success'
            );

            loadUserDetails();

            return;
        }

        showUserDetailsAlert(
            result.message
        );

    } catch (error) {

        console.log(error);

        showUserDetailsAlert(
            'Failed to update user status'
        );
    }
}