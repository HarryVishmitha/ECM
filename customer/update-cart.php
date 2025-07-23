<?php
// customer/update-cart.php
session_start();
require '../env.php';
require_once '../core/DB_conn.php';

if (!isset($_SESSION['user_id'])) {
    // Not logged in — send back to login
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];

if (isset($_POST['cart_id'], $_POST['quantity'])) {
    $cartId  = (int) $_POST['cart_id'];
    $qty     = max(1, (int) $_POST['quantity']);  // ensure at least 1

    // Optionally, you could check stock here before updating:
    // $stmt = mysqli_prepare($conn, "
    //   SELECT v.stock
    //     FROM cart_items c
    //     JOIN product_variants v ON c.variant_id = v.id
    //    WHERE c.id = ? AND c.user_id = ?
    // ");
    // …then compare $qty <= $stock and reject if not.

    $stmt = mysqli_prepare($conn, "
        UPDATE cart_items
           SET quantity = ?
         WHERE id = ? AND user_id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'iii', $qty, $cartId, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $_SESSION['cart_message'] = 'Quantity updated successfully.';
}

// Go back to the cart
header('Location: cart.php');
exit;
