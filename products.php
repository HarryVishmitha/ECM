<?php
// index.php
require 'env.php';
require_once 'core/DB_conn.php';

$page = 'products';

$category = isset($_GET['category']) ? trim($_GET['category']) : null;
$lookId   = isset($_GET['look']) ? (int) $_GET['look'] : null;
$sort     = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$where = "WHERE p.status = 'active'";
$params = [];

if ($category) {
    $where .= " AND c.name = ?";
    $params[] = $category;
}

if ($lookId) {
    $where .= " AND p.id IN (SELECT product_id FROM look_products WHERE look_id = ?)";
    $params[] = $lookId;
}

switch ($sort) {
    case 'low-to-high':
        $orderBy = "ORDER BY p.price ASC";
        break;
    case 'high-to-low':
        $orderBy = "ORDER BY p.price DESC";
        break;
    default:
        $orderBy = "ORDER BY p.created_at DESC";
        break;
}

$priceRange = isset($_GET['price']) ? $_GET['price'] : null;
if ($priceRange) {
    switch ($priceRange) {
        case 'under-2000':
            $where .= " AND p.price < 2000";
            break;
        case '2000-5000':
            $where .= " AND p.price BETWEEN 2000 AND 5000";
            break;
        case 'above-5000':
            $where .= " AND p.price > 5000";
            break;
    }
}



$sql = "SELECT 
            p.*, 
            c.name AS category_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS main_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        $where
        $orderBy";

$stmt = mysqli_prepare($conn, $sql);

// Bind parameters dynamically
if (!empty($params)) {
    $types = str_repeat("s", count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);

$categoryQuery = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");
$categories = mysqli_fetch_all($categoryQuery, MYSQLI_ASSOC);

$categoryQuery1 = mysqli_query($conn, "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC");
$sidebarCategories = mysqli_fetch_all($categoryQuery1, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products – <?php echo $name; ?></title>
    <link rel="stylesheet" href="inc/css/stylesheet.css">
    <script src="inc/js/script.js" defer></script>
    <link rel="icon" href="inc/assets/site-images/logo.png" type="image/x-icon">
</head>

<body>

    <!-- Navigation bar -->
    <?php include 'components/topnav.php'; ?>

    <!-- Breadcrumb -->
    <section class="product-header-section">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a> &gt; <span>Products</span>
            </div>
            <h1 class="product-page-title">All Products</h1>
            <p class="product-page-subtitle">Discover trendy and timeless pieces that fit your identity.</p>
        </div>
    </section>

    <!-- filter bar -->
    <section class="product-filters">
        <div class="container filter-bar">
            <div class="filter-group">
                <label for="category">Category:</label>
                <select id="category" name="category">
                    <option value="all">All</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= urlencode($cat['name']) ?>" <?= ($category === $cat['name']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            </div>

            <div class="filter-group">
                <label for="price">Price:</label>
                <select id="price" name="price">
                    <option value="all">All</option>
                    <option value="under-2000">Under Rs. 2000</option>
                    <option value="2000-5000">Rs. 2000 - 5000</option>
                    <option value="above-5000">Above Rs. 5000</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="sort">Sort by:</label>
                <select id="sort" name="sort">
                    <option value="newest">Newest</option>
                    <option value="low-to-high">Price: Low to High</option>
                    <option value="high-to-low">Price: High to Low</option>
                </select>
            </div>
        </div>
    </section>


    <!-- sidebar + product grid -->
    <section class="product-content-wrapper mt-3 mb-3">
        <div class="container product-layout">

            <!-- Sidebar Filters -->
            <aside class="filter-sidebar">
                <h3 class="filter-heading">Filters</h3>

                <!-- Search -->
                <div class="filter-block">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" placeholder="Search products...">
                </div>

                <!-- Category -->
                <div class="filter-block">
                    <label>Category</label>
                    <ul class="filter-list">
                        <?php foreach ($sidebarCategories as $cat): ?>
                            <li>
                                <input type="checkbox" id="cat-<?= $cat['id'] ?>" name="category[]" value="<?= htmlspecialchars($cat['name']) ?>" <?= ($category === $cat['name']) ? 'checked' : '' ?>>
                                <label for="cat-<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Price Range -->
                <div class="filter-block">
                    <label>Price Range</label>
                    <ul class="filter-list">
                        <li><input type="radio" name="price" id="p1"> <label for="p1">Under Rs. 2000</label></li>
                        <li><input type="radio" name="price" id="p2"> <label for="p2">Rs. 2000 - 5000</label></li>
                        <li><input type="radio" name="price" id="p3"> <label for="p3">Above Rs. 5000</label></li>
                    </ul>
                </div>

                <!-- Sort by -->
                <div class="filter-block">
                    <label for="sort">Sort by</label>
                    <select id="sort">
                        <option value="newest">Newest</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                    </select>
                </div>
            </aside>

            <!-- Product Grid -->
            <div class="product-grid-area">
                <div class="product-grid">
                    <?php foreach ($products as $p): ?>
                        <?php $image = $p['main_image'] ?? 'inc/assets/uploads/placeholder.png'; ?>
                        <div class="product-card">
                            <div class="product-image">
                               <img src="<?= str_replace('../', '', $image) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($p['name']) ?></h3>
                                <p class="price">Rs. <?= number_format($p['price']) ?></p>
                                <button class="quick-view-btn"
                                    onclick="openQuickView(`<?= htmlspecialchars($p['name']) ?>`, `Rs. <?= number_format($p['price']) ?>`, `<?= str_replace('../', '', $image) ?>`, `<?= htmlspecialchars(strip_tags(substr($p['description'], 0, 100))) ?>...`, `product-details.php?id=<?= $p['id'] ?>&slug=<?= urlencode($p['slug']) ?>`)">
                                    👁 Quick View
                                </button>

                                <a href="product-details.php?id=<?= $p['id'] ?>&slug=<?= urlencode($p['slug']) ?>" class="btn small-btn primary-btn">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

            </div>
        </div>
    </section>

    <!-- Quick View Modal -->
    <div id="quickViewModal" class="quick-view-modal">
        <div class="quick-view-content">
            <span class="close-modal" onclick="closeQuickView()">×</span>
            <div class="modal-image">
                <img src="<?= str_replace('../', '', $image) ?>" alt="Product Image" id="quickViewImg">
            </div>
            <div class="modal-details">
                <h3 id="quickViewTitle">Product Title</h3>
                <p id="quickViewPrice">Rs. 0.00</p>
                <p id="quickViewDescription">A short description of the product goes here.</p>
                <a href="#" class="btn small-btn primary-btn">View Full Details</a>
            </div>
        </div>
    </div>





    <!-- Footer -->
    <?php include 'components/footer.php'; ?>

    <script>
        document.querySelectorAll('select').forEach(el => {
            el.addEventListener('change', () => {
                const category = document.getElementById('category').value;
                const sort = document.getElementById('sort').value;
                const price = document.getElementById('price').value;

                let url = `products.php?category=${category}&sort=${sort}&price=${price}`;
                window.location.href = url;
            });
        });
    </script>

</body>

</html>