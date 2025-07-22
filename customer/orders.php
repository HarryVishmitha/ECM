<?php

require '../env.php';
require_once '../core/DB_conn.php';
$page = 'User Dashboard';
session_start();

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$query = "SELECT id, order_number, total_amount, order_status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

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

    <div class="container mt-5">
        <h1 class="text-center mb-3">📦 My Orders</h1>

        <?php if (count($orders) === 0): ?>
            <p class="bg-white text-center p-4 border-rounded">You haven’t placed any orders yet. <a href="../index.php?page=shop" class="primary-btn small-btn">Start Shopping</a></p>
        <?php else: ?>
            <div class="order-table bg-white">
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['order_number'] ?></td>
                                <td><?= date('F j, Y', strtotime($order['created_at'])) ?></td>
                                <td><?= ucfirst($order['order_status']) ?></td>
                                <td>Rs. <?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <a href="order-details.php?id=<?= $order['id'] ?>" class="primary-btn small-btn">View</a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer mt-3">
        <?php include '../components/footer.php'; ?>
    </div>
</body>

</html>