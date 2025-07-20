<?php

require '../env.php';
require_once '../core/DB_conn.php';
$page = 'Admin Dashboard';
session_start();
$userName = $_SESSION['user_name'];

// Fetch counts from DB
$productCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'];
$categoryCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM categories"))['total'];
$customerCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='customer'"))['total'];
$orderCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'];
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
</head>

<body>
    <?php include '../components/user_topnav.php' ?>

    <div class="admin-dashboard">
        <?php include '../components/admin-sidebar.php'; ?>

        <main class="admin-main">
            <div class="dashboard-wrapper">
                <header>
                    <h1>Welcome, <?= $userName ?></h1>
                    <p>Here's what's happening with your store today.</p>
                </header>

                <section class="dashboard-cards">
                    <div class="card">
                        <h3>Total Products</h3>
                        <p><?= $productCount ?></p>
                    </div>
                    <div class="card">
                        <h3>Categories</h3>
                        <p><?= $categoryCount ?></p>
                    </div>
                    <div class="card">
                        <h3>Registered Customers</h3>
                        <p><?= $customerCount ?></p>
                    </div>
                    <div class="card">
                        <h3>Orders</h3>
                        <p><?= $orderCount ?></p>
                    </div>
                </section>

                <section class="quick-links">
                    <a href="admin/products.php" class="btn">Manage Products</a>
                    <a href="admin/orders.php" class="btn">Manage Orders</a>
                    <a href="admin/categories.php" class="btn">Manage Categories</a>
                    <a href="admin/customers.php" class="btn">View Customers</a>
                </section>

                <section class="recent-orders">
                    <h2>Recent Orders</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Placed On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orders = mysqli_query($conn, "SELECT o.id, u.name, o.order_status, o.total_amount, o.created_at 
                                         FROM orders o 
                                         JOIN users u ON o.user_id = u.id 
                                         ORDER BY o.created_at DESC 
                                         LIMIT 5");
                            while ($row = mysqli_fetch_assoc($orders)): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['order_status']) ?></td>
                                    <td>Rs. <?= number_format($row['total_amount'], 2) ?></td>
                                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>

</body>

</html>