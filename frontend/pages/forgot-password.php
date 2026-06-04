<?php include '../components/header.php'; ?>
<?php include '../components/navbar.php'; ?>

<div class="container mt-5" style="max-width:500px;">

    <h2 class="mb-4">Forgot Password</h2>

    <div id="forgotPasswordAlert"></div>

    <form id="forgotPasswordForm">

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input
                type="email"
                id="forgotEmail"
                class="form-control"
                placeholder="Enter your verified email"
                required
            >
        </div>

        <button type="submit" class="btn btn-dark w-100">
            Send Reset Link
        </button>

    </form>

</div>

<script src="../assets/js/forgot-password.js"></script>

<?php include '../components/footer.php'; ?>