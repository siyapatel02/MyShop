<?php include '../components/header.php'; ?>

<?php include '../components/navbar.php'; ?>

<main class="container page-shell cart-page" data-aos="fade-up">

<h2 class="mb-4">My Cart</h2>

<div id="cartAlert"></div>

<div class="row g-4">

<div class="col-md-8">

<div id="cartContainer"></div>

</div>

<div class="col-md-4">

<div class="card cart-summary p-4">

<h4 class="mb-3">Order Summary</h4>

<div class="d-flex justify-content-between mb-2">
<span>Subtotal</span>
<span>₹<span id="cartSubtotal">0.00</span></span>
</div>

<div class="d-flex justify-content-between mb-2">
<span>Delivery</span>
<span>₹<span id="cartDelivery">50.00</span></span>
</div>

<hr>

<div class="d-flex justify-content-between fw-bold fs-5">
<span>Total</span>
<span>₹<span id="cartTotal">0.00</span></span>
</div>

<a
href="checkout.php"
id="checkoutBtn"
class="btn btn-success w-100 mt-3"
>
Proceed To Checkout
</a>

<a
href="home.php"
class="btn btn-outline-dark w-100 mt-2"
>
Continue Shopping
</a>

</div>

</div>

</div>

</main>

<?php include '../components/footer.php'; ?>