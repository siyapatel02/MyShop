<?php include '../components/header.php'; ?>

<?php include '../components/navbar.php'; ?>

<main class="container auth-page page-shell">

<div class="row justify-content-center align-items-center min-vh-soft">

<div class="col-md-6 col-lg-5" data-aos="fade-up">

<div class="card auth-card">

<div class="card-body">

<span class="eyebrow">MyShop Account</span>
<h3 class="mb-2">Create your account</h3>
<p class="text-muted mb-4">Join MyShop and save your favourite styles.</p>

<form id="registerForm" method="POST">

<div class="mb-3">

<label>Name</label>

<input
type="text"
name="name"
class="form-control"
required
>

</div>

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
id="registerPassword"
class="form-control"
required
>
<div id="passwordRules" class="password-rules password-rules-popup mt-2" aria-live="polite"></div>

</div>

<button
class="btn btn-dark w-100" id="registerSubmitBtn"
>
Register
</button>

</form>

<div class="mt-3">

<a href="login.php">
Already have account?
</a>

</div>

</div>

</div>

</div>

</div>

</main>

<script src="../assets/js/auth.js"></script>

<?php include '../components/footer.php'; ?>