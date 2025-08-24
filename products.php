<?php
// products.php
require 'env.php';
require_once 'core/DB_conn.php';

$page = 'products';

// site title fallback (in case $name isn't set in env.php)
$siteTitle = isset($name) && $name ? $name : 'Velvet Vogue';

// Read query params
$category = isset($_GET['category']) ? trim($_GET['category']) : null;
if ($category === 'all' || $category === '') $category = null;

$lookId   = isset($_GET['look']) ? (int) $_GET['look'] : null;
$sort     = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$q        = isset($_GET['q']) ? trim($_GET['q']) : null;

// Build WHERE and params safely
$whereParts = ["p.status = 'active'"];
$params = [];
$types  = "";

// Category filter
if ($category) {
    $whereParts[] = "c.name = ?";
    $params[] = $category;
    $types   .= "s";
}

// Look filter
if ($lookId) {
    $whereParts[] = "p.id IN (SELECT product_id FROM look_products WHERE look_id = ?)";
    $params[] = $lookId;
    $types   .= "i";
}

// Price filter
$priceRange = isset($_GET['price']) ? $_GET['price'] : null;
if ($priceRange && $priceRange !== 'all') {
    if ($priceRange === 'under-2000') {
        $whereParts[] = "p.price < 2000";
    } elseif ($priceRange === '2000-5000') {
        $whereParts[] = "p.price BETWEEN 2000 AND 5000";
    } elseif ($priceRange === 'above-5000') {
        $whereParts[] = "p.price > 5000";
    }
}

// Keyword search across name/description/slug/category
if ($q) {
    $whereParts[] = "(p.name LIKE ? OR p.description LIKE ? OR p.slug LIKE ? OR c.name LIKE ?)";
    $like = "%{$q}%";
    array_push($params, $like, $like, $like, $like);
    $types .= "ssss";
}

// Sort
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

$where = "WHERE " . implode(" AND ", $whereParts);

