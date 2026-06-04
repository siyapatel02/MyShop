<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';
require_once __DIR__ . '/../../helpers/mailer.php';

$headers = getallheaders();

$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$authHeader) {
    jsonResponse(false, 'Token missing');
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);
$user = verifyToken($token);

if (!$user) {
    jsonResponse(false, 'Invalid token');
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    jsonResponse(false, 'Invalid JSON data');
    exit;
}

$allowedFields = [
    'fullname',
    'phone',
    'pincode',
    'address_line',
    'city',
    'state',
    'country',
    'address_type',
    'label',
    'payment_method',
    'coupon_code',
    'discount_amount'
];

foreach ($data as $key => $value) {
    if (!in_array($key, $allowedFields)) {
        jsonResponse(false, 'Invalid field: ' . $key);
        exit;
    }
}

$fullname = trim($data['fullname'] ?? '');
$phone = trim($data['phone'] ?? '');
$pincode = trim($data['pincode'] ?? '');
$addressLine = trim($data['address_line'] ?? '');
$city = trim($data['city'] ?? '');
$state = trim($data['state'] ?? '');
$country = trim($data['country'] ?? 'India');
$addressType = trim($data['address_type'] ?? 'home');
$label = trim($data['label'] ?? $addressType);
$paymentMethod = trim($data['payment_method'] ?? '');
$couponCode = strtoupper(trim($data['coupon_code'] ?? ''));
$discountAmount = floatval($data['discount_amount'] ?? 0);

if (
    empty($fullname) ||
    empty($phone) ||
    empty($pincode) ||
    empty($addressLine) ||
    empty($city) ||
    empty($state) ||
    empty($paymentMethod)
) {
    jsonResponse(false, 'All fields are required');
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    jsonResponse(false, 'Phone number must be 10 digits');
    exit;
}

if (!preg_match('/^[0-9]{6}$/', $pincode)) {
    jsonResponse(false, 'Pincode must be 6 digits');
    exit;
}

$allowedPaymentMethods = ['cod', 'upi', 'online'];

if (!in_array($paymentMethod, $allowedPaymentMethods)) {
    jsonResponse(false, 'Invalid payment method');
    exit;
}

