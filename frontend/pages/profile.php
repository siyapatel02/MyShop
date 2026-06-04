<?php include '../components/header.php'; ?>
<?php include '../components/navbar.php'; ?>

<link rel="stylesheet" href="../assets/css/pages/profile.css?v=1">

<section class="profile-header" data-aos="fade-up">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="profile-avatar">
                    <img src="../assets/images/default-user.jpg" alt="Profile" class="avatar-img" id="profileAvatar">
                    <div class="avatar-upload">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="profile-info">
                    <h1 class="profile-name" id="profileName">Loading...</h1>
                    <p class="profile-email" id="profileEmail">Loading...</p>
                    <p class="profile-joined" id="profileJoined">
                        <i class="far fa-calendar-alt me-2"></i>Member since -
                    </p>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-value" id="ordersCount">0</span>
                            <span class="stat-label">Orders</span>
                        </div>

                        <div class="stat-item">
                            <span class="stat-value" id="addressCount">0</span>
                            <span class="stat-label">Addresses</span>
                        </div>

                        <div class="stat-item">
                            <span class="stat-value" id="wishlistCount">0</span>
                            <span class="stat-label">Wishlist</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="profile-content">
    <div class="container">
        <div id="profileAlert"></div>

        <div class="row">
            <div class="col-lg-3">
                <div class="profile-sidebar">
                    <ul class="profile-nav">
                        <li><a href="#personal-info" class="profile-nav-link active"><i class="fas fa-user"></i> Personal Info</a></li>
                        <li><a href="#orders" class="profile-nav-link"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                        <li><a href="#addresses" class="profile-nav-link"><i class="fas fa-map-marker-alt"></i> Addresses</a></li>
                        <li><a href="#wishlist" class="profile-nav-link"><i class="far fa-heart"></i> Wishlist</a></li>
                        <li><a href="#security" class="profile-nav-link"><i class="fas fa-lock"></i> Security</a></li>
                        <li class="mt-4"><a href="#" class="profile-nav-link text-danger" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-9">

                <div class="profile-section" id="personal-info" data-aos="fade-up">
                    <div class="section-header">
                        <h2 class="section-title">Personal Information</h2>
                        <button class="edit-btn" type="button" id="editProfileBtn">
                            <i class="fas fa-edit me-2"></i>Edit
                        </button>
                    </div>

                    <form id="profileForm">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" disabled>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Gender</label>
                                <select class="form-control" id="gender" disabled>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                    <option value="prefer-not-to-say">Prefer not to say</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="save-btn d-none" id="saveProfileBtn">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <div class="profile-section d-none" id="orders" data-aos="fade-up">
                    <div class="section-header">
                        <h2 class="section-title">My Orders</h2>
                    </div>
                    <div id="profileOrdersList"></div>
                    <div class="text-center mt-4">
                        <a href="orders.php" class="btn btn-outline-dark">View All Orders</a>
                    </div>
                </div>

                <div class="profile-section d-none" id="addresses" data-aos="fade-up">
                    <div class="section-header">
                        <h2 class="section-title">My Addresses</h2>
                        <a href="checkout.php" class="edit-btn text-decoration-none">
                            <i class="fas fa-plus me-2"></i>Add New
                        </a>
                    </div>
                    <div class="row" id="profileAddressesList"></div>
                </div>

                <div class="profile-section d-none" id="wishlist" data-aos="fade-up">
                    <div class="section-header">
                        <h2 class="section-title">My Wishlist</h2>
                    </div>
                    <div class="wishlist-grid" id="profileWishlistList"></div>
                </div>

                <div class="profile-section d-none" id="security" data-aos="fade-up">
                    <div class="section-header">
                        <h2 class="section-title">Security Settings</h2>
                    </div>

                    <form id="changePasswordForm">

                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input
                                type="password"
                                class="form-control"
                                id="currentPassword"
                                placeholder="Enter current password"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input
                                type="password"
                                class="form-control"
                                id="newPassword"
                                placeholder="Enter new password"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input
                                type="password"
                                class="form-control"
                                id="confirmPassword"
                                placeholder="Confirm new password"
                            >
                        </div>

                        <button type="submit" class="save-btn">
                            <i class="fas fa-lock"></i> Change Password
                        </button>

                    </form>

                    <hr class="my-4">

                    <p class="mb-2">
                        Forgot your password?
                    </p>

                    <a href="forgot-password.php" class="btn btn-outline-dark">
                        Reset Password
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="../assets/js/profile.js?v=1"></script>

<?php include '../components/footer.php'; ?>