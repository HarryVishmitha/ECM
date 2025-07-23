<?php

require '../env.php';
require_once '../core/DB_conn.php';
$page = 'Products - Admin';
session_start();
$userName = $_SESSION['user_name'];

// Handle Delete
if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];

    // Delete related records
    mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $deleteId");
    mysqli_query($conn, "DELETE FROM product_variants WHERE product_id = $deleteId");
    mysqli_query($conn, "DELETE FROM look_product WHERE product_id = $deleteId");

    // Delete the product
    mysqli_query($conn, "DELETE FROM products WHERE id = $deleteId");

    header("Location: products.php?deleted=1");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products – <?php echo $name; ?></title>
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

                <h1 class="admin-title">Products</h1>
                <?php if (isset($_GET['deleted'])): ?>
                    <p class="alert alert-success">Product deleted successfully.</p>
                <?php endif; ?>
                <a href="add_product.php" class="btn btn-primary">+ Add New Product</a>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Price</th>
                                <th>Total Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT 
                                p.id, p.name AS product_name, p.status, p.price, c.name AS category_name,
                                (SELECT SUM(stock) FROM product_variants WHERE product_id = p.id) AS total_stock
                              FROM products p
                              LEFT JOIN categories c ON p.category_id = c.id
                              ORDER BY p.created_at DESC";

                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)):
                            ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><?= htmlspecialchars($row['category_name']) ?? 'Uncategorized' ?></td>
                                    <td><?= ucfirst($row['status']) ?></td>
                                    <td>Rs. <?= number_format($row['price'], 2) ?></td>
                                    <td><?= $row['total_stock'] ?? 0 ?></td>
                                    <td>
                                        <a href="add_product.php?id=<?= $row['id'] ?>" class="btn btn-sm">Edit/View</a>
                                        <a href="products.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?');">Delete</a>

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