<?php
// customer/remove-cart.php
session_start();
require '../env.php';
require_once '../core/DB_conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $cartId = (int) $_GET['id'];
    $stmt = mysqli_prepare($conn, "
        DELETE FROM cart_items
         WHERE id = ? AND user_id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'ii', $cartId, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header('Location: cart.php');
exit;
