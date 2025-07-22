<?php
if (!isset($_SESSION)) session_start();
$name = $_SESSION['user_name'] ?? 'User';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

?>

<nav class="user-dashboard-topnav">
    <div class="user-nav-left">
        <a href="../index.php?page=home" class="logo-text-link">
            <div class="logo-container">
                <img src="../inc/assets/site-images/logo.png" alt="Velvet Vogue Logo" class="logo-icon" />
                <div class="logo-text">
                    <span class="brand-name">Velvet Vogue</span>
                    <span class="brand-tagline">Clothing</span>
                </div>
            </div>
        </a>
    </div>

    <button class="nav-toggle" id="userNavToggle" aria-label="Toggle Navigation">
        <span></span><span></span><span></span>
    </button>

    <div class="user-nav-right" id="userNavMenu">
        <a href="orders.php" class="nav-link">Orders</a>
        <a href="wishlist.php" class="nav-link">Wishlist</a>
        <a href="cart.php" class="nav-icon" title="Cart">
            <img src="../inc/assets/icons/cart.svg" alt="Cart" />
        </a>
        <div class="user-dropdown">
            <span class="dropdown-toggle"><?= htmlspecialchars($name) ?> ▼</span>
            <div class="dropdown-menu">
                <a href="../components/profile_edit.php">Profile</a>
                <a href="../core/logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="nav-overlay" id="userNavOverlay"></div>
</nav>