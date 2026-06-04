const adminToken =
    localStorage.getItem(
        'admin_token'
    );

if (!adminToken) {

    window.location.href =
        'login.php';
}

let selectedCategoryId = '';

function adminLogout() {

    localStorage.removeItem(
        'admin_token'
    );

    window.location.href =
        'login.php';
}

function showEditProductAlert(
    message,
    type = 'danger'
) {

    const alertBox =
        document.getElementById(
            'editProductAlert'
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
                'editProductForm'
            )
        ) {

            loadProduct();

            document
                .getElementById(
                    'editProductForm'
                )
                .addEventListener(
                    'submit',
                    updateProduct
                );
        }
    }
);

function getProductId() {

    const params =
        new URLSearchParams(
            window.location.search
        );

    return params.get('id');
}

async function loadCategories() {

    try {

        const response =
            await fetch(

                ADMIN_API_URL +
                'categories/list.php'
            );

        const result =
            await response.json();

        if (!result.status) {

            showEditProductAlert(
                result.message
            );

            return;
        }

        const select =
            document.getElementById(
                'editProductCategory'
            );

        select.innerHTML =
            '<option value="">Select Category</option>';

        const parents =
            result.data.filter(
                category => !category.parent_id
            );

        const children =
            result.data.filter(
                category => category.parent_id
            );

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

        if (selectedCategoryId) {

            select.value =
                selectedCategoryId;
        }

    } catch (error) {

        console.log(error);

        showEditProductAlert(
            'Failed to load categories'
        );
    }
}

async function loadProduct() {

    try {

        const productId =
            getProductId();

        if (!productId) {

            showEditProductAlert(
                'Invalid product id'
            );

            return;
        }

        const response =
            await fetch(

                ADMIN_API_URL +
                'products/details.php?id=' +
                productId
            );

        const result =
            await response.json();

        if (!result.status) {

            showEditProductAlert(
                result.message
            );

            return;
        }

        const product =
            result.data;

        const form =
            document.getElementById(
                'editProductForm'
            );

        form.id.value =
            product.id ?? '';

        form.name.value =
            product.name ?? '';

        form.description.value =
            product.description ?? '';

        form.price.value =
            product.price ?? '';

        selectedCategoryId =
            product.category_id ?? '';

        document.getElementById(
            'currentProductImage'
        ).src =
            product.image_url
                ? product.image_url
                : ADMIN_UPLOAD_URL + product.image;

        await loadCategories();

    } catch (error) {

        console.log(error);

        showEditProductAlert(
            'Failed to load product'
        );
    }
}

async function updateProduct(e) {

    e.preventDefault();

    try {

        const form =
            document.getElementById(
                'editProductForm'
            );

        const formData =
            new FormData(form);

        if (!formData.get('id')) {

            showEditProductAlert(
                'Product id missing'
            );

            return;
        }

        if (!formData.get('name').trim()) {

            showEditProductAlert(
                'Product name is required'
            );

            return;
        }

        if (!formData.get('description').trim()) {

            showEditProductAlert(
                'Description is required'
            );

            return;
        }

        if (!formData.get('price') || Number(formData.get('price')) <= 0) {

            showEditProductAlert(
                'Valid price is required'
            );

            return;
        }

        if (!formData.get('category_id')) {

            showEditProductAlert(
                'Please select category'
            );

            return;
        }

        const response =
            await fetch(

                ADMIN_API_URL +
                'products/update.php',

                {

                    method: 'POST',

                    headers: {

                        'Authorization':
                            'Bearer ' + adminToken
                    },

                    body:
                        formData
                }
            );

        const result =
            await response.json();

        if (result.status) {

            showEditProductAlert(
                result.message,
                'success'
            );

            setTimeout(() => {

                window.location.href =
                    'products.php';

            }, 800);

            return;
        }

        showEditProductAlert(
            result.message
        );

    } catch (error) {

        console.log(error);

        showEditProductAlert(
            'Product update failed'
        );
    }
}