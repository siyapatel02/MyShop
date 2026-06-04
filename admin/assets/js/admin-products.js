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
let currentCategory = '';
let productLimit = 8;

function showProductsAlert(
    message,
    type = 'danger'
) {

    const alertBox =
        document.getElementById(
            'productsAlert'
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

function adminLogout() {

    localStorage.removeItem(
        'admin_token'
    );

    window.location.href =
        'login.php';
}

document.addEventListener(
    'DOMContentLoaded',
    () => {

        if (
            document.getElementById(
                'adminProductsTable'
            )
        ) {

            loadCategoryFilter();

            loadAdminProducts();
        }
    }
);

async function loadCategoryFilter() {

    try {

        const response =
            await fetch(

                ADMIN_API_URL +
                'categories/list.php'
            );

        const result =
            await response.json();

        if (!result.status) {
            return;
        }

        const select =
            document.getElementById(
                'productCategoryFilter'
            );

        if (!select) {
            return;
        }

        const parents =
            result.data.filter(
                category => !category.parent_id
            );

        const children =
            result.data.filter(
                category => category.parent_id
            );

        select.innerHTML =
            '<option value="">All Categories</option>';

        parents.forEach(parent => {

            select.innerHTML += `

                <option value="${parent.id}">

                    ${parent.name}

                </option>
            `;

            children
                .filter(child => Number(child.parent_id) === Number(parent.id))
                .forEach(child => {

                    select.innerHTML += `

                        <option value="${child.id}">

                            -- ${child.name}

                        </option>
                    `;
                });
        });

    } catch (error) {

        console.log(error);
    }
}

function applyProductFilters() {

    const searchInput =
        document.getElementById(
            'productSearch'
        );

    const categorySelect =
        document.getElementById(
            'productCategoryFilter'
        );

    currentSearch =
        searchInput
            ? searchInput.value.trim()
            : '';

    currentCategory =
        categorySelect
            ? categorySelect.value
            : '';

    currentPage = 1;

    loadAdminProducts();
}

async function loadAdminProducts(
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
            productLimit
        );

        if (currentSearch) {

            params.append(
                'search',
                currentSearch
            );
        }

        if (currentCategory) {

            params.append(
                'category_id',
                currentCategory
            );
        }

        const response =
            await fetch(

                ADMIN_API_URL +
                'products/list.php?' +
                params.toString()
            );

        const result =
            await response.json();

        console.log(result);

        const table =
            document.getElementById(
                'adminProductsTable'
            );

        table.innerHTML = '';

        if (!result.status) {

            showProductsAlert(
                result.message
            );

            return;
        }

        const products =
            result.data.products;

        const pagination =
            result.data.pagination;

        if (products.length === 0) {

            table.innerHTML = `

                <tr>

                    <td colspan="6" class="text-center">

                        No products found

                    </td>

                </tr>
            `;

            renderProductsPagination(
                pagination
            );

            return;
        }

        products.forEach(product => {

            table.innerHTML += `

                <tr>

                    <td>${product.id}</td>

                    <td>

                        <img
                        src="${product.image}"
                        width="70"
                        height="70"
                        style="object-fit:cover;"
                        >

                    </td>

                    <td>${product.name}</td>

                    <td>₹${Number(product.price).toFixed(2)}</td>

                    <td>${product.category_name ?? '-'}</td>

                    <td>

                        <a
                        href="edit-product.php?id=${product.id}"
                        class="btn btn-sm btn-primary"
                        >

                            Edit

                        </a>

                        <button
                        class="btn btn-sm btn-danger"
                        onclick="deleteProduct(${product.id})"
                        >

                            Delete

                        </button>

                    </td>

                </tr>
            `;
        });

        renderProductsPagination(
            pagination
        );

    } catch (error) {

        console.log(error);

        showProductsAlert(
            'Failed to load products'
        );
    }
}

function renderProductsPagination(
    pagination
) {

    const container =
        document.getElementById(
            'productsPagination'
        );

    if (!container) {
        return;
    }

    container.innerHTML = '';

    if (!pagination || pagination.total_pages <= 1) {
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
            onclick="loadAdminProducts(${i})"
            >

                ${i}

            </button>
        `;
    }
}

async function deleteProduct(
    productId
) {

    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }

    try {

        const response =
            await fetch(

                ADMIN_API_URL +
                'products/delete.php',

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

                            id: productId
                        })
                }
            );

        const result =
            await response.json();

        if (result.status) {

            showProductsAlert(
                result.message,
                'success'
            );

            loadAdminProducts(
                currentPage
            );

            return;
        }

        showProductsAlert(
            result.message
        );

    } catch (error) {

        console.log(error);

        showProductsAlert(
            'Delete failed'
        );
    }
}