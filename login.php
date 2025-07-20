<?php
// index.php
require 'env.php';
$page = 'login';

session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === "admin") {
        header('Location: admin/dashboard.php');
    } elseif ($_SESSION['user_role'] === "customer") {
        header('Location: user/dashboard.php');
    } else {
        header('Location : index.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – <?php echo $name; ?></title>
    <link rel="stylesheet" href="inc/css/stylesheet.css">
    <script src="inc/js/script.js" defer></script>
    <link rel="icon" href="inc/assets/site-images/logo.png" type="image/x-icon">
</head>

<body class="vv-login-body">
    <div class="vv-login-wrapper">
        <div class="vv-login-card">
            <div class="vv-login-header">
                <img src="inc/assets/site-images/logo.png" alt="Velvet Vogue" class="vv-logo">
                <h2>Welcome Back</h2>
                <p>Login to continue shopping</p>
            </div>
            <form action="core/auth-login.php" method="POST" class="vv-login-form" onsubmit="return validateLoginForm()">
                <div class="vv-form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="vv-email" required placeholder="example@mail.com" />
                </div>
                <div class="vv-form-group vv-password-wrap">
                    <label for="password">Password</label>
                    <div class="vv-password-field">
                        <input type="password" name="password" id="vv-password" required placeholder="••••••••" />
                        <span class="vv-toggle-password" onclick="togglePassword(this)" title="Show Password">👁</span>
                    </div>
                </div>

                <div class="vv-remember-forgot">
                    <label><input type="checkbox" name="remember" /> Remember me</label>
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>
                <button type="submit" class="vv-login-btn">Login</button>
                <p class="vv-login-footer">
                    Don’t have an account? <a href="register.php">Register</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(el) {
            const input = document.getElementById("vv-password");
            const isVisible = input.type === "text";

            input.type = isVisible ? "password" : "text";
            el.textContent = isVisible ? "👁" : "🙈";
            el.title = isVisible ? "Show Password" : "Hide Password";
        }
    </script>

</body>

</html>