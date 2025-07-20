<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<header class="admin-topnav">
    <div class="admin-logo">
        <a href="../index.php?page=home" class="logo-link">
            <div class="logo-container">
                <img src="../inc/assets/site-images/logo.png" alt="Velvet Vogue Logo" class="logo-icon" />
                <div class="logo-text">
                    <span class="brand-name">Velvet Vogue</span>
                    <span class="brand-tagline">Clothing</span>
                </div>
            </div>
        </a>
    </div>

    <div class="admin-welcome">
        Welcome back, <strong><?php echo $_SESSION['user_name']; ?></strong>
    </div>

    <div class="admin-user-dropdown">
        <button class="dropdown-toggle" onclick="toggleDropdown()">👤</button>
        <ul id="adminDropdown" class="dropdown-menu">
            <li><a href="../components/profile_edit.php">Edit Profile</a></li>
            <li><a href="../core/logout.php">Logout</a></li>
        </ul>
    </div>
</header>