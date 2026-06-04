const adminToken =
    localStorage.getItem(
        'admin_token'
    );

if (!adminToken) {

    window.location.href =
        'login.php';
}

let categories = [];

function adminLogout() {

    localStorage.removeItem(
        'admin_token'
    );

    window.location.href =
        'login.php';
}

function showCategoryAlert(
    message,
    type = 'danger'
) {

    const alertBox =
        document.getElementById(
            'categoryAlert'
        );

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
                'categoriesTable'
            )
        ) {

            loadCategories();

            document
                .getElementById(
                    'categoryForm'
                )
                .addEventListener(
                    'submit',
                    saveCategory
                );
        }
    }
);

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

            showCategoryAlert(
                result.message
            );

            return;
        }

        categories =
            result.data;

        renderParentDropdown();

        renderCategoriesTable();

    } catch (error) {

        console.log(error);

        showCategoryAlert(
            'Failed to load categories'
        );
    }
}

function renderParentDropdown() {

    const select =
        document.getElementById(
            'parentCategory'
        );

    select.innerHTML =
        '<option value="">No Parent</option>';

    categories
        .filter(category => !category.parent_id)
        .forEach(category => {

            select.innerHTML += `

                <option value="${category.id}">

                    ${category.name}

                </option>
            `;
        });
}

function getParentName(
    parentId
) {

    if (!parentId) {
        return '-';
    }

    const parent =
        categories.find(
            category => Number(category.id) === Number(parentId)
        );

    return parent
        ? parent.name
        : '-';
}

function renderCategoriesTable() {

    const table =
        document.getElementById(
            'categoriesTable'
        );

    table.innerHTML = '';

    if (categories.length === 0) {

        table.innerHTML = `

            <tr>

                <td colspan="6" class="text-center">

                    No categories found

                </td>

            </tr>
        `;

        return;
    }

    categories.forEach(category => {

        table.innerHTML += `

            <tr>

                <td>${category.id}</td>

                <td>${category.name}</td>

                <td>${category.slug}</td>

                <td>${getParentName(category.parent_id)}</td>

                <td>

                    <span class="badge ${category.status === 'active' ? 'bg-success' : 'bg-secondary'}">

                        ${category.status}

                    </span>

                </td>

                <td>

                    <button
                    class="btn btn-sm btn-primary"
                    onclick="editCategory(${category.id})"
                    >

                        Edit

                    </button>

                    <button
                    class="btn btn-sm btn-danger"
                    onclick="deleteCategory(${category.id})"
                    >

                        Delete

                    </button>

                </td>

            </tr>
        `;
    });
}

async function saveCategory(e) {

    e.preventDefault();

    const form =
        document.getElementById(
            'categoryForm'
        );

    const id =
        form.id.value;

    const data = {

        name:
            form.name.value.trim(),

        parent_id:
            form.parent_id.value || null,

        status:
            form.status.value
    };

    if (!data.name) {

        showCategoryAlert(
            'Category name is required'
        );

        return;
    }

    if (id) {

        data.id =
            Number(id);
    }

    const url =
        id
            ? 'admin/categories/update.php'
            : 'admin/categories/create.php';

    try {

        const response =
            await fetch(

                ADMIN_API_URL +
                url,

                {

                    method: 'POST',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Authorization':
                            'Bearer ' + adminToken
                    },

                    body:
                        JSON.stringify(data)
                }
            );

        const result =
            await response.json();

        if (result.status) {

            showCategoryAlert(
                result.message,
                'success'
            );

            resetCategoryForm();

            loadCategories();

            return;
        }

        showCategoryAlert(
            result.message
        );

    } catch (error) {

        console.log(error);

        showCategoryAlert(
            'Category save failed'
        );
    }
}

function editCategory(
    id
) {

    const category =
        categories.find(
            item => Number(item.id) === Number(id)
        );

    if (!category) {
        return;
    }

    const form =
        document.getElementById(
            'categoryForm'
        );

    form.id.value =
        category.id;

    form.name.value =
        category.name;

    form.parent_id.value =
        category.parent_id || '';

    form.status.value =
        category.status;

    document.getElementById(
        'formTitle'
    ).innerText =
        'Edit Category';

    document.getElementById(
        'submitBtn'
    ).innerText =
        'Update';
}

async function deleteCategory(
    id
) {

    if (!confirm('Are you sure you want to delete this category?')) {
        return;
    }

    try {

        const response =
            await fetch(

                ADMIN_API_URL +
                'admin/categories/delete.php',

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
                            id: id
                        })
                }
            );

        const result =
            await response.json();

        if (result.status) {

            showCategoryAlert(
                result.message,
                'success'
            );

            loadCategories();

            return;
        }

        showCategoryAlert(
            result.message
        );

    } catch (error) {

        console.log(error);

        showCategoryAlert(
            'Category delete failed'
        );
    }
}

function resetCategoryForm() {

    const form =
        document.getElementById(
            'categoryForm'
        );

    form.reset();

    form.id.value =
        '';

    document.getElementById(
        'formTitle'
    ).innerText =
        'Add Category';

    document.getElementById(
        'submitBtn'
    ).innerText =
        'Save';
}