$sql = "SELECT 
            p.*, 
            c.name AS category_name,
            (SELECT image_path FROM product_images 
             WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS main_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        $where
        $orderBy";

$stmt = mysqli_prepare($conn, $sql);

// Bind dynamically with correct types
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Topbar categories (for the select)
$categoryQuery = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");
$categories = mysqli_fetch_all($categoryQuery, MYSQLI_ASSOC);

// Sidebar categories (active only)
$categoryQuery1 = mysqli_query($conn, "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC");
$sidebarCategories = mysqli_fetch_all($categoryQuery1, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products – <?php echo htmlspecialchars($siteTitle); ?></title>
    <link rel="stylesheet" href="inc/css/stylesheet.css" />
    <script src="inc/js/script.js" defer></script>
    <link rel="icon" href="inc/assets/site-images/logo.png" type="image/x-icon" />
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
                        <?php $isSel = ($category === $cat['name']); ?>
                        <option value="<?= urlencode($cat['name']) ?>" <?= $isSel ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="price">Price:</label>
                <select id="price" name="price">
                    <option value="all" <?= (!$priceRange || $priceRange === 'all') ? 'selected' : '' ?>>All</option>
                    <option value="under-2000" <?= ($priceRange === 'under-2000') ? 'selected' : '' ?>>Under Rs. 2000</option>
                    <option value="2000-5000" <?= ($priceRange === '2000-5000') ? 'selected' : '' ?>>Rs. 2000 - 5000</option>
                    <option value="above-5000" <?= ($priceRange === 'above-5000') ? 'selected' : '' ?>>Above Rs. 5000</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="sort">Sort by:</label>
                <select id="sort" name="sort">
                    <option value="newest" <?= ($sort === 'newest') ? 'selected' : ''; ?>>Newest</option>
                    <option value="low-to-high" <?= ($sort === 'low-to-high') ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="high-to-low" <?= ($sort === 'high-to-low') ? 'selected' : ''; ?>>Price: High to Low</option>
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
                    <input type="text" id="search" name="search" placeholder="Search products..."
                        value="<?= htmlspecialchars($q ?? '') ?>">
                </div>

                <!-- Category -->
                <div class="filter-block">
                    <label>Category</label>
                    <ul class="filter-list">
                        <?php foreach ($sidebarCategories as $cat): ?>
                            <li>
                                <?php
                                $checked = ($category === $cat['name']) ? 'checked' : '';
                                $idAttr  = 'cat-' . (int)$cat['id'];
                                ?>
                                <input type="checkbox" id="<?= $idAttr ?>" name="category[]" value="<?= htmlspecialchars($cat['name']) ?>" <?= $checked ?>>
                                <label for="<?= $idAttr ?>"><?= htmlspecialchars($cat['name']) ?></label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Price Range -->
                <div class="filter-block">
                    <label>Price Range</label>
                    <ul class="filter-list">
                        <li><input type="radio" name="priceSidebar" id="p1" <?= ($priceRange === 'under-2000') ? 'checked' : ''; ?>> <label for="p1">Under Rs. 2000</label></li>
                        <li><input type="radio" name="priceSidebar" id="p2" <?= ($priceRange === '2000-5000') ? 'checked' : ''; ?>> <label for="p2">Rs. 2000 - 5000</label></li>
                        <li><input type="radio" name="priceSidebar" id="p3" <?= ($priceRange === 'above-5000') ? 'checked' : ''; ?>> <label for="p3">Above Rs. 5000</label></li>
                    </ul>
                </div>

                <!-- Sort by (sidebar) — different id to avoid duplicate) -->
                <div class="filter-block">
                    <label for="sortSidebar">Sort by</label>
                    <select id="sortSidebar">
                        <option value="newest" <?= ($sort === 'newest') ? 'selected' : ''; ?>>Newest</option>
                        <option value="low-to-high" <?= ($sort === 'low-to-high') ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="high-to-low" <?= ($sort === 'high-to-low') ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
            </aside>

            <!-- Product Grid -->
            <div class="product-grid-area">
                <div class="product-grid">
                    <?php foreach ($products as $p): ?>
                        <?php $image = $p['main_image'] ?: 'inc/assets/uploads/placeholder.png'; ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars(str_replace('../', '', $image)) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($p['name']) ?></h3>
                                <p class="price">Rs. <?= number_format((float)$p['price']) ?></p>
                                <button class="quick-view-btn"
                                    onclick="openQuickView(
                                        `<?= htmlspecialchars($p['name']) ?>`,
                                        `Rs. <?= number_format((float)$p['price']) ?>`,
                                        `<?= htmlspecialchars(str_replace('../', '', $image)) ?>`,
                                        `<?= htmlspecialchars(strip_tags(mb_substr($p['description'] ?? '', 0, 100))) ?>...`,
                                        `product-details.php?id=<?= (int)$p['id'] ?>&slug=<?= urlencode($p['slug']) ?>`
                                    )">
                                    👁 Quick View
                                </button>

                                <a href="product-details.php?id=<?= (int)$p['id'] ?>&slug=<?= urlencode($p['slug']) ?>" class="btn small-btn primary-btn">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($products)): ?>
                        <div class="empty-state">
                            <p>No products found. Try adjusting your filters or search.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

    <!-- Quick View Modal -->
    <div id="quickViewModal" class="quick-view-modal" style="display:none">
        <div class="quick-view-content">
            <span class="close-modal" onclick="closeQuickView()">×</span>
            <div class="modal-image">
                <img src="inc/assets/uploads/placeholder.png" alt="Product Image" id="quickViewImg">
            </div>
            <div class="modal-details">
                <h3 id="quickViewTitle">Product Title</h3>
                <p id="quickViewPrice">Rs. 0.00</p>
                <p id="quickViewDescription">A short description of the product goes here.</p>
                <a id="quickViewLink" href="#" class="btn small-btn primary-btn">View Full Details</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>

    <script>
        (function() {
            // Controls (top bar)
            const catSel = document.getElementById('category');
            const priceSel = document.getElementById('price');
            const sortSel = document.getElementById('sort');

            // Sidebar mirrors
            const sortSidebar = document.getElementById('sortSidebar');
            const sidebarPriceUnder = document.getElementById('p1');
            const sidebarPriceMid = document.getElementById('p2');
            const sidebarPriceAbove = document.getElementById('p3');

            // Search input
            const searchI = document.getElementById('search');

            // Build URL from current UI state
            function buildUrl(overrides = {}) {
                const category = overrides.category ?? (catSel?.value ?? 'all');
                const sort = overrides.sort ?? (sortSel?.value ?? 'newest');
                const price = overrides.price ?? (priceSel?.value ?? 'all');
                const qRaw = overrides.q ?? (searchI?.value || '');
                const q = qRaw.trim();

                const params = new URLSearchParams();
                params.set('category', category);
                params.set('sort', sort);
                params.set('price', price);
                if (q) params.set('q', q);

                return `products.php?${params.toString()}`;
            }

            function go(overrides = {}) {
                window.location.href = buildUrl(overrides);
            }

            // Top select changes
            [catSel, priceSel, sortSel].forEach(el => {
                if (!el) return;
                el.addEventListener('change', () => go());
            });

            // Sidebar "Sort by" mirrors top sort
            if (sortSidebar) {
                sortSidebar.addEventListener('change', () => {
                    if (sortSel) sortSel.value = sortSidebar.value;
                    go();
                });
            }

            // Sidebar price radios mirror top price
            function sidebarToTopPrice(val) {
                if (!priceSel) return;
                priceSel.value = val;
                go();
            }
            if (sidebarPriceUnder) sidebarPriceUnder.addEventListener('change', () => sidebarToTopPrice('under-2000'));
            if (sidebarPriceMid) sidebarPriceMid.addEventListener('change', () => sidebarToTopPrice('2000-5000'));
            if (sidebarPriceAbove) sidebarPriceAbove.addEventListener('change', () => sidebarToTopPrice('above-5000'));

            // Enter to search + debounce
            if (searchI) {
                searchI.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        go();
                    }
                });
                let t = null;
                searchI.addEventListener('input', () => {
                    clearTimeout(t);
                    t = setTimeout(() => {
                        const val = searchI.value.trim();
                        if (val.length >= 2 || val.length === 0) go();
                    }, 400);
                });
            }

            // Quick View fallback (in case not defined in script.js)
            if (typeof window.openQuickView !== 'function') {
                window.openQuickView = function(title, price, img, desc, link) {
                    const modal = document.getElementById('quickViewModal');
                    document.getElementById('quickViewTitle').textContent = title || 'Product';
                    document.getElementById('quickViewPrice').textContent = price || '';
                    document.getElementById('quickViewDescription').textContent = desc || '';
                    document.getElementById('quickViewImg').src = img || 'inc/assets/uploads/placeholder.png';
                    const a = document.getElementById('quickViewLink');
                    a.href = link || '#';
                    modal.style.display = 'block';
                };
            }
            if (typeof window.closeQuickView !== 'function') {
                window.closeQuickView = function() {
                    const modal = document.getElementById('quickViewModal');
                    modal.style.display = 'none';
                };
            }
        })();
    </script>

</body>

</html>