<?php
session_start();
$page = 'logout';
$alert = null;

// Destroy the session
session_unset();
session_destroy();

// Optional: remove session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Setup logout alert
$alert = [
    'type' => 'success',
    'msg' => 'You have been successfully logged out. Redirecting to homepage...'
];
$redirectURL = '../index.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Logout – Velvet Vogue</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="inc/css/stylesheet.css">
    <link rel="icon" href="inc/assets/site-images/logo.png" type="image/x-icon">
    <style>
        body.vv-login-status {
            background: linear-gradient(120deg, #5d3fd3, #fff);
            font-family: 'Montserrat', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .vv-status-card {
            max-width: 480px;
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .vv-status-card img {
            width: 80px;
            margin-bottom: 1rem;
        }

        .vv-status-card h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .alert-success {
            color: #2e7d32;
            background: #dff0d8;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .vv-status-card .btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            background-color: var(--brand-primary, #5d3fd3);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: 0.3s;
        }

        .vv-status-card .btn:hover {
            background-color: #4527a0;
        }
    </style>
</head>

<body class="vv-login-status">
    <div class="vv-status-card">
        <div class="logo-container mb-3">
            <img src="../inc/assets/site-images/logo.png" alt="Velvet Vogue Logo" class="logo-icon" />
            <div class="logo-text">
                <span class="brand-name text-white">Velvet Vogue</span>
                <span class="brand-tagline text-white">Clothing</span>
            </div>
        </div>

        <h2>Logged Out</h2>
        <div class="alert alert-success">
            <?= $alert['msg'] ?>
        </div>

        <script>
            setTimeout(() => {
                window.location.href = "<?= $redirectURL ?>";
            }, 3500);
        </script>

        <div style="margin-top: 1.5rem;">
            <a href="<?= $redirectURL ?>" class="btn">Return to Home</a>
        </div>
    </div>
</body>

</html>