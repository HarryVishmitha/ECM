<?php

require '../env.php';
require_once '../core/DB_conn.php';
$page = 'User Dashboard';
session_start();

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Get total orders
$orderCount = 0;
$lastOrder = null;

$stmt = mysqli_prepare($conn, "SELECT COUNT(*), MAX(id) FROM orders WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $orderCount, $lastOrderId);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Get wishlist count
$wishlistCount = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) AS count FROM wishlist WHERE user_id = $userId");
$row = mysqli_fetch_assoc($res);
$wishlistCount = $row['count'] ?? 0;

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
    <?php include '../components/customer-topnav.php' ?>

    <div class="container mt-3">
        <h1 class="text-center mt-3">Welcome back, <?= htmlspecialchars($userName) ?> 👋</h1>
        <div class="dashboard-grid mt-3">
            <div class="dashboard-card">
                <h3><?= $orderCount ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="dashboard-card">
                <h3><?= $wishlistCount ?></h3>
                <p>Items in Wishlist</p>
            </div>
            <div class="dashboard-card">
                <h3>#<?= $lastOrderId ?: 'N/A' ?></h3>
                <p>Last Order ID</p>
            </div>
            <div class="dashboard-card">
                <a href="support.php" class="primary-btn small-btn">Contact Support</a>
            </div>
        </div>
    </div>


    <!-- Get and show last order related details here -->

    <?php
    // Fetch last order details if exists
    $lastOrderData = null;
    if ($lastOrderId) {
        $stmt = mysqli_prepare($conn, "SELECT order_number, total_amount, payment_method, order_status, created_at FROM orders WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $lastOrderId, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $orderNumber, $totalAmount, $paymentMethod, $orderStatus, $createdAt);
        if (mysqli_stmt_fetch($stmt)) {
            $lastOrderData = [
                'order_number' => $orderNumber,
                'total' => $totalAmount,
                'method' => $paymentMethod,
                'status' => ucfirst($orderStatus),
                'date' => date('F j, Y', strtotime($createdAt))
            ];
        }
        mysqli_stmt_close($stmt);
    }
    ?>

    <?php if ($lastOrderData): ?>
        <div class="dashboard-last-order mt-3">
            <h3 class="mb-2">📝 Your Last Order Summary</h3>
            <div class="order-summary-box">
                <p><strong>Order #:</strong> <?= $lastOrderData['order_number'] ?></p>
                <p><strong>Total:</strong> Rs. <?= number_format($lastOrderData['total'], 2) ?></p>
                <p><strong>Payment:</strong> <?= $lastOrderData['method'] ?></p>
                <p><strong>Status:</strong> <?= $lastOrderData['status'] ?></p>
                <p><strong>Date:</strong> <?= $lastOrderData['date'] ?></p>
                <a href="orders.php" class="primary-btn small-btn mt-1">View All Orders</a>
            </div>
        </div>
    <?php else: ?>
        <div class="dashboard-last-order mt-3 text-center">
            <h3 class="mb-2">📝 You haven't placed any orders yet.</h3>
            <a href="../index.php?page=shop" class="primary-btn mt-1">Start Shopping</a>
        </div>
    <?php endif; ?>

    <div class="footer mt-3">
        <?php include '../components/footer.php'; ?>
    </div>

</body>

</html>