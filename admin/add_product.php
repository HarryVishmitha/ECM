<?php

require '../env.php';
require_once '../core/DB_conn.php';
$page = 'Products - Admin';
session_start();
$userName = $_SESSION['user_name'];


$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $meta_description = trim($_POST['meta_description']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    $stmt = mysqli_prepare($conn, "INSERT INTO products (category_id, name, slug, meta_description, description, price, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssds", $category_id, $name, $slug, $meta_description, $description, $price, $status);
    if (mysqli_stmt_execute($stmt)) {
        $product_id = mysqli_insert_id($conn);

        // Handle variants
        for ($i = 0; $i < count($_POST['size']); $i++) {
            $size = $_POST['size'][$i];
            $color = $_POST['color'][$i];
            $stock = intval($_POST['stock'][$i]);
            $add_price = floatval($_POST['add_price'][$i]);

            $vstmt = mysqli_prepare($conn, "INSERT INTO product_variants (product_id, size, color, stock, additional_price) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($vstmt, "issid", $product_id, $size, $color, $stock, $add_price);
            mysqli_stmt_execute($vstmt);
        }

        // Handle image upload
        foreach ($_FILES['images']['tmp_name'] as $i => $tmpPath) {
            $filename = basename($_FILES['images']['name'][$i]);
            $target = "../uploads/" . time() . '_' . $filename;
            move_uploaded_file($tmpPath, $target);

            $is_primary = ($i === 0) ? 1 : 0;
            $imgStmt = mysqli_prepare($conn, "INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($imgStmt, "isi", $product_id, $target, $is_primary);
            mysqli_stmt_execute($imgStmt);
        }

        $message = "Product added successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product – <?php echo $name; ?></title>
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
                <h1>Add New Product</h1>

                <?php if ($message): ?>
                    <p class="alert"><?= $message ?></p>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <label>Category:</label>
                    <select name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php
                        $catQuery = mysqli_query($conn, "SELECT id, name FROM categories WHERE status='active'");
                        while ($cat = mysqli_fetch_assoc($catQuery)):
                        ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label>Product Name:</label>
                    <input type="text" name="name" required>

                    <label>Slug (unique):</label>
                    <input type="text" name="slug" required>

                    <label>Price (Base):</label>
                    <input type="number" name="price" step="0.01" required>

                    <label>Meta Description:</label>
                    <textarea name="meta_description"></textarea>

                    <label>Description:</label>
                    <textarea name="description"></textarea>

                    <label>Status:</label>
                    <select name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>

                    <label>Images:</label>
                    <input type="file" name="images[]" multiple required>

                    <hr>
                    <h3>Variants</h3>
                    <div id="variant-container">
                        <div class="variant-row">
                            <input type="text" name="size[]" placeholder="Size" required>
                            <input type="text" name="color[]" placeholder="Color" required>
                            <input type="number" name="stock[]" placeholder="Stock" required>
                            <input type="number" step="0.01" name="add_price[]" placeholder="Additional Price">
                        </div>
                    </div>
                    <button type="button" onclick="addVariant()">+ Add More Variant</button>

                    <br><br>
                    <button type="submit" class="btn">Add Product</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        function addVariant() {
            const container = document.getElementById('variant-container');
            const row = document.createElement('div');
            row.className = 'variant-row';
            row.innerHTML = `
        <input type="text" name="size[]" placeholder="Size" required>
        <input type="text" name="color[]" placeholder="Color" required>
        <input type="number" name="stock[]" placeholder="Stock" required>
        <input type="number" step="0.01" name="add_price[]" placeholder="Additional Price">
    `;
            container.appendChild(row);
        }
    </script>
</body>

</html>