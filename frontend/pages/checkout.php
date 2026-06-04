<?php include '../components/header.php'; ?>
<?php include '../components/navbar.php'; ?>

<main class="container page-shell checkout-page" data-aos="fade-up">
    <h2 class="mb-4">Checkout</h2>

    <div id="checkoutAlert"></div>

    <form id="checkoutForm">
        <div class="row">
            <div class="col-md-6">
                <div class="card p-3 shadow-sm mb-3">
                    <h5 class="border-bottom pb-2 mb-3">1. Shipping Address</h5>

                        <div id="selectedAddressBox"></div>
                        <div id="savedAddresses" style="display:none;"></div>
                    <hr>
                    <p class="text-muted small">Want to add new address?</p>

                    <input type="text" name="fullname" class="form-control mb-2" placeholder="Full Name">
                    <input type="text" name="phone" class="form-control mb-2" placeholder="Mobile Number">
                    <input type="text" name="pincode" class="form-control mb-2" placeholder="Pincode">
                    <input type="text" name="address_line" class="form-control mb-2" placeholder="House No, Street, Area">

                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" name="city" class="form-control mb-2" placeholder="City">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="state" class="form-control mb-2" placeholder="State">
                        </div>
                    </div>

                    <select name="address_type" class="form-control mb-2">
                        <option value="home">Home</option>
                        <option value="office">Office</option>
                    </select>
                </div>

                <div class="card p-3 shadow-sm">
                    <h5 class="border-bottom pb-2 mb-3">2. Payment Method</h5>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" value="cod" checked>
                        <label class="form-check-label">Cash on Delivery</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" value="upi">
                        <label class="form-check-label">UPI</label>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-3 shadow-sm" style="position:sticky; top:20px;">
                    <h5 class="border-bottom pb-2 mb-3">Order Summary</h5>

                    <div id="checkoutItems"></div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span>Subtotal</span>
                        <span>₹<span id="checkoutSubtotal">0.00</span></span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Delivery</span>
                        <span>₹50.00</span>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold">Apply Coupon</label>

                        <div class="input-group">
                            <input 
                                type="text" 
                                id="couponCode" 
                                class="form-control" 
                                placeholder="Enter coupon code"
                            >

                            <button 
                                type="button" 
                                class="btn btn-outline-dark" 
                                id="applyCouponBtn"
                            >
                                Apply
                            </button>
                        </div>

                        <small id="couponMessage" class="d-block mt-2"></small>
                        <div id="availableOffers" class="mt-3"></div>
                    </div>

                    <div class="d-flex justify-content-between mt-3" id="discountRow" style="display:none !important;">
                        <span>Discount</span>
                        <span class="text-success">-₹<span id="checkoutDiscount">0.00</span></span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span class="text-success">₹<span id="checkoutTotal">50.00</span></span>
                    </div>

                    <button type="submit" class="btn btn-success w-100 mt-3">
                        Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</main>

<script src="../assets/js/checkout.js"></script>

<?php include '../components/footer.php'; ?>



