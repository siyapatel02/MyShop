<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$isPagesFolder = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$basePath = $isPagesFolder ? '../' : '';
?>

<nav class="navbar navbar-expand-lg main-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= $basePath ?>pages/home.php">
            <span class="brand-mark">M</span> MyShop
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="d-flex ms-lg-4 my-3 my-lg-0 nav-search" onsubmit="navbarSearch(event)">
                <input type="text" id="navSearchInput" class="form-control" placeholder="Search dresses, shoes, sarees...">
                <button class="btn btn-brand nav-search-btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>

            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'home.php' ? 'active' : '' ?>" href="<?= $basePath ?>pages/home.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'cart.php' ? 'active' : '' ?>" href="<?= $basePath ?>pages/cart.php"><i class="fa-solid fa-bag-shopping me-1"></i>Cart <span id="navCartCount" class="badge nav-badge">0</span></a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'wishlist.php' ? 'active' : '' ?>" href="<?= $basePath ?>pages/wishlist.php"><i class="fa-regular fa-heart me-1"></i>Wishlist <span id="navWishlistCount" class="badge nav-badge">0</span></a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'orders.php' ? 'active' : '' ?>" href="<?= $basePath ?>pages/orders.php">Orders</a></li>

                <li class="nav-item dropdown" id="userDropdown" style="display:none;">
                    <a class="nav-link dropdown-toggle user-pill" href="#" role="button" data-bs-toggle="dropdown"><span id="navUserName">User</span></a>
                    <ul class="dropdown-menu dropdown-menu-end luxury-dropdown">
                        <li><a class="dropdown-item" href="<?= $basePath ?>pages/profile.php"><i class="fa-regular fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>pages/orders.php"><i class="fa-solid fa-box me-2"></i>My Orders</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= $basePath ?>pages/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </li>

                <li class="nav-item" id="loginLink"><a class="btn btn-brand btn-sm ms-lg-2" href="<?= $basePath ?>pages/login.php">Login</a></li>
            </ul>
        </div>
    </div>
</nav>
