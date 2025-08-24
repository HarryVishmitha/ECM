<?php
require 'env.php';
require_once 'core/DB_conn.php';
$page = 'product';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get main product
$productQuery = mysqli_query($conn, "SELECT * FROM products WHERE id = $id AND status = 'active'");
$product = mysqli_fetch_assoc($productQuery);

// If product exists, fetch variants and images
$variants = [];
$images = [];
$totalStock = 0;

if ($product) {
    // Variants
    $variantQuery = mysqli_query($conn, "SELECT id, product_id, size, color, stock, additional_price FROM product_variants WHERE product_id = $id");
    while ($row = mysqli_fetch_assoc($variantQuery)) {
        $row['stock'] = (int)$row['stock'];
        $row['additional_price'] = (float)$row['additional_price'];
        $variants[] = $row;
        $totalStock += $row['stock'];
    }

    // Images
    $imageQuery = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id = $id ORDER BY is_primary DESC, id ASC");
    while ($img = mysqli_fetch_assoc($imageQuery)) {
        $images[] = $img['image_path'];
    }
} else {
    $variants = [];
    $images = [];
    $totalStock = 0;
}

$newProducts = "SELECT p.id, p.name, p.price, pi.image_path
          FROM products p
          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
          WHERE p.status = 'active'
          ORDER BY p.created_at DESC
          LIMIT 4";

$newArrivals = mysqli_query($conn, $newProducts);

