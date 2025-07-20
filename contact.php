<?php
// index.php
require 'env.php';
$page = 'Contact Us';
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
                <a href="index.php">Home</a> &gt; <span>Contact Us</span>
            </div>
            <h1 class="product-page-title">Reach Us</h1>
            <p class="product-page-subtitle">If you have any problem, don't hesitate to call us.</p>
        </div>
    </section>

    <section class="contact-header mt-3 mb-3">
        <div class="container text-center">
            <h1 class="section-title">Get in Touch</h1>
            <p class="text-muted">Have a question or feedback? We'd love to hear from you.</p>
        </div>
    </section>

    <section class="contact-content mb-3">
        <div class="container contact-grid">

            <!-- Contact Info -->
            <div class="contact-info">
                <h3>Contact Details</h3>
                <p><strong>📍 Address:</strong> 123 Vogue Street, Colombo, Sri Lanka</p>
                <p><strong>📞 Phone:</strong> +94 77 123 4567</p>
                <p><strong>📧 Email:</strong> hello@velvetvogue.lk</p>
                <p><strong>⏰ Hours:</strong> Mon - Sat: 9AM – 7PM</p>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <h3>Send Us a Message</h3>
                <form action="#" method="post">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <textarea name="message" rows="5" placeholder="Your Message..." required></textarea>
                    <button type="submit" class="send-msg">Send Message</button>
                </form>
            </div>

        </div>
    </section>

    <?php include 'components/footer.php'; ?>
</body>

</html>