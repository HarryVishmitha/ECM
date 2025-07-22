<?php

require '../env.php';
require_once '../core/DB_conn.php';
$page = 'User Dashboard';
session_start();

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$query = "
    SELECT w.id AS wishlist_id, p.id AS product_id, p.name, p.price,
           (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$wishlistItems = mysqli_fetch_all($result, MYSQLI_ASSOC);
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

    <div class="container mt-3">
        <h1 class="mb-3 text-center">❤️ My Wishlist</h1>

        <?php if (empty($wishlistItems)): ?>
            <p class="bg-white text-center p-4 border-rounded">Your wishlist is empty. <a href="../index.php?page=shop" class="primary-btn small-btn">Browse Products</a></p>
        <?php else: ?>
            <div class="wishlist-grid bg-white">
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="wishlist-card">
                        <img src="<?= $item['image'] ?? '../inc/assets/images/default.jpg' ?>" alt="<?= $item['name'] ?>" class="wishlist-img">
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <p class="price">Rs. <?= number_format($item['price'], 2) ?></p>
                        <div class="wishlist-actions">
                            <a href="../cart/add.php?product_id=<?= $item['product_id'] ?>" class="primary-btn small-btn">Add to Cart</a>
                            <a href="remove-wishlist.php?id=<?= $item['wishlist_id'] ?>" class="secondary-btn small-btn">Remove</a>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer mt-3">
        <?php include '../components/footer.php'; ?>
    </div>

</body>

</html>