// Helpers for view
$siteName = isset($name) ? $name : 'Velvet Vogue';
$displayTitle = $product ? $product['name'] : 'Product Not Found';
$basePrice = $product ? (float)$product['price'] : 0.00;
$hasStock = $totalStock > 0;
$primaryImage = !empty($images) ? str_replace('../', '', $images[0]) : 'inc/assets/uploads/placeholder.png';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($displayTitle) ?> – <?= htmlspecialchars($siteName) ?></title>
    <link rel="stylesheet" href="inc/css/stylesheet.css">
    <script src="inc/js/script.js"></script>
    <link rel="icon" href="inc/assets/site-images/logo.png" type="image/x-icon">

    <script>
        // Variants data for JS
        const productVariants = <?= json_encode($variants, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const basePrice = <?= json_encode($basePrice) ?>;

        // Cached els
        let priceText, stockText, variantIdInput, submitBtn, sizeSel, colorSel, stockBadge;

        function setAddToCart(enabled) {
            if (!submitBtn) submitBtn = document.querySelector('.add-to-cart-btn');
            if (submitBtn) submitBtn.disabled = !enabled;
        }

        function setStockBadge(inStock) {
            if (!stockBadge) stockBadge = document.querySelector('.stock-badge');
            if (!stockBadge) return;
            stockBadge.textContent = inStock ? 'In Stock' : 'Out of Stock';
            stockBadge.classList.toggle('out', !inStock); // style hook if you want .out { background: #f44; }
        }

        function resetPriceAndStock() {
            if (!priceText) priceText = document.getElementById('priceText');
            if (!stockText) stockText = document.getElementById('stockText');
            if (!variantIdInput) variantIdInput = document.getElementById('variantId');

            if (priceText) priceText.textContent = "Rs. " + (Number(basePrice).toFixed(2));
            if (stockText) stockText.textContent = "";
            if (variantIdInput) variantIdInput.value = "";
        }

        function populateColorsForSize(size) {
            const colorSelect = document.getElementById('colorSelect');
            if (!colorSelect) return;

            colorSelect.innerHTML = '<option value="">-- Choose Color --</option>';

            const colors = [...new Set(
                productVariants
                .filter(v => v.size === size)
                .map(v => v.color)
            )];

            colors.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c;
                opt.text = c;
                colorSelect.appendChild(opt);
            });
        }

        function updateColors() {
            // ensure refs
            priceText = document.getElementById('priceText');
            stockText = document.getElementById('stockText');
            variantIdInput = document.getElementById('variantId');
            submitBtn = document.querySelector('.add-to-cart-btn');
            sizeSel = document.getElementById('sizeSelect');
            colorSel = document.getElementById('colorSelect');
            stockBadge = document.querySelector('.stock-badge');

            resetPriceAndStock();
            setAddToCart(false);

            const size = sizeSel.value;
            if (!size) {
                if (<?= $hasStock ? 'true' : 'false' ?>) {
                    stockText.textContent = '';
                    setStockBadge(true);
                } else {
                    stockText.textContent = 'Out of stock';
                    setStockBadge(false);
                }
                return;
            }

            populateColorsForSize(size);

            // If no colors exist for this size, mark as OOS
            const sizeHasAny = productVariants.some(v => v.size === size && Number(v.stock) > 0);
            if (!sizeHasAny) {
                stockText.textContent = 'Out of stock';
                setStockBadge(false);
            } else {
                stockText.textContent = '';
                setStockBadge(true);
            }
        }

        function updatePrice() {
            // ensure refs
            priceText = document.getElementById('priceText');
            stockText = document.getElementById('stockText');
            variantIdInput = document.getElementById('variantId');
            submitBtn = document.querySelector('.add-to-cart-btn');
            sizeSel = document.getElementById('sizeSelect');
            colorSel = document.getElementById('colorSelect');
            stockBadge = document.querySelector('.stock-badge');

            const size = sizeSel.value;
            const color = colorSel.value;

            // No selection yet
            if (!size || !color) {
                resetPriceAndStock();
                // Global stock state
                if (<?= $hasStock ? 'true' : 'false' ?>) {
                    stockText.textContent = '';
                    setStockBadge(true);
                } else {
                    stockText.textContent = 'Out of stock';
                    setStockBadge(false);
                }
                setAddToCart(false);
                return;
            }

            // Find variant
            const match = productVariants.find(v => v.size === size && v.color === color);
            if (!match) {
                resetPriceAndStock();
                stockText.textContent = 'Out of stock';
                setAddToCart(false);
                setStockBadge(false);
                return;
            }

            const final = Number(basePrice) + Number(match.additional_price || 0);
            priceText.textContent = "Rs. " + final.toFixed(2);

            const stk = Number(match.stock || 0);
            if (stk > 0) {
                stockText.textContent = "Stock: " + stk;
                variantIdInput.value = match.id;
                setAddToCart(true);
                setStockBadge(true);
            } else {
                stockText.textContent = "Out of stock";
                variantIdInput.value = "";
                setAddToCart(false);
                setStockBadge(false);
            }
        }

        // Qty controls (unchanged)
        function adjustQty(delta) {
            const qty = document.getElementById('qty');
            if (!qty) return;
            const cur = parseInt(qty.value || '1', 10);
            const next = Math.max(1, cur + delta);
            qty.value = next;
        }

        // Image swap
        function changeMainImage(el) {
            const target = document.getElementById('mainProductImage');
            if (target && el && el.src) target.src = el.src;
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Cache refs
            sizeSel = document.getElementById('sizeSelect');
            colorSel = document.getElementById('colorSelect');
            submitBtn = document.querySelector('.add-to-cart-btn');
            stockBadge = document.querySelector('.stock-badge');

            // Initial stock badge based on total stock
            setStockBadge(<?= $hasStock ? 'true' : 'false' ?>);

            // If there are no variants or no total stock, disable immediately
            <?php if (!$hasStock || empty($variants)): ?>
                setAddToCart(false);
                const st = document.getElementById('stockText');
                if (st) st.textContent = 'Out of stock';
            <?php endif; ?>

            if (sizeSel) sizeSel.addEventListener('change', updateColors);
            if (colorSel) colorSel.addEventListener('change', updatePrice);
        });

        // Make globally available (your existing HTML calls these)
        window.updateColors = updateColors;
        window.updatePrice = updatePrice;
        window.adjustQty = adjustQty;
        window.changeMainImage = changeMainImage;
    </script>
