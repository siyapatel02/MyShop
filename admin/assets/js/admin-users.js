const adminToken = localStorage.getItem('admin_token');

if (!adminToken) {
    window.location.href = 'login.php';
}

let currentPage = 1;
let currentSearch = '';
let currentStatus = '';
let userLimit = 10;

function adminLogout() {
    localStorage.removeItem('admin_token');
    window.location.href = 'login.php';
}

function showAdminUsersAlert(message, type = 'danger') {
    const alertBox = document.getElementById('adminUsersAlert');

    if (!alertBox) {
        return;
    }

    alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('adminUsersTable')) {
        loadAdminUsers();
    }
});

function applyUserFilters() {
    currentSearch = document.getElementById('userSearch').value.trim();
    currentStatus = document.getElementById('userStatusFilter').value;
    currentPage = 1;

    loadAdminUsers();
}

async function loadAdminUsers(page = currentPage) {

    try {
        currentPage = page;

        const params = new URLSearchParams();

        params.append('page', currentPage);
        params.append('limit', userLimit);

        if (currentSearch) {
            params.append('search', currentSearch);
        }

        if (currentStatus) {
            params.append('status', currentStatus);
        }

        const response = await fetch(
            ADMIN_API_URL + 'admin/users.php?' + params.toString(),
            {
                method: 'GET',
                headers: {
                    Authorization: 'Bearer ' + adminToken
                }
            }
        );

        const result = await response.json();

        console.log(result);

        if (!result.status) {
            showAdminUsersAlert(result.message);
            return;
        }

        const users = result.data.users;
        const pagination = result.data.pagination;

        document.getElementById('totalUsersCount').innerText =
            pagination.total_records;

        const table = document.getElementById('adminUsersTable');

        table.innerHTML = '';

        if (users.length === 0) {
            table.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center">No users found</td>
                </tr>
            `;

            renderUsersPagination(pagination);
            return;
        }

        users.forEach(user => {
            table.innerHTML += `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.name ?? '-'}</td>
                    <td>${user.email ?? '-'}</td>
                    <td>${user.phone ?? '-'}</td>
                    <td>${user.dob ?? '-'}</td>
                    <td>${user.gender ?? '-'}</td>
                    <td>${user.total_orders ?? 0}</td>
                    <td>₹${Number(user.total_spent).toFixed(2)}</td>
                    <td>${user.created_at ?? '-'}</td>
                    <td>
                        <a href="user-details.php?id=${user.id}" class="btn btn-sm btn-dark">
                            View
                        </a>
                    </td>
                </tr>
            `;
        });

        renderUsersPagination(pagination);

    } catch (error) {
        console.log(error);
        showAdminUsersAlert('Failed to load users');
    }
}

function renderUsersPagination(pagination) {
    const container = document.getElementById('usersPagination');

    if (!container) {
        return;
    }

    container.innerHTML = '';

    if (!pagination || pagination.total_pages <= 1) {
        return;
    }

    for (let i = 1; i <= pagination.total_pages; i++) {
        container.innerHTML += `
            <button
                class="btn btn-sm ${Number(pagination.page) === i ? 'btn-dark' : 'btn-outline-dark'}"
                onclick="loadAdminUsers(${i})"
            >
                ${i}
            </button>
        `;
    }
}