try {

    $database = new Database();
    $db = $database->connect();

    /*
    |--------------------------------------------------------------------------
    | CHECK EMAIL VERIFICATION
    |--------------------------------------------------------------------------
    */

    $userCheckQuery = "
        SELECT
            id,
            name,
            email,
            email_verified
        FROM users
        WHERE id = :user_id
        LIMIT 1
    ";

    $userCheckStmt = $db->prepare($userCheckQuery);

    $userCheckStmt->execute([
        ':user_id' => $user['id']
    ]);

    $loggedInUser = $userCheckStmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$loggedInUser ||
        intval($loggedInUser['email_verified']) !== 1
    ) {
        jsonResponse(
            false,
            'Please verify your email before placing an order.',
            [
                'email_verified' => 0
            ],
            403
        );
        exit;
    }

    $db->beginTransaction();

    $cartQuery = "
        SELECT 
            cart.product_id,
            cart.quantity,
            products.name AS product_name,
            products.price
        FROM cart
        INNER JOIN products
        ON cart.product_id = products.id
        WHERE cart.user_id = :user_id
    ";

    $cartStmt = $db->prepare($cartQuery);
    $cartStmt->bindParam(':user_id', $user['id']);
    $cartStmt->execute();

    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($cartItems) <= 0) {
        $db->rollBack();
        jsonResponse(false, 'Cart is empty');
        exit;
    }

    $subtotal = 0;

    foreach ($cartItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    $total = $subtotal;

    /*
    |--------------------------------------------------------------------------
    | APPLY COUPON AGAIN (SECURITY)
    |--------------------------------------------------------------------------
    */

    if (!empty($couponCode)) {

        $offerQuery = "
            SELECT *
            FROM offers
            WHERE code = :code
            AND status = 'active'
            LIMIT 1
        ";

        $offerStmt = $db->prepare($offerQuery);

        $offerStmt->execute([
            ':code' => $couponCode
        ]);

        $offer = $offerStmt->fetch(PDO::FETCH_ASSOC);

        if (!$offer) {
            $db->rollBack();
            jsonResponse(false, 'Invalid coupon code');
            exit;
        }

        if (
            !empty($offer['expiry_date']) &&
            date('Y-m-d') > $offer['expiry_date']
        ) {
            $db->rollBack();
            jsonResponse(false, 'Coupon expired');
            exit;
        }

        if ($total < floatval($offer['minimum_order'])) {
            $db->rollBack();
            jsonResponse(false, 'Minimum order not reached');
            exit;
        }

        if (intval($offer['first_order_only']) === 1) {

            $orderCheckQuery = "
                SELECT COUNT(*) AS total_orders
                FROM orders
                WHERE user_id = :user_id
            ";

            $orderCheckStmt = $db->prepare($orderCheckQuery);

            $orderCheckStmt->execute([
                ':user_id' => $user['id']
            ]);

            $orderCheck = $orderCheckStmt->fetch(PDO::FETCH_ASSOC);

            if (
                $orderCheck &&
                intval($orderCheck['total_orders']) > 0
            ) {
                $db->rollBack();
                jsonResponse(false, 'This coupon is valid only for your first order');
                exit;
            }
        }

        $actualDiscount = 0;

        if ($offer['discount_type'] === 'percentage') {

            $actualDiscount =
                ($total * floatval($offer['discount_value'])) / 100;

            if (
                !empty($offer['max_discount']) &&
                $actualDiscount > floatval($offer['max_discount'])
            ) {
                $actualDiscount = floatval($offer['max_discount']);
            }

        } else {
            $actualDiscount = floatval($offer['discount_value']);
        }

        $discountAmount = $actualDiscount;
        $total = $total - $discountAmount;
    }

    $addressText =
        $fullname . ', ' .
        $phone . ', ' .
        $addressLine . ', ' .
        $city . ', ' .
        $state . ' - ' .
        $pincode . ', ' .
        $country;

    /*
    |--------------------------------------------------------------------------
    | CHECK EXISTING ADDRESS - PREVENT DUPLICATE
    |--------------------------------------------------------------------------
    */

    $checkAddressQuery = "
        SELECT id 
        FROM user_addresses
        WHERE user_id = :user_id
        AND fullname = :fullname
        AND phone = :phone
        AND pincode = :pincode
        AND address_line = :address_line
        AND city = :city
        AND state = :state
        AND country = :country
        LIMIT 1
    ";

    $checkAddressStmt = $db->prepare($checkAddressQuery);

    $checkAddressStmt->execute([
        ':user_id' => $user['id'],
        ':fullname' => $fullname,
        ':phone' => $phone,
        ':pincode' => $pincode,
        ':address_line' => $addressLine,
        ':city' => $city,
        ':state' => $state,
        ':country' => $country
    ]);

    $existingAddress = $checkAddressStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingAddress) {

        $addressId = $existingAddress['id'];

        $updateAddressQuery = "
            UPDATE user_addresses
            SET 
                address_type = :address_type,
                label = :label,
                last_used_at = NOW()
            WHERE id = :id
            AND user_id = :user_id
        ";

        $updateAddressStmt = $db->prepare($updateAddressQuery);

        $updateAddressStmt->execute([
            ':address_type' => $addressType,
            ':label' => $label,
            ':id' => $addressId,
            ':user_id' => $user['id']
        ]);

    } else {

        $addressQuery = "
            INSERT INTO user_addresses
            (
                user_id,
                fullname,
                phone,
                pincode,
                address_line,
                city,
                state,
                address_type,
                label,
                country,
                last_used_at
            )
            VALUES
            (
                :user_id,
                :fullname,
                :phone,
                :pincode,
                :address_line,
                :city,
                :state,
                :address_type,
                :label,
                :country,
                NOW()
            )
        ";

        $addressStmt = $db->prepare($addressQuery);

        $addressStmt->execute([
            ':user_id' => $user['id'],
            ':fullname' => $fullname,
            ':phone' => $phone,
            ':pincode' => $pincode,
            ':address_line' => $addressLine,
            ':city' => $city,
            ':state' => $state,
            ':address_type' => $addressType,
            ':label' => $label,
            ':country' => $country
        ]);

        $addressId = $db->lastInsertId();
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

    $orderQuery = "
        INSERT INTO orders
        (
            user_id,
            total,
            address,
            status,
            payment_method,
            coupon_code,
            discount_amount
        )
        VALUES
        (
            :user_id,
            :total,
            :address,
            'pending',
            :payment_method,
            :coupon_code,
            :discount_amount
        )
    ";

    $orderStmt = $db->prepare($orderQuery);

    $orderStmt->execute([
        ':user_id' => $user['id'],
        ':total' => $total,
        ':address' => $addressText,
        ':payment_method' => $paymentMethod,
        ':coupon_code' => $couponCode ?: null,
        ':discount_amount' => $discountAmount
    ]);

    $orderId = $db->lastInsertId();

    $itemQuery = "
        INSERT INTO order_items
        (
            order_id,
            product_id,
            quantity,
            price
        )
        VALUES
        (
            :order_id,
            :product_id,
            :quantity,
            :price
        )
    ";

    $itemStmt = $db->prepare($itemQuery);

    foreach ($cartItems as $item) {
        $itemStmt->execute([
            ':order_id' => $orderId,
            ':product_id' => $item['product_id'],
            ':quantity' => $item['quantity'],
            ':price' => $item['price']
        ]);
    }

    $clearQuery = "
        DELETE FROM cart
        WHERE user_id = :user_id
    ";

    $clearStmt = $db->prepare($clearQuery);
    $clearStmt->bindParam(':user_id', $user['id']);
    $clearStmt->execute();

    $db->commit();

    /*
    |--------------------------------------------------------------------------
    | SEND ORDER CONFIRMATION EMAIL
    |--------------------------------------------------------------------------
    */

    $itemsHtml = '';

    foreach ($cartItems as $item) {

        $itemSubtotal =
            floatval($item['price']) *
            intval($item['quantity']);

        $itemsHtml .= "
            <tr>
                <td style='padding:10px;border-bottom:1px solid #eee;'>
                    {$item['product_name']}
                </td>
                <td style='padding:10px;border-bottom:1px solid #eee;text-align:center;'>
                    {$item['quantity']}
                </td>
                <td style='padding:10px;border-bottom:1px solid #eee;text-align:right;'>
                    ₹" . number_format($item['price'], 2) . "
                </td>
                <td style='padding:10px;border-bottom:1px solid #eee;text-align:right;'>
                    ₹" . number_format($itemSubtotal, 2) . "
                </td>
            </tr>
        ";
    }

    $discountRow = '';

    if ($discountAmount > 0) {
        $discountRow = "
            <tr>
                <td colspan='3' style='padding:8px;text-align:right;'>
                    Discount
                </td>
                <td style='padding:8px;text-align:right;color:green;'>
                    - ₹" . number_format($discountAmount, 2) . "
                </td>
            </tr>
        ";
    }

    $couponRow = '';

    if (!empty($couponCode)) {
        $couponRow = "
            <p>
                <strong>Coupon:</strong> {$couponCode}
            </p>
        ";
    }

    $emailBody = "
        <div style='font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;'>

            <div style='max-width:700px;margin:auto;background:#ffffff;border-radius:8px;overflow:hidden;'>

                <div style='background:#111;color:#fff;padding:20px;text-align:center;'>
                    <h2 style='margin:0;'>MyShop Order Confirmation</h2>
                </div>

                <div style='padding:20px;'>

                    <p>Hello <strong>{$loggedInUser['name']}</strong>,</p>

                    <p>Thank you for shopping with MyShop. Your order has been placed successfully.</p>

                    <p>
                        <strong>Order ID:</strong> #{$orderId}<br>
                        <strong>Order Status:</strong> Pending<br>
                        <strong>Payment Method:</strong> " . strtoupper($paymentMethod) . "<br>
                        <strong>Order Date:</strong> " . date('d M Y, h:i A') . "
                    </p>

                    {$couponRow}

                    <h3>Invoice Details</h3>

                    <table style='width:100%;border-collapse:collapse;border:1px solid #eee;'>
                        <thead>
                            <tr style='background:#f8f8f8;'>
                                <th style='padding:10px;text-align:left;'>Product</th>
                                <th style='padding:10px;text-align:center;'>Qty</th>
                                <th style='padding:10px;text-align:right;'>Price</th>
                                <th style='padding:10px;text-align:right;'>Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            {$itemsHtml}
                        </tbody>

                        <tfoot>
                            <tr>
                                <td colspan='3' style='padding:8px;text-align:right;'>
                                    Subtotal
                                </td>
                                <td style='padding:8px;text-align:right;'>
                                    ₹" . number_format($subtotal, 2) . "
                                </td>
                            </tr>

                            {$discountRow}

                            <tr>
                                <td colspan='3' style='padding:10px;text-align:right;font-weight:bold;'>
                                    Final Total
                                </td>
                                <td style='padding:10px;text-align:right;font-weight:bold;'>
                                    ₹" . number_format($total, 2) . "
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <h3>Shipping Address</h3>

                    <p style='line-height:1.6;'>
                        {$addressText}
                    </p>

                    <p style='margin-top:25px;'>
                        We will notify you when your order is shipped.
                    </p>

                    <p>
                        Thank you,<br>
                        <strong>MyShop Team</strong>
                    </p>

                </div>
            </div>
        </div>
    ";

    $invoiceMailSent = sendMail(
        $loggedInUser['email'],
        $loggedInUser['name'],
        'MyShop Order Confirmation #' . $orderId,
        $emailBody
    );

    jsonResponse(
        true,
        'Order placed successfully',
        [
            'order_id' => $orderId,
            'total' => $total,
            'address_id' => $addressId,
            'invoice_mail_sent' => $invoiceMailSent
        ]
    );

} catch (Exception $e) {

    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    jsonResponse(false, 'Order failed: ' . $e->getMessage());
}