</head>

<body>
    <?php include 'components/topnav.php'; ?>

    <?php if ($product): ?>
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

        <section class="product-details mt-4 mb-5">
            <div class="container prod-grid">
                <div class="prod-image-box">
                    <div class="main-image">
                        <img id="mainProductImage" src="<?= htmlspecialchars($primaryImage) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <div class="thumbnail-gallery">
                        <?php foreach ($images as $index => $img): ?>
                            <img src="<?= htmlspecialchars(str_replace('../', '', $img)) ?>" alt="thumb-<?= (int)$index ?>" class="thumbnail" onclick="changeMainImage(this)">
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="prod-content-box">
                    <h1 class="prod-title-main"><?= htmlspecialchars($product['name']) ?></h1>
                    <span class="stock-badge <?= $hasStock ? '' : 'out' ?>"><?= $hasStock ? 'In Stock' : 'Out of Stock' ?></span>

                    <p id="priceText" class="prod-price">Rs. <?= number_format($basePrice, 2) ?></p>
                    <p id="stockText" class="stock-info"><?= $hasStock ? '' : 'Out of stock' ?></p>

                    <p class="prod-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

                    <ul class="prod-features">
                        <li>🚚 Free delivery within 3–5 days</li>
                        <li>🔄 7-day easy returns</li>
                        <li>🧼 Machine washable | Cool iron</li>
                    </ul>

                    <form class="prod-form" action="add_to_cart.php" method="post">
                        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                        <input type="hidden" name="variant_id" id="variantId">

                        <div class="prod-row">
                            <label>Select Size:</label>
                            <select id="sizeSelect" name="size" onchange="updateColors()" <?= (!$hasStock || empty($variants)) ? 'disabled' : '' ?>>
                                <option value="">-- Choose Size --</option>
                                <?php
                                $uniqueSizes = array_unique(array_map(function ($v) {
                                    return $v['size'];
                                }, $variants));
                                foreach ($uniqueSizes as $size):
                                ?>
                                    <option value="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="prod-row">
                            <label>Select Color:</label>
                            <select id="colorSelect" name="color" onchange="updatePrice()" <?= (!$hasStock || empty($variants)) ? 'disabled' : '' ?>>
                                <option value="">-- Choose Color --</option>
                            </select>
                        </div>

                        <div class="prod-row">
                            <label for="qty">Quantity:</label>
                            <div class="qty-group">
                                <button type="button" onclick="adjustQty(-1)">−</button>
                                <input type="number" id="qty" name="quantity" min="1" value="1" <?= $hasStock ? '' : 'disabled' ?> />
                                <button type="button" onclick="adjustQty(1)">+</button>
                            </div>
                        </div>

                        <button type="submit" class="add-to-cart-btn" <?= $hasStock ? '' : 'disabled' ?>>Add to Cart</button>
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
                    <?php while ($rp = mysqli_fetch_assoc($newArrivals)): ?>
                        <?php
                        $rpImg = $rp['image_path'] ? str_replace('../', '', $rp['image_path']) : 'inc/assets/uploads/placeholder.png';
                        ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($rpImg) ?>" alt="<?= htmlspecialchars($rp['name']) ?>">
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($rp['name']) ?></h3>
                                <p class="price">Rs. <?= number_format((float)$rp['price'], 2) ?></p>
                                <a href="product-details.php?id=<?= (int)$rp['id'] ?>" class="btn small-btn primary-btn text-decoration-none">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>

    <?php else: ?>
        <section class="product-header-section">
            <div class="container">
                <h1>Product not found.</h1>
            </div>
        </section>
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

                    document.querySelectorAll(".accordion-item").forEach(i => {
                        i.classList.remove("active");
                        i.querySelector(".accordion-toggle").setAttribute("aria-expanded", "false");
                        i.querySelector(".accordion-icon").textContent = "+";
                    });

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