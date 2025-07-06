<?php
// index.php
require 'env.php';
$page = 'products';
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
                    <option value="men">Men</option>
                    <option value="women">Women</option>
                    <option value="accessories">Accessories</option>
                    <option value="seasonal">Seasonal</option>
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
                        <li><input type="checkbox" id="men"> <label for="men">Men</label></li>
                        <li><input type="checkbox" id="women"> <label for="women">Women</label></li>
                        <li><input type="checkbox" id="accessories"> <label for="accessories">Accessories</label></li>
                        <li><input type="checkbox" id="seasonal"> <label for="seasonal">Seasonal</label></li>
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
                    <?php
                    $products = [
                        ["title" => "Boho Blouse", "price" => "Rs. 2,700", "img" => "category-women.jpg", "description" => "A breathable bohemian blouse perfect for sunny days."],
                        ["title" => "Slim Fit Shirt", "price" => "Rs. 3,950", "img" => "category-men.jpg", "description" => "A stylish slim fit shirt for a modern look."],
                        ["title" => "Leather Wallet", "price" => "Rs. 1,500", "img" => "category-accessories.jpeg", "description" => "A classic leather wallet with multiple card slots."],
                        ["title" => "Summer Dress", "price" => "Rs. 4,200", "img" => "category-seasonal.jpg", "description" => "A light and airy summer dress for casual outings."],
                        ["title" => "Knitted Sweater", "price" => "Rs. 5,200", "img" => "category-seasonal.jpg", "description" => "A breathable bohemian blouse perfect for sunny days."],
                        ["title" => "Crossbody Bag", "price" => "Rs. 3,000", "img" => "category-accessories.jpeg", "description" => "A breathable bohemian blouse perfect for sunny days."],
                        ["title" => "Floral Skirt", "price" => "Rs. 2,450", "img" => "category-women.jpg", "description" => "A breathable bohemian blouse perfect for sunny days."],
                        ["title" => "Denim Jacket", "price" => "Rs. 6,000", "img" => "category-men.jpg", "description" => "A breathable bohemian blouse perfect for sunny days."],
                    ];
                    foreach ($products as $p) {
                        // Escape quotes for safety
                        $title = htmlspecialchars($p["title"], ENT_QUOTES);
                        $price = htmlspecialchars($p["price"], ENT_QUOTES);
                        $img = 'inc/assets/site-images/' . $p["img"];
                        $desc = isset($p["description"]) ? htmlspecialchars($p["description"], ENT_QUOTES) : 'No description available.';

                        echo '
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="' . $img . '" alt="' . $title . '">
                                </div>
                                <div class="product-info">
                                    <h3>' . $title . '</h3>
                                    <p class="price">' . $price . '</p>
                                    <a href="#" class="btn small-btn primary-btn text-decoration-none view-details">View Details</a>
                                    <button class="quick-view-btn" onclick="openQuickView(\'' . $title . '\', \'' . $price . '\', \'' . $img . '\', \'' . $desc . '\')">
                                        👁 Quick View
                                    </button>
                                </div>
                            </div>';
                    }
                    ?>
                </div>
                <div class="product-pagination">
                    <ul class="pagination">
                        <li><a href="#" class="page-link">« Prev</a></li>
                        <li><a href="#" class="page-link active">1</a></li>
                        <li><a href="#" class="page-link">2</a></li>
                        <li><a href="#" class="page-link">3</a></li>
                        <li><a href="#" class="page-link">Next »</a></li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- Quick View Modal -->
    <div id="quickViewModal" class="quick-view-modal">
        <div class="quick-view-content">
            <span class="close-modal" onclick="closeQuickView()">×</span>
            <div class="modal-image">
                <img src="" alt="Product Image" id="quickViewImg">
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

</body>

</html>