let banners = [];

document.addEventListener('DOMContentLoaded', () => {
    loadBanners();

    document.getElementById('newBannerBtn').addEventListener('click', showCreateBannerForm);
    document.getElementById('cancelBannerBtn').addEventListener('click', hideBannerForm);
    document.getElementById('bannerForm').addEventListener('submit', saveBanner);
});

function getAdminToken() {
    return localStorage.getItem('admin_token') || localStorage.getItem('token');
}

function showBannersAlert(message, type = 'danger') {
    const box = document.getElementById('bannersAlert');

    box.innerHTML = `
        <div class="alert alert-${type}">
            ${message}
        </div>
    `;

    setTimeout(() => {
        box.innerHTML = '';
    }, 3000);
}

function showCreateBannerForm() {
    document.getElementById('bannerFormTitle').innerText = 'Add New Banner';
    document.getElementById('bannerForm').reset();
    document.getElementById('bannerId').value = '';
    document.getElementById('bannerPreviewBox').classList.add('d-none');
    document.getElementById('bannerFormCard').classList.remove('d-none');
}

function hideBannerForm() {
    document.getElementById('bannerFormCard').classList.add('d-none');
    document.getElementById('bannerForm').reset();
    document.getElementById('bannerId').value = '';
    document.getElementById('bannerPreviewBox').classList.add('d-none');
}

async function loadBanners() {
    try {
        const response = await fetch('../backend/api/banners/list.php');
        const result = await response.json();

        if (!result.status) {
            showBannersAlert(result.message || 'Failed to load banners');
            return;
        }

        banners = result.data || [];
        renderBanners();

    } catch (error) {
        console.log(error);
        showBannersAlert('Failed to load banners');
    }
}

function renderBanners() {
    const list = document.getElementById('bannersList');

    if (!banners.length) {
        list.innerHTML = `
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    No banners found
                </div>
            </div>
        `;
        return;
    }

    let html = '';

    banners.forEach(banner => {
        html += `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="banner-card">
                    <img 
                        src="${banner.image_url}" 
                        class="banner-card-img"
                        alt="${banner.title}"
                    >

                    <div class="banner-card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <h5>${banner.title}</h5>
                                <p>${banner.subtitle || ''}</p>
                            </div>

                            <span class="status-badge ${banner.status === 'active' ? 'active' : 'inactive'}">
                                ${banner.status}
                            </span>
                        </div>

                        <div class="banner-meta">
                            <small>Button: ${banner.button_text || '-'}</small>
                            <small>Link: ${banner.button_link || '-'}</small>
                        </div>

                        <div class="banner-actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="editBanner(${banner.id})">
                                Edit
                            </button>

                            <button class="btn btn-sm btn-outline-danger" onclick="deleteBanner(${banner.id})">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    list.innerHTML = html;
}

function editBanner(id) {
    const banner = banners.find(item => Number(item.id) === Number(id));

    if (!banner) {
        showBannersAlert('Banner not found');
        return;
    }

    document.getElementById('bannerFormTitle').innerText = 'Edit Banner';
    document.getElementById('bannerId').value = banner.id;
    document.getElementById('title').value = banner.title;
    document.getElementById('subtitle').value = banner.subtitle || '';
    document.getElementById('button_text').value = banner.button_text || '';
    document.getElementById('button_link').value = banner.button_link || '';
    document.getElementById('status').value = banner.status;

    document.getElementById('bannerPreview').src = banner.image_url;
    document.getElementById('bannerPreviewBox').classList.remove('d-none');

    document.getElementById('bannerFormCard').classList.remove('d-none');

    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

async function saveBanner(e) {
    e.preventDefault();

    const token = getAdminToken();

    if (!token) {
        showBannersAlert('Admin token missing. Please login again.');
        return;
    }

    const id = document.getElementById('bannerId').value;
    const imageFile = document.getElementById('image').files[0];

    if (!document.getElementById('title').value.trim()) {
        showBannersAlert('Title is required');
        return;
    }

    if (!id && !imageFile) {
        showBannersAlert('Banner image is required');
        return;
    }

    const formData = new FormData();

    if (id) {
        formData.append('id', id);
    }

    formData.append('title', document.getElementById('title').value.trim());
    formData.append('subtitle', document.getElementById('subtitle').value.trim());
    formData.append('button_text', document.getElementById('button_text').value.trim());
    formData.append('button_link', document.getElementById('button_link').value.trim());
    formData.append('status', document.getElementById('status').value);

    if (imageFile) {
        formData.append('image', imageFile);
    }

    const url = id
        ? '../backend/api/banners/update.php'
        : '../backend/api/banners/create.php';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            body: formData
        });

        const result = await response.json();

        if (!result.status) {
            showBannersAlert(result.message || 'Save failed');
            return;
        }

        showBannersAlert(result.message, 'success');
        hideBannerForm();
        loadBanners();

    } catch (error) {
        console.log(error);
        showBannersAlert('Save failed');
    }
}

async function deleteBanner(id) {
    const token = getAdminToken();

    if (!token) {
        showBannersAlert('Admin token missing. Please login again.');
        return;
    }

    if (!confirm('Are you sure you want to delete this banner?')) {
        return;
    }

    try {
        const response = await fetch('../backend/api/banners/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ id })
        });

        const result = await response.json();

        if (!result.status) {
            showBannersAlert(result.message || 'Delete failed');
            return;
        }

        showBannersAlert(result.message, 'success');
        loadBanners();

    } catch (error) {
        console.log(error);
        showBannersAlert('Delete failed');
    }
}