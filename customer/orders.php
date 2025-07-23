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
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        h1.text-center {
            color: #333;
            font-size: 2rem;
            font-weight: 600;
        }

        .order-table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        }

        .order-table table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }

        .order-table thead {
            background-color: #5d3fd3;
            color: #fff;
        }

        .order-table th,
        .order-table td {
            padding: 1rem;
            text-align: center;
            font-size: 0.95rem;
            border-bottom: 1px solid #eee;
        }

        .order-table tr:hover {
            background-color: #f2f4ff;
        }

        .order-table td:nth-child(3) {
            text-transform: capitalize;
            font-weight: 500;
        }

        .primary-btn.small-btn {
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            border-radius: 6px;
        }

        .bg-white.text-center.p-4.border-rounded {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .footer {
            margin-top: 4rem;
        }

        .container.mt-5 {
            padding-bottom: 2rem;
        }
    </style>
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