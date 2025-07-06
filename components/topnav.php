<?php
// components/topnav.php
?>
<div class="nav-overlay"></div>
<div class="top-nav">
    <div class="container-fluid">
        <div class="topNav-wrapper">
            <div class="logo-section">
                <a href="index.php?page=home" class="logo-link">
                    <div class="logo-container">
                        <img src="inc/assets/site-images/logo.png" alt="Velvet Vogue Logo" class="logo-icon" />
                        <div class="logo-text">
                            <span class="brand-name">Velvet Vogue</span>
                            <span class="brand-tagline">Clothing</span>
                        </div>
                    </div>
                </a>
            </div>
            <button class="nav-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-section">
                <nav class="topbar">
                    <ul>
                        <li class="top-nav-links <?= ($page === 'home') ? 'nav-active' : '' ?>">
                            <a href="index.php?page=home" class="nav-link">Home</a>
                        </li>
                        <li class="top-nav-links <?= ($page === 'products') ? 'nav-active' : '' ?>">
                            <a href="products.php?page=products" class="nav-link">Products</a>
                        </li>
                        <li class="top-nav-links <?= ($page === 'contact') ? 'nav-active' : '' ?>">
                            <a href="contact.php?page=contact" class="nav-link">Contact Us</a>
                        </li>
                        <li class="top-nav-links <?= ($page === 'login') ? 'nav-active' : '' ?>">
                            <a href="login.php?page=login" class="nav-link">Login</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>