<?php
require 'env.php';
require_once 'core/DB_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=product-details.php?id=" . $_POST['product_id']);
    exit;
}

if (!isset($_POST['variant_id'])) {
    $_SESSION['error'] = "Please select size and color!";
    header("Location: product-details.php?id=" . $_POST['product_id']);
    exit;
}


$userId = $_SESSION['user_id'];

$product_id = (int) $_POST['product_id'];
$variant_id = isset($_POST['variant_id']) ? (int) $_POST['variant_id'] : null;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

// Optional: check stock
if ($variant_id) {
    $stockCheck = mysqli_query($conn, "SELECT stock FROM product_variants WHERE id = $variant_id");
    $row = mysqli_fetch_assoc($stockCheck);
    if ($row && $row['stock'] < $quantity) {
        $_SESSION['error'] = "Only " . $row['stock'] . " item(s) left in stock!";
        header("Location: product-details.php?id=$product_id");
        exit;
    }
}

// Check if item already in cart
$check = mysqli_query($conn, "SELECT id FROM cart_items WHERE user_id=$userId AND product_id=$product_id AND variant_id " . ($variant_id ? "= $variant_id" : "IS NULL"));
if (mysqli_num_rows($check)) {
    mysqli_query($conn, "UPDATE cart_items SET quantity = quantity + $quantity WHERE user_id=$userId AND product_id=$product_id AND variant_id " . ($variant_id ? "= $variant_id" : "IS NULL"));
} else {
    $stmt = mysqli_prepare($conn, "INSERT INTO cart_items (user_id, product_id, variant_id, quantity) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiii", $userId, $product_id, $variant_id, $quantity);
    mysqli_stmt_execute($stmt);
}

$_SESSION['success'] = "Item added to cart!";
header("Location: customer/cart.php");
exit;
