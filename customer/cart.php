<?php

require '../env.php';
require_once '../core/DB_conn.php';
$page = 'cart';
session_start();



if (isset($_SESSION['user_id'])) {

    $userId = $_SESSION['user_id'];
    $userName = $_SESSION['user_name'];

    $query = "
SELECT c.id AS cart_id, p.id AS product_id, p.name, p.price, c.quantity,
       v.size, v.color, v.additional_price,
       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
FROM cart_items c
JOIN products p ON c.product_id = p.id
LEFT JOIN product_variants v ON c.variant_id = v.id
WHERE c.user_id = ?
";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $cartItems = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    $total = 0;
    foreach ($cartItems as $item) {
        $itemPrice = $item['price'] + ($item['additional_price'] ?? 0);
        $total += $itemPrice * $item['quantity'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard – <?php echo $name; ?></title>
    <link rel="stylesheet" href="../inc/css/customer-stylesheet.css">
    <script src="../inc/js/customer-script.js" defer></script>
    <link rel="icon" href="../inc/assets/site-images/logo.png" type="image/x-icon">
</head>

<body>

    <?php if (!isset($_SESSION['user_id'])) : ?>

        <!-- // Show login required message directly in page -->
        <div class="" style="background: linear-gradient(120deg, #5d3fd3, #fff); width: 100%; height: 100vh; display: flex; align-items: center; justify-content: center;">
            <div class="bg-white p-4 text-center border-rounded" style=" max-width: 480px;background: white;padding: 2.5rem;border-radius: 20px;box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);text-align: center;">
                <h2>🚫 Login Required</h2>
                <p>You must <a href="../login.php" class="primary-btn small-btn">log in</a> to view your cart.</p>
            </div>
        </div>
        <?php exit; ?>
    <?php else: ?>


        <?php include '../components/customer-topnav.php' ?>

        <div class="container mt-3">
            <h1 class="mb-3 text-center">🛍️ Your Shopping Cart</h1>

            <?php if (empty($cartItems)): ?>
                <p class="bg-white text-center p-4 border-rounded">Your cart is empty. <a href="../index.php?page=shop" class="primary-btn small-btn">Browse Products</a></p>
            <?php else: ?>
                <div class="cart-grid bg-white">
                    <?php foreach ($cartItems as $item):
                        $itemPrice = $item['price'] + ($item['additional_price'] ?? 0);
                        $subtotal = $itemPrice * $item['quantity'];
                    ?>
                        <div class="cart-card">
                            <img src="<?= $item['image'] ?? '../inc/assets/images/default.jpg' ?>" class="cart-img" alt="<?= $item['name'] ?>" />
                            <div class="cart-info">
                                <h4><?= htmlspecialchars($item['name']) ?></h4>
                                <?php if ($item['size'] || $item['color']): ?>
                                    <p class="variant">Size: <?= $item['size'] ?? '-' ?> | Color: <?= $item['color'] ?? '-' ?></p>
                                <?php endif ?>
                                <p>Rs. <?= number_format($itemPrice, 2) ?> × <?= $item['quantity'] ?> = <strong>Rs. <?= number_format($subtotal, 2) ?></strong></p>

                                <div class="cart-actions">
                                    <form action="update-cart.php" method="POST">
                                        <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>" />
                                        <input type="number" name="quantity" min="1" value="<?= $item['quantity'] ?>" />
                                        <button type="submit" class="primary-btn small-btn">Update</button>
                                    </form>
                                    <a href="remove-cart.php?id=<?= $item['cart_id'] ?>" class="secondary-btn small-btn">Remove</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>

                <div class="cart-total mt-3">
                    <h3>Total: Rs. <?= number_format($total, 2) ?></h3>
                    <a href="checkout.php" class="primary-btn">Proceed to Checkout</a>
                </div>
            <?php endif ?>
        </div>

        <div class="footer mt-3">
            <?php include '../components/footer.php'; ?>
        </div>

    <?php endif; ?>
</body>

</html>