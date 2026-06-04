<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin-layout.css?v=2">

<script>
document.body.classList.add('admin-body');
if (localStorage.getItem('myshop_admin_theme') === 'dark') {
    document.body.classList.add('admin-dark');
}
if (!localStorage.getItem('admin_token') && !window.location.pathname.endsWith('/admin/login.php')) {
    window.location.href = 'login.php';
}
function adminLogout() {
    localStorage.removeItem('admin_token');
    window.location.href = 'login.php';
}
</script>

<div class="admin-layout">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <div class="admin-brand-mark">M</div>
            <div class="admin-brand-text">
                <strong>MyShop</strong>
                <span>Admin Workspace</span>
            </div>
        </div>

        <nav class="admin-menu">
            <a class="admin-link" href="dashboard.php" data-page="dashboard.php"><span class="admin-icon">▦</span><span>Dashboard</span></a>
            <a class="admin-link" href="products.php" data-page="products.php"><span class="admin-icon">▣</span><span>Products</span></a>
            <a class="admin-link" href="add-product.php" data-page="add-product.php"><span class="admin-icon">＋</span><span>Add Product</span></a>
            <a class="admin-link" href="categories.php" data-page="categories.php"><span class="admin-icon">☰</span><span>Categories</span></a>
            <a class="admin-link" href="orders.php" data-page="orders.php"><span class="admin-icon">⌁</span><span>Orders</span></a>
            <a class="admin-link" href="users.php" data-page="users.php"><span class="admin-icon">◉</span><span>Users</span></a>
            <a class="admin-link" href="reviews.php" data-page="reviews.php"><span class="admin-icon">★</span><span>Reviews</span></a>
            <a class="admin-link" href="offers.php" data-page="offers.php"><span class="admin-icon">%</span><span>Offers</span></a>
            <a class="admin-link" href="banners.php" data-page="banners.php"><span class="admin-icon">▤</span><span>Banners</span></a>
        </nav>

        <div class="admin-sidebar-footer">
            <button class="admin-logout" type="button" onclick="adminLogout()"><span class="admin-icon">↩</span><span>Logout</span></button>
        </div>
    </aside>

    <main class="admin-main" id="adminMain">
        <div class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="admin-toggle" onclick="toggleAdminSidebar()" title="Toggle sidebar">☰</button>
                <div>
                    <h1 class="admin-page-title" id="adminTopTitle">Admin Panel</h1>
                    <p class="admin-page-subtitle">Manage store data and daily operations</p>
                </div>
            </div>
            <div class="admin-top-actions">
                <button type="button" class="admin-export-btn" onclick="exportAdminPage()">⇩ <span>Export</span></button>
                <button type="button" class="admin-theme-toggle" id="adminThemeToggle" onclick="toggleAdminTheme()">🌙 <span>Dark</span></button>
            </div>
        </div>

<script src="assets/js/admin-ui.js?v=2"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const current = window.location.pathname.split('/').pop();
    document.querySelectorAll('.admin-link').forEach(link => {
        if (link.dataset.page === current) link.classList.add('active');
    });
});
</script>
