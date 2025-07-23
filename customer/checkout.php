<?php
// customer/checkout.php
session_start();
require '../env.php';
require_once '../core/DB_conn.php';

// 1) Login guard
if (!isset($_SESSION['user_id'])) {
    echo <<<HTML
    <div style="background:linear-gradient(120deg,#5d3fd3,#fff);width:100%;height:100vh;
                display:flex;align-items:center;justify-content:center;">
      <div style="max-width:480px;background:#fff;padding:2rem;border-radius:20px;
                  box-shadow:0 8px 20px rgba(0,0,0,0.08);text-align:center;">
        <h2>🚫 Login Required</h2>
        <p>You must <a href="../login.php" class="primary-btn">log in</a> to checkout.</p>
      </div>
    </div>
    HTML;
    exit;
}

$userId    = $_SESSION['user_id'];
$userName  = $_SESSION['user_name'];
$orderPlaced   = false;
$errorMessage  = '';

// 2) Payment methods whitelist & default
$methods = [
    'cod'    => 'Cash on Delivery',
    'card'   => 'Card Payment',
    'paypal' => 'PayPal'
];
$pmKey = 'cod';

// 3) Handle POST: place order, clear cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // validate payment method
    $pmRaw = $_POST['payment_method'] ?? 'cod';
    $pmKey = array_key_exists($pmRaw, $methods) ? $pmRaw : 'cod';

    // fetch cart items for this user
    $stmt = mysqli_prepare($conn, "
        SELECT c.id AS cart_id, p.id AS product_id, p.name, p.price, c.quantity,
               v.id AS variant_id, v.size, v.color, v.additional_price
          FROM cart_items c
          JOIN products p ON c.product_id = p.id
          LEFT JOIN product_variants v ON c.variant_id = v.id
         WHERE c.user_id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $cartItems = mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // compute total server-side
    $total = 0;
    foreach ($cartItems as $it) {
        $unit  = $it['price'] + ($it['additional_price'] ?? 0);
        $total += $unit * $it['quantity'];
    }

    try {
        mysqli_begin_transaction($conn);

        // generate unique order number
        $order_number = uniqid('ORD-', true);

        // insert into orders
        $oStmt = mysqli_prepare($conn, "
            INSERT INTO orders
              (user_id, order_number, total_amount, payment_method, order_status, created_at)
            VALUES
              (?,       ?,            ?,            ?,              'pending',    NOW())
        ");
        mysqli_stmt_bind_param($oStmt, 'isds', $userId, $order_number, $total, $pmKey);
        mysqli_stmt_execute($oStmt);
        $orderId = mysqli_insert_id($conn);
        mysqli_stmt_close($oStmt);

        // insert into order_items
        $iStmt = mysqli_prepare($conn, "
    INSERT INTO order_items
      (order_id, product_id, variant_id, quantity, price)
    VALUES
      (?,        ?,          ?,          ?,      ?)
");

        foreach ($cartItems as $it) {
            $unit = $it['price'] + ($it['additional_price'] ?? 0);
            mysqli_stmt_bind_param(
                $iStmt,
                'iiiid',
                $orderId,
                $it['product_id'],
                $it['variant_id'],
                $it['quantity'],
                $unit
            );
            mysqli_stmt_execute($iStmt);
        }
        mysqli_stmt_close($iStmt);

        // clear the cart
        $dStmt = mysqli_prepare($conn, "DELETE FROM cart_items WHERE user_id = ?");
        mysqli_stmt_bind_param($dStmt, 'i', $userId);
        mysqli_stmt_execute($dStmt);
        mysqli_stmt_close($dStmt);

        mysqli_commit($conn);
        $orderPlaced = true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Checkout error: " . $e->getMessage());
        $errorMessage = "There was a problem placing your order. Please try again. Error : " . $e->getMessage();
    }
}

// 4) If not placed yet, fetch cart items & total for display
$cartItems = [];
$total      = 0;
if (!$orderPlaced) {
    $stmt = mysqli_prepare($conn, "
        SELECT c.id AS cart_id, p.id AS product_id, p.name, p.price, c.quantity,
               v.id AS variant_id, v.size, v.color, v.additional_price
          FROM cart_items c
          JOIN products p ON c.product_id = p.id
          LEFT JOIN product_variants v ON c.variant_id = v.id
         WHERE c.user_id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $cartItems = mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    foreach ($cartItems as $it) {
        $unit  = $it['price'] + ($it['additional_price'] ?? 0);
        $total += $unit * $it['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Checkout – <?= htmlspecialchars($name) ?></title>
    <link rel="stylesheet" href="../inc/css/customer-stylesheet.css">
    <script src="../inc/js/customer-script.js" defer></script>
    <link rel="icon" href="../inc/assets/site-images/logo.png" type="image/x-icon">
    <style>
        /* checkout.css */
        .checkout-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .checkout-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .checkout-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin: 0;
        }

        .order-summary {
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: .75rem 0;
        }

        .order-item+.order-item {
            border-top: 1px solid #f0f0f0;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 1.25rem;
            margin-top: 1rem;
        }

        .payment-methods label {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1rem;
            color: #333;
        }

        .payment-methods input {
            margin-right: .75rem;
            transform: scale(1.2);
        }

        .primary-btn {
            background: #5d3fd3;
            color: #fff;
            padding: .75rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background .3s;
            text-decoration: none;
            display: inline-block;
        }

        .primary-btn:hover {
            background: #432f9a;
        }

        .thank-you {
            text-align: center;
            padding: 2rem;
            background: #e5f4ea;
            border-radius: 12px;
            animation: fadeIn .5s ease-in-out;
        }

        .hidden {
            display: none;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* alert styles */
        .alert {
            padding: .75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background: #fdecea;
            color: #b71c1c;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #e6ffed;
            color: #256029;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>

<body>
    <?php include '../components/customer-topnav.php'; ?>

    <div class="checkout-container">
        <div class="checkout-header">
            <h1>🛒 Checkout</h1>
        </div>

        <?php if ($errorMessage): ?>
            <p class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <?php if ($orderPlaced): ?>
            <div class="thank-you">
                <h2>🎉 Thank you, <?= htmlspecialchars($userName) ?>!</h2>
                <p>Your order <strong>#<?= htmlspecialchars($orderId) ?></strong> has been placed.</p>
                <p>Payment Method: <strong><?= htmlspecialchars($methods[$pmKey]) ?></strong></p>
                <a href="orders.php" class="primary-btn">View Your Orders</a>
            </div>

        <?php elseif (empty($cartItems)): ?>
            <p>Your cart is empty. <a href="../index.php?page=shop" class="primary-btn">Browse Products</a></p>

        <?php else: ?>
            <div class="order-summary">
                <?php foreach ($cartItems as $it):
                    $unit = $it['price'] + ($it['additional_price'] ?? 0);
                    $sub  = $unit * $it['quantity'];
                ?>
                    <div class="order-item">
                        <div>
                            <strong><?= htmlspecialchars($it['name']) ?></strong>
                            <small>(Size: <?= htmlspecialchars($it['size'] ?: '–') ?>,
                                Color: <?= htmlspecialchars($it['color'] ?: '–') ?>)</small>
                        </div>
                        <div>Rs. <?= number_format($unit, 2) ?> × <?= $it['quantity'] ?> = Rs. <?= number_format($sub, 2) ?></div>
                    </div>
                <?php endforeach; ?>
                <div class="order-total">
                    <span>Total:</span>
                    <span>Rs. <?= number_format($total, 2) ?></span>
                </div>
            </div>

            <form action="checkout.php" method="POST">
                <div class="payment-methods">
                    <?php foreach ($methods as $key => $label): ?>
                        <label>
                            <input type="radio" name="payment_method" value="<?= $key ?>"
                                <?= $key === $pmKey ? 'checked' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="primary-btn">Place Order</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="footer mt-5">
        <?php include '../components/footer.php'; ?>
    </div>
</body>

</html>