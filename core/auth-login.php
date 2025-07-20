<?php
require_once __DIR__ . '/../env.php';
require_once 'DB_conn.php';
session_start();

$page = 'login-handler';
$alert = null;
$userRole = null;
$redirectURL = null;
$shouldRedirect = false;
$errorDetails = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            throw new Exception("Both email and password are required.");
        }

        $stmt = mysqli_prepare($conn, "SELECT id, name, password, role FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Database Error: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $id, $name, $hashed_password, $role);
            mysqli_stmt_fetch($stmt);

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;

                $userRole = $role;
                $shouldRedirect = true;

                if ($role === 'admin') {
                    $redirectURL = '../admin/dashboard.php';
                } else {
                    $redirectURL = '../user/dashboard.php';
                }

                $alert = ['type' => 'success', 'msg' => "Login successful! Redirecting to your $role dashboard..."];
            } else {
                throw new Exception("Incorrect password for the provided email.");
            }
        } else {
            throw new Exception("No user found with the given email.");
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    $alert = ['type' => 'error', 'msg' => "Login failed. Please review the error below."];
    $errorDetails = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login Status – Velvet Vogue</title>
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

        .alert-error {
            color: #c62828;
            background: #fbe9e7;
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

        .vv-error-details {
            font-size: 0.95rem;
            margin-top: 1rem;
            word-break: break-word;
            background: #f1f1f1;
            padding: 1rem;
            border-radius: 10px;
            color: #444;
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
        <h2>
            <?php echo $alert['type'] === 'success' ? 'Login Successful' : 'Login Failed'; ?>
        </h2>
        <div class="alert alert-<?php echo $alert['type']; ?>">
            <?php echo $alert['msg']; ?>
        </div>

        <?php if ($errorDetails): ?>
            <div class="vv-error-details">
                <strong>Error Details:</strong><br>
                <?php echo $errorDetails; ?>
            </div>
        <?php endif; ?>

        <?php if ($shouldRedirect): ?>
            <script>
                setTimeout(() => {
                    window.location.href = "<?php echo $redirectURL; ?>";
                }, 3500);
            </script>
        <?php else: ?>
            <div style="margin-top: 1.5rem;">
                <a href="../login.php" class="btn">Back to Login</a>
                <a href="../index.php" class="btn" style="background-color:#999;">Home Page</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>