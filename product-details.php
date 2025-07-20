<?php
require 'env.php';
$page = 'product';

// Simulate product DB
$products = [
    1 => [
        "title" => "Boho Blouse",
        "price" => "Rs. 2,700",
        "images" => ["category-women.jpg", "category-men.jpg", "boho3.jpg"],
        "description" => "A breathable bohemian blouse perfect for sunny days.",
        "sizes" => ["S", "M", "L", "XL"]
    ],
    2 => [
        "title" => "Men's Slim Fit Shirt",
        "price" => "Rs. 3,950",
        "images" => ["shirt1.jpg", "shirt2.jpg"],
        "description" => "A sharp, modern fit shirt for all-day comfort.",
        "sizes" => ["M", "L", "XL"]
    ],
];


// Get ID from query
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$product = $products[$id] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['title']; ?> – <?php echo $name; ?></title>
    <link rel="stylesheet" href="inc/css/stylesheet.css">
    <script src="inc/js/script.js" defer></script>
    <link rel="icon" href="inc/assets/site-images/logo.png" type="image/x-icon">
</head>

<body>
    <?php include 'components/topnav.php'; ?>

    <section class="product-header-section">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a> &gt; <a href="products.php?page=products">Products</a> &gt; <span><?php echo $product['title']; ?></span>
            </div>
            <h1 class="product-page-title"><?php echo $product['title']; ?></h1>
            <p class="product-page-subtitle">View more details of <?php echo $product['title']; ?></p>
        </div>
    </section>

    <?php if ($product): ?>
        <section class="product-details mt-4 mb-5">
            <div class="container prod-grid">
                <div class="prod-image-box">
                    <div class="main-image">
                        <img id="mainProductImage" src="inc/assets/site-images/<?php echo $product['images'][0]; ?>" alt="<?php echo $product['title']; ?>">
                    </div>
                    <div class="thumbnail-gallery">
                        <?php foreach ($product['images'] as $index => $img): ?>
                            <img src="inc/assets/site-images/<?php echo $img; ?>" alt="thumb-<?php echo $index; ?>" class="thumbnail" onclick="changeMainImage(this)">
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="prod-content-box">
                    <h1 class="prod-title-main"><?php echo $product['title']; ?></h1>
                    <span class="stock-badge">In Stock</span>
                    <p class="prod-price"><?php echo $product['price']; ?></p>
                    <p class="prod-description"><?php echo $product['description']; ?></p>

                    <ul class="prod-features">
                        <li>🚚 Free delivery within 3–5 days</li>
                        <li>🔄 7-day easy returns</li>
                        <li>🧼 Machine washable | Cool iron</li>
                    </ul>

                    <form class="prod-form" action="#" method="post">
                        <div class="prod-row">
                            <label for="size">
                                Select Size:
                                <a href="#" onclick="openSizeGuide(event)" class="size-guide-link">(Size Guide)</a>
                            </label>
                            <select id="size" name="size">
                                <?php foreach ($product['sizes'] as $size): ?>
                                    <option value="<?php echo $size; ?>"><?php echo $size; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="prod-row">
                            <label for="qty">Quantity:</label>
                            <div class="qty-group">
                                <button type="button" onclick="adjustQty(-1)">−</button>
                                <input type="number" id="qty" name="qty" min="1" value="1" />
                                <button type="button" onclick="adjustQty(1)">+</button>
                            </div>
                        </div>

                        <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                    </form>

                    <div class="accordion" id="productAccordion">
                        <div class="accordion-item">
                            <button class="accordion-toggle" aria-expanded="true">
                                <span class="accordion-label">Delivery Info</span>
                                <span class="accordion-icon">+</span>
                            </button>
                            <div class="accordion-content">
                                Delivered in 3–5 working days across Sri Lanka.
                            </div>
                        </div>
                        <div class="accordion-item">
                            <button class="accordion-toggle" aria-expanded="false">
                                <span class="accordion-label">Return Policy</span>
                                <span class="accordion-icon">+</span>
                            </button>
                            <div class="accordion-content">
                                Return within 7 days for a full refund or exchange.
                            </div>
                        </div>
                        <div class="accordion-item">
                            <button class="accordion-toggle" aria-expanded="false">
                                <span class="accordion-label">Garment Care</span>
                                <span class="accordion-icon">+</span>
                            </button>
                            <div class="accordion-content">
                                Machine wash at 30°C. Do not bleach. Iron on low.
                            </div>
                        </div>
                    </div>


                    <div class="payment-icons">
                        <div class="weAppcet be-">We accept:</div>
                        <div class="payment-logos">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Mastercard-logo.png" alt="Mastercard">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal">
                        </div>
                    </div>


                </div>
            </div>
        </section>

        <section class="related-products">
            <div class="container">
                <h2>Related Products</h2>
                <div class="product-grid">
                    <?php
                    $products = [
                        ["id" => "1", "slug" => "boho-blouse", "title" => "Boho Blouse", "price" => "Rs. 2,700", "img" => "category-women.jpg", "description" => "A breathable bohemian blouse perfect for sunny days."],
                        ["id" => "1", "slug" => "boho-blouse", "title" => "Slim Fit Shirt", "price" => "Rs. 3,950", "img" => "category-men.jpg", "description" => "A stylish slim fit shirt for a modern look."],
                        ["id" => "1", "slug" => "boho-blouse", "title" => "Leather Wallet", "price" => "Rs. 1,500", "img" => "category-accessories.jpeg", "description" => "A classic leather wallet with multiple card slots."],
                        ["id" => "1", "slug" => "boho-blouse", "title" => "Summer Dress", "price" => "Rs. 4,200", "img" => "category-seasonal.jpg", "description" => "A light and airy summer dress for casual outings."],
                        ["id" => "1", "slug" => "boho-blouse", "title" => "Knitted Sweater", "price" => "Rs. 5,200", "img" => "category-seasonal.jpg", "description" => "A breathable bohemian blouse perfect for sunny days."],
                        ["id" => "1", "slug" => "boho-blouse", "title" => "Crossbody Bag", "price" => "Rs. 3,000", "img" => "category-accessories.jpeg", "description" => "A breathable bohemian blouse perfect for sunny days."],
                        ["id" => "1", "slug" => "boho-blouse", "title" => "Floral Skirt", "price" => "Rs. 2,450", "img" => "category-women.jpg", "description" => "A breathable bohemian blouse perfect for sunny days."],
                        ["id" => "1", "slug" => "boho-blouse", "title" => "Denim Jacket", "price" => "Rs. 6,000", "img" => "category-men.jpg", "description" => "A breathable bohemian blouse perfect for sunny days."],
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
                                    <a href="product-details.php?id=' . $p["id"] . '&slug=' . $p["slug"] . '" class="btn small-btn primary-btn text-decoration-none view-details">View Details</a>
                                    <button class="quick-view-btn" onclick="openQuickView(\'' . $title . '\', \'' . $price . '\', \'' . $img . '\', \'' . $desc . '\')">
                                        👁 Quick View
                                    </button>
                                </div>
                            </div>';
                    }
                    ?>
                </div>
            </div>
        </section>

    <?php else: ?>
        <p class="container text-center">Product not found.</p>
    <?php endif; ?>

    <div id="prodSizeGuideModal" class="prod-modal-backdrop">
        <div class="prod-modal-box">
            <span class="prod-close-modal" onclick="closeSizeGuide()">×</span>
            <h3>Size Guide</h3>
            <table class="prod-size-table">
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>Chest</th>
                        <th>Waist</th>
                        <th>Length</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>S</td>
                        <td>34-36"</td>
                        <td>28-30"</td>
                        <td>25"</td>
                    </tr>
                    <tr>
                        <td>M</td>
                        <td>38-40"</td>
                        <td>31-33"</td>
                        <td>26"</td>
                    </tr>
                    <tr>
                        <td>L</td>
                        <td>42-44"</td>
                        <td>34-36"</td>
                        <td>27"</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toggles = document.querySelectorAll(".accordion-toggle");

            toggles.forEach(toggle => {
                toggle.addEventListener("click", () => {
                    const item = toggle.closest(".accordion-item");
                    const isActive = item.classList.contains("active");

                    // Close all
                    document.querySelectorAll(".accordion-item").forEach(i => {
                        i.classList.remove("active");
                        i.querySelector(".accordion-toggle").setAttribute("aria-expanded", "false");
                        i.querySelector(".accordion-icon").textContent = "+";
                    });

                    // Toggle clicked one
                    if (!isActive) {
                        item.classList.add("active");
                        toggle.setAttribute("aria-expanded", "true");
                        toggle.querySelector(".accordion-icon").textContent = "−";
                    }
                });
            });
        });
    </script>
</body>

</html>