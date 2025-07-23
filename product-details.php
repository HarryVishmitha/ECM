<?php
require 'env.php';
require_once 'core/DB_conn.php';
$page = 'product';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get main product
$productQuery = mysqli_query($conn, "SELECT * FROM products WHERE id = $id AND status = 'active'");
$product = mysqli_fetch_assoc($productQuery);

// If product exists, fetch variants and images
if ($product) {
    // Fetch variants
    // $variantQuery = mysqli_query($conn, "SELECT DISTINCT size FROM product_variants WHERE product_id = $id");
    $variants = [];
    $variantQuery = mysqli_query($conn, "SELECT * FROM product_variants WHERE product_id = $id");
    while ($row = mysqli_fetch_assoc($variantQuery)) {
        $variants[] = $row; // id, size, color, stock, additional_price
    }


    // Fetch images
    $imageQuery = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id = $id");
    $images = [];
    while ($img = mysqli_fetch_assoc($imageQuery)) {
        $images[] = $img['image_path'];
    }
} else {
    $sizes = [];
    $images = [];
}


$newProducts = "SELECT p.id, p.name, p.price, pi.image_path
          FROM products p
          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
          WHERE p.status = 'active'
          ORDER BY p.created_at DESC
          LIMIT 4";

$newArrivals = mysqli_query($conn, $newProducts);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product['name'] ?? 'Product Not Found' ?> – <?= $name ?></title>
    <link rel="stylesheet" href="inc/css/stylesheet.css">
    <script src="inc/js/script.js"></script>
    <link rel="icon" href="inc/assets/site-images/logo.png" type="image/x-icon">

    <script>
        // Make updateColors and updatePrice available globally before DOMContentLoaded
        const productVariants = <?= json_encode($variants) ?>;
        let priceText, stockText, variantIdInput, submitBtn;

        function updateColors() {
            // Ensure elements are available
            priceText = document.getElementById('priceText');
            stockText = document.getElementById('stockText');
            variantIdInput = document.getElementById('variantId');
            submitBtn = document.querySelector('.add-to-cart-btn');

            if (!priceText || !stockText || !variantIdInput) return;

            // reset
            priceText.textContent = "Rs. <?= number_format($product['price'], 2) ?>";
            stockText.textContent = "";
            variantIdInput.value = "";
            if (submitBtn) submitBtn.disabled = true;

            // populate colors
            const size = document.getElementById('sizeSelect').value;
            const colors = [...new Set(
                productVariants
                .filter(v => v.size === size)
                .map(v => v.color)
            )];

            const colorSelect = document.getElementById('colorSelect');
            colorSelect.innerHTML = '<option value="">-- Choose Color --</option>';
            colors.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c;
                opt.text = c;
                colorSelect.appendChild(opt);
            });
        }

        function updatePrice() {
            // Ensure elements are available
            priceText = document.getElementById('priceText');
            stockText = document.getElementById('stockText');
            variantIdInput = document.getElementById('variantId');
            submitBtn = document.querySelector('.add-to-cart-btn');

            if (!priceText || !stockText || !variantIdInput) return;

            const size = document.getElementById('sizeSelect').value;
            const color = document.getElementById('colorSelect').value;
            const base = <?= $product['price'] ?>;
            const match = productVariants.find(v => v.size === size && v.color === color);

            if (match) {
                const final = base + parseFloat(match.additional_price);
                priceText.textContent = "Rs. " + final.toFixed(2);
                stockText.textContent = "Stock: " + match.stock;
                variantIdInput.value = match.id;
                if (submitBtn) submitBtn.disabled = false;
            } else {
                priceText.textContent = "Rs. <?= number_format($product['price'], 2) ?>";
                stockText.textContent = "Out of stock";
                variantIdInput.value = "";
                if (submitBtn) submitBtn.disabled = true;
            }
        }
    </script>

</head>

<body>
    <?php include 'components/topnav.php'; ?>

    <section class="product-header-section">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a> &gt; <a href="products.php?page=products">Products</a> &gt; <span><?= htmlspecialchars($product['name']) ?></span>
            </div>
            <h1 class="product-page-title"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-page-subtitle">View more details of <?= htmlspecialchars($product['name']) ?></p>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="alert alert-error"><?= $_SESSION['error'];
                                                unset($_SESSION['error']); ?></p>
            <?php endif; ?>

        </div>
    </section>

    <?php if ($product): ?>
        <section class="product-details mt-4 mb-5">
            <div class="container prod-grid">
                <div class="prod-image-box">
                    <div class="main-image">
                        <img id="mainProductImage" src="<?= $images[0] ? str_replace('../', '', $images[0]) : 'fallback.jpg' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <div class="thumbnail-gallery">
                        <?php foreach ($images as $index => $img): ?>
                            <img src="<?= str_replace('../', '', $img) ?>" alt="thumb-<?= $index ?>" class="thumbnail" onclick="changeMainImage(this)">
                        <?php endforeach; ?>

                    </div>
                </div>

                <div class="prod-content-box">
                    <h1 class="prod-title-main"><?= htmlspecialchars($product['name']) ?></h1>
                    <span class="stock-badge">In Stock</span>
                    <p id="priceText" class="prod-price">Rs. <?= number_format($product['price'], 2) ?></p>
                    <p id="stockText" class="stock-info"></p>

                    <p class="prod-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

                    <ul class="prod-features">
                        <li>🚚 Free delivery within 3–5 days</li>
                        <li>🔄 7-day easy returns</li>
                        <li>🧼 Machine washable | Cool iron</li>
                    </ul>

                    <form class="prod-form" action="add_to_cart.php" method="post">
                        <!-- <div class="prod-row"> -->
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="variant_id" id="variantId">
                        <div class="prod-row">
                            <label>Select Size:</label>
                            <select id="sizeSelect" name="size" onchange="updateColors()">
                                <option value="">-- Choose Size --</option>
                                <?php
                                $uniqueSizes = array_unique(array_column($variants, 'size'));
                                foreach ($uniqueSizes as $size):
                                ?>
                                    <option value="<?= $size ?>"><?= $size ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="prod-row">
                            <label>Select Color:</label>
                            <select id="colorSelect" name="color" onchange="updatePrice()">
                                <option value="">-- Choose Color --</option>
                                <!-- Colors will be populated via JS -->
                            </select>
                        </div>


                        <!-- </div> -->

                        <div class="prod-row">
                            <label for="qty">Quantity:</label>
                            <div class="qty-group">
                                <button type="button" onclick="adjustQty(-1)">−</button>
                                <input type="number" id="qty" name="quantity" min="1" value="1" />
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

                    <?php while ($product = mysqli_fetch_assoc($newArrivals)): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars(str_replace('../', '', $product['image_path'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="price">Rs. <?= number_format($product['price'], 2) ?></p>
                                <a href="product-details.php?id=<?= $product['id'] ?>" class="btn small-btn primary-btn text-decoration-none">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
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

            const sizeSel = document.getElementById('sizeSelect');
            const colorSel = document.getElementById('colorSelect');

            if (sizeSel) sizeSel.addEventListener('change', updateColors);
            if (colorSel) colorSel.addEventListener('change', updatePrice);
        });
    </script>
</body>

</html>