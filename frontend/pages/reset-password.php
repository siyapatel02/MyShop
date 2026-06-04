<?php include '../components/header.php'; ?>
<?php include '../components/navbar.php'; ?>

<main class="container page-shell flex-fill" style="max-width:500px;">

    <h2 class="mb-4">Reset Password</h2>

    <div id="resetPasswordAlert"></div>

    <form id="resetPasswordForm">

        <input type="hidden" id="resetToken">

        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input
                type="password"
                id="newPassword"
                class="form-control"
                placeholder="Enter new password"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input
                type="password"
                id="confirmPassword"
                class="form-control"
                placeholder="Confirm new password"
                required
            >
        </div>

        <button type="submit" class="btn btn-dark w-100">
            Reset Password
        </button>

    </form>

</main>

<script src="../assets/js/reset-password.js"></script>

<?php include '../components/footer.php'; ?>