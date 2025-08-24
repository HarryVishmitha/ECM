<?php
// index.php
require 'env.php';
require_once 'core/DB_conn.php';
session_start();
$page = 'home';

$categories = mysqli_query($conn, "SELECT name, image FROM categories WHERE status = 'active' LIMIT 4");
$newProducts = "SELECT p.id, p.name, p.price, pi.image_path
          FROM products p
          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
          WHERE p.status = 'active'
          ORDER BY p.created_at DESC
          LIMIT 4";

$newArrivals = mysqli_query($conn, $newProducts);

$shopByLook = mysqli_query($conn, "SELECT id, title, image FROM looks ORDER BY created_at DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home – <?php echo $name; ?></title>
    <meta name="description" content="Discover Velvet Vogue – your destination for trendy casual and elegant formal wear. Shop the latest fashion collections with fast delivery and secure checkout.">
    <meta name="keywords" content="Velvet Vogue, trendy clothing, casual wear, formal wear, fashion Sri Lanka, online clothing store, stylish outfits, young adults fashion">
    <link rel="stylesheet" href="inc/css/stylesheet.css">
    <script src="inc/js/script.js" defer></script>
    <link rel="icon" href="inc/assets/site-images/logo.png" type="image/x-icon">
</head>

<body>


    <section class="hero">
        <div class="hero-overlay">
            <div class="hero-content">
                <div class="logo-container mb-3">
                    <img src="inc/assets/site-images/logo.png" alt="Velvet Vogue Logo" class="logo-icon" />
                    <div class="logo-text">
                        <span class="brand-name text-white">Velvet Vogue</span>
                        <span class="brand-tagline text-white">Clothing</span>
                    </div>
                </div>
                <h1>Define Your Fashion with Velvet Vogue</h1>
                <p>Explore the latest trends in casual & formal wear for modern youth.</p>
                <div class="hero-buttons">
                    <a href="#shop" class="btn primary-btn">Shop Now</a>
                    <a href="#new-arrivals" class="btn secondary-btn">View New Arrivals</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Navigation bar -->
    <?php include 'components/topnav.php'; ?>

    <!-- Category view -->
    <section class="featured-categories mt-3 mb-3">
        <div class="container">
            <h2 class="section-title text-center mb-3">Explore Our Categories</h2>
            <div class="category-grid">
                <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                    <a href="products.php?category=<?= urlencode($category['name']) ?>" class="category-box" data-animate="fade-up">
                        <img class="op-top" src="<?= htmlspecialchars(str_replace('../', '', $category['image'])) ?>" alt="<?= htmlspecialchars($category['name']) ?>">
                        <div class="category-overlay">
                            <h3><?= htmlspecialchars($category['name']) ?></h3>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="new-arrivals mt-3 mb-3">
        <div class="container">
            <h2 class="section-title text-center mb-3">New Arrivals</h2>

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

        <div class="text-center mt-3 centered-btn">
            <a href="products.php" class="btn view-all-btn">View All Products</a>
        </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials mt-3 mb-3">
        <div class="container">
            <h2 class="section-title text-center mb-3">What Our Customers Say</h2>

            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <p class="review">"Absolutely loved the fabric quality and fast delivery! I felt like a queen in the dress."</p>
                    <div class="reviewer">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Customer photo">
                        <div>
                            <h4>Shanuli Perera</h4>
                            <span>Colombo, Sri Lanka</span>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <p class="review">"Velvet Vogue’s men’s wear is 🔥. I’ve been stopped on the street for my outfit!"</p>
                    <div class="reviewer">
                        <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Customer photo">
                        <div>
                            <h4>Isuru Jayasinghe</h4>
                            <span>Kandy, Sri Lanka</span>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <p class="review">"My go-to fashion store now. Affordable, premium, and always in trend."</p>
                    <div class="reviewer">
                        <img src="https://randomuser.me/api/portraits/women/79.jpg" alt="Customer photo">
                        <div>
                            <h4>Dinithi Hansika</h4>
                            <span>Galle, Sri Lanka</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Shop by look -->
    <section class="shop-by-look mt-3 mb-3">
        <div class="container">
            <h2 class="section-title text-center mb-3">Shop By Look</h2>
            <?php if (mysqli_num_rows($shopByLook) > 0): ?>
                <div class="look-grid">
                    <?php while ($look = mysqli_fetch_assoc($shopByLook)): ?>
                        <div class="look-card">
                            <img src="<?= htmlspecialchars(str_replace('../', '', $look['image'])) ?>" alt="<?= htmlspecialchars($look['title']) ?>">
                            <div class="look-info">
                                <h4><?= htmlspecialchars($look['title']) ?></h4>
                                <a href="products.php?look=<?= htmlspecialchars($look['id']) ?>" class="btn small-btn primary-btn">Explore Look</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-center">No looks available at the moment.</p>
            <?php endif; ?>

        </div>
    </section>


    <!-- Newsletter -->
    <section class="newsletter-section mt-3 mb-3">
        <div class="container newsletter-box">
            <h2 class="section-title text-center section-title-bg">Join Our Style Club</h2>
            <p class="newsletter-text text-center">
                Be the first to know about new arrivals, exclusive deals, and style tips.
                Subscribe & get <span class="highlight-yellow">10% OFF</span> your first order!
            </p>

            <form class="newsletter-form" action="#" method="post">
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit" class="btn primary-btn">Subscribe</button>
            </form>
        </div>
    </section>



    <!-- our story -->
    <section class="brand-story mt-3 mb-3">
        <div class="container">
            <div class="story-content">
                <div class="story-text">
                    <h2 class="section-title">Our Story</h2>
                    <p>At <strong>Velvet Vogue</strong>, fashion isn’t just clothing — it's identity. Born in Colombo, we are on a mission to empower young adults with timeless, trendy fashion that reflects their personality, ambition, and creativity.</p>
                    <p>From casual streetwear to elegant eveningwear, our collections are curated with love, quality, and the Velvet Vogue promise — to help you wear your confidence.</p>
                    <a href="#" class="btn primary-btn mt-2">Discover More</a>
                </div>
                <div class="story-image">
                    <img src="https://images.unsplash.com/photo-1503342217505-b0a15ec3261c" alt="Our Brand Image">
                </div>
            </div>
        </div>
    </section>



    <!-- Footer -->
    <?php include 'components/footer.php'; ?>


</body>

</html>