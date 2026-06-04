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

function showAddProductAlert(
    message,
    type = 'danger'
) {

    const alertBox =
        document.getElementById(
            'addProductAlert'
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
                'addProductForm'
            )
        ) {

            loadProductCategories();

            document
                .getElementById(
                    'addProductForm'
                )
                .addEventListener(
                    'submit',
                    createProduct
                );
        }
    }
);

async function loadProductCategories() {

    try {

        const response =
            await fetch(

                ADMIN_API_URL +
                'categories/list.php'
            );

        const result =
            await response.json();

        if (!result.status) {

            showAddProductAlert(
                result.message
            );

            return;
        }

        const select =
            document.getElementById(
                'productCategory'
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

                <option
                value="${parent.id}"
                >

                    ${parent.name}

                </option>
            `;

            children
                .filter(child => Number(child.parent_id) === Number(parent.id))
                .forEach(child => {

                    select.innerHTML += `

                        <option
                        value="${child.id}"
                        >

                            -- ${child.name}

                        </option>
                    `;
                });
        });

    } catch (error) {

        console.log(error);

        showAddProductAlert(
            'Failed to load categories'
        );
    }
}

async function createProduct(e) {

    e.preventDefault();

    try {

        const form =
            document.getElementById(
                'addProductForm'
            );

        const formData =
            new FormData(form);

        if (!formData.get('name').trim()) {

            showAddProductAlert(
                'Product name is required'
            );

            return;
        }

        if (!formData.get('description').trim()) {

            showAddProductAlert(
                'Description is required'
            );

            return;
        }

        if (!formData.get('price') || Number(formData.get('price')) <= 0) {

            showAddProductAlert(
                'Valid price is required'
            );

            return;
        }

        if (!formData.get('category_id')) {

            showAddProductAlert(
                'Please select category'
            );

            return;
        }

        if (!formData.get('image[]') || formData.get('image[]').size === 0) {

            showAddProductAlert(
                'Product image is required'
            );

            return;
        }

        const response =
            await fetch(

                ADMIN_API_URL +
                'products/create.php',

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

            showAddProductAlert(
                result.message,
                'success'
            );

            form.reset();

            setTimeout(() => {

                window.location.href =
                    'products.php';

            }, 800);

            return;
        }

        showAddProductAlert(
            result.message
        );

    } catch (error) {

        console.log(error);

        showAddProductAlert(
            'Product create failed'
        );
    }
}