<?php
require_once '../core/DB_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'], $_POST['quantity'])) {
    $cartId = (int)$_POST['cart_id'];
    $qty = max(1, (int)$_POST['quantity']);

    $query = "UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iii", $qty, $cartId, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header("Location: cart.php");
exit;
