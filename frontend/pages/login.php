<?php include '../components/header.php'; ?>

<?php include '../components/navbar.php'; ?>

<main class="container auth-page page-shell">

<div class="row justify-content-center align-items-center min-vh-soft">

<div class="col-md-6 col-lg-5" data-aos="fade-up">

<div class="card auth-card">

<div class="card-body">

<span class="eyebrow">MyShop Account</span>
<h3 class="mb-2">Sign in to MyShop</h3>
<p class="text-muted mb-4">Welcome back to your fashion store.</p>

<form id="loginForm" method="POST">

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required
>

</div>

<div class="mb-3">

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required
>

</div>

<button
class="btn btn-dark w-100"
>
Login
</button>

</form>

<div class="mt-3">

<a href="register.php">
Create Account
</a>

</div>

</div>

</div>

</div>

</div>

</main>

<script src="../assets/js/auth.js"></script>

<?php include '../components/footer.php'; ?>