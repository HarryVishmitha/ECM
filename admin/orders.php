<?php

require '../env.php';
require_once '../core/DB_conn.php';
session_start();
$userName = $_SESSION['user_name'];

$page = 'orders';
$orders = mysqli_query($conn, "
    SELECT o.*, u.name AS customer_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");

// Update status logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['order_status'];

    mysqli_query($conn, "UPDATE orders SET order_status='$new_status' WHERE id=$order_id");
    mysqli_query($conn, "INSERT INTO order_status_history (order_id, status, updated_by) VALUES ($order_id, '$new_status', {$_SESSION['user_id']})");

    header("Location: orders.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – <?php echo $name; ?></title>
    <link rel="stylesheet" href="../inc/css/admin-stylesheet.css">
    <script src="../inc/js/admin-script.js" defer></script>
    <link rel="icon" href="../inc/assets/site-images/logo.png" type="image/x-icon">

    <style>
        .status-badge {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-transform: capitalize;
            color: white;
        }

        .pending {
            background-color: #f9a825;
        }

        .confirmed {
            background-color: #1976d2;
        }

        .shipped {
            background-color: #5e35b1;
        }

        .delivered {
            background-color: #2e7d32;
        }

        .cancelled {
            background-color: #c62828;
        }

        .inline-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .inline-form select {
            padding: 6px 8px;
            border-radius: 5px;
        }

        .inline-form button {
            padding: 6px 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <?php include '../components/user_topnav.php' ?>

    <div class="admin-dashboard">
        <?php include '../components/admin-sidebar.php'; ?>

        <main class="admin-main">
            <div class="dashboard-wrapper">

                <h1 class="admin-title">Manage Orders</h1>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Order Status</th>
                                <th>Placed On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                                <tr>
                                    <td><?= $order['order_number'] ?></td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td>Rs. <?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <?= htmlspecialchars($order['payment_method']) ?><br>
                                        <span class="status-badge <?= $order['payment_status'] ?>"><?= ucfirst($order['payment_status']) ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <select name="order_status">
                                                <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= $order['order_status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-sm">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
            </div>
        </main>
    </div>
</body>

</html>