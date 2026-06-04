document.addEventListener('DOMContentLoaded', () => {
    updateNavbarUser();
    updateNavbarCounts();
    initHeaderSearch();
});

function updateNavbarUser() {
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    const userDropdown = document.getElementById('userDropdown');
    const loginLink = document.getElementById('loginLink');
    const navUserName = document.getElementById('navUserName');
    if (token && user) {
        if (userDropdown) userDropdown.style.display = 'block';
        if (loginLink) loginLink.style.display = 'none';
        if (navUserName) navUserName.innerText = user.name || 'User';
    } else {
        if (userDropdown) userDropdown.style.display = 'none';
        if (loginLink) loginLink.style.display = 'block';
    }
}

async function updateNavbarCounts() {
    const token = localStorage.getItem('token');
    if (!token) return;
    try {
        const [cartRes, wishRes] = await Promise.allSettled([
            fetch(API_URL + 'cart/view.php', { headers: { Authorization: 'Bearer ' + token } }),
            fetch(API_URL + 'wishlist/view.php', { headers: { Authorization: 'Bearer ' + token } })
        ]);
        if (cartRes.status === 'fulfilled') {
            const cart = await cartRes.value.json();
            const count = (cart.data?.items || []).reduce((sum, item) => sum + Number(item.quantity || 0), 0);
            const el = document.getElementById('navCartCount');
            if (el) el.innerText = count;
        }
        if (wishRes.status === 'fulfilled') {
            const wish = await wishRes.value.json();
            const el = document.getElementById('navWishlistCount');
            if (el) el.innerText = (wish.data || []).length;
        }
    } catch (error) { console.log(error); }
}

function navbarSearch(event) {
    if (event) event.preventDefault();
    const input = document.getElementById('navSearchInput');
    const q = input ? input.value.trim() : '';
    window.location.href = 'product-list.php' + (q ? '?search=' + encodeURIComponent(q) : '');
}

function initHeaderSearch() {
    const form = document.querySelector('.nav-search');
    const input = document.getElementById('navSearchInput');
    if (!form || !input) return;

    let dropdown = document.getElementById('navSearchDropdown');
    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.id = 'navSearchDropdown';
        dropdown.className = 'nav-search-dropdown';
        form.appendChild(dropdown);
    }

    input.addEventListener('focus', async () => {
        await loadSearchCategories(dropdown);
        dropdown.classList.add('show');
    });

    input.addEventListener('input', () => {
        const q = input.value.trim();
        if (q.length > 0) {
            const words = ['women', 'men', 'kids', 'latest', 'new'];
            dropdown.innerHTML = `<div class="search-title">Similar searches</div>` + words.map(w => `<button type="button" onclick="goSearch('${q} for ${w}')"><i class="fa-solid fa-magnifying-glass"></i>${q} for ${w}</button>`).join('');
        } else {
            loadSearchCategories(dropdown);
        }
        dropdown.classList.add('show');
    });

    document.addEventListener('click', e => {
        if (!form.contains(e.target)) dropdown.classList.remove('show');
    });
}

async function loadSearchCategories(dropdown) {
    try {
        const response = await fetch(API_URL + 'categories/list.php');
        const result = await response.json();
        if (!result.status) return;
        buildSearchCategoryDropdown(result.data, dropdown);
    } catch (e) { console.log(e); }
}

function buildSearchCategoryDropdown(categories, targetDropdown) {
    const dropdown = targetDropdown || document.getElementById('navSearchDropdown');
    if (!dropdown || !categories) return;
    const parents = categories.filter(c => !c.parent_id);
    const children = categories.filter(c => c.parent_id);
    let html = '<div class="search-title">Shop by category</div>';
    parents.forEach(parent => {
        html += `<button type="button" class="main-cat" onclick="goCategory(${parent.id})">${parent.name}</button>`;
        children.filter(ch => Number(ch.parent_id) === Number(parent.id)).forEach(ch => {
            html += `<button type="button" class="sub-cat" onclick="goCategory(${ch.id})">— ${ch.name}</button>`;
        });
    });
    dropdown.innerHTML = html;
}

function goSearch(query) { window.location.href = 'product-list.php?search=' + encodeURIComponent(query); }
function goCategory(id) { window.location.href = 'product-list.php?category_id=' + encodeURIComponent(id); }
