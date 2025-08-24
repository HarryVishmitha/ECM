<?php
// reset-password.php
require 'env.php';
require_once 'core/DB_conn.php';
session_start();

$page  = 'reset-password';
$alert = ['type' => null, 'msg' => null];

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $newpw    = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Basic validations
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alert = ['type' => 'error', 'msg' => 'Please enter a valid email address.'];
    } elseif (strlen($newpw) < 8) {
        $alert = ['type' => 'error', 'msg' => 'Password must be at least 8 characters long.'];
    } elseif ($newpw !== $confirm) {
        $alert = ['type' => 'error', 'msg' => 'Password and confirmation do not match.'];
    } else {
        // Look up user by email
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $uid);
            if (mysqli_stmt_fetch($stmt)) {
                mysqli_stmt_close($stmt);

                // Update password
                $hash = password_hash($newpw, PASSWORD_DEFAULT);
                $up = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                if ($up) {
                    mysqli_stmt_bind_param($up, "si", $hash, $uid);
                    if (mysqli_stmt_execute($up)) {
                        $alert = ['type' => 'success', 'msg' => 'Your password has been reset. You can now log in.'];
                    } else {
                        $alert = ['type' => 'error', 'msg' => 'Failed to update password. Please try again.'];
                    }
                    mysqli_stmt_close($up);
                } else {
                    $alert = ['type' => 'error', 'msg' => 'Unexpected error preparing update.'];
                }
            } else {
                // Do not reveal whether the email exists; keep message generic
                mysqli_stmt_close($stmt);
                $alert = ['type' => 'error', 'msg' => 'Could not reset password for this account. Please check details and try again.'];
            }
        } else {
            $alert = ['type' => 'error', 'msg' => 'Unexpected error preparing lookup.'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password – <?= htmlspecialchars($name ?? 'Velvet Vogue') ?></title>
    <link rel="stylesheet" href="inc/css/stylesheet.css" />
    <link rel="icon" href="inc/assets/site-images/logo.png" type="image/x-icon">
    <style>
        .auth-wrapper {
            max-width: 480px;
            margin: 60px auto;
            padding: 24px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
        }

        .auth-title {
            margin: 0 0 8px;
            font-size: 1.6rem;
        }

        .auth-sub {
            margin: 0 0 18px;
            color: #555;
        }

        .form-row {
            margin-bottom: 14px;
        }

        .form-row label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .form-row input[type="email"],
        .form-row input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        .btn.primary-btn {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            background: #5d3fd3;
            color: #fff;
            font-weight: 700;
            text-decoration: none;
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-weight: 600;
        }

        .alert-success {
            background: #e6f6ec;
            color: #146c2e;
            border: 1px solid #b7e3c6;
        }

        .alert-error {
            background: #fdecea;
            color: #b42318;
            border: 1px solid #f5c2c0;
        }

        .links {
            margin-top: 12px;
        }

        .links a {
            color: #5d3fd3;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .hint {
            color: #666;
            font-size: .9rem;
            margin-top: 6px;
        }
    </style>
</head>

<body>
    <?php include 'components/topnav.php'; ?>

    <section class="product-header-section">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a> &gt; <span>Reset Password</span>
            </div>
            <h1 class="product-page-title">Reset your password</h1>
            <p class="product-page-subtitle">Enter your account email and a new password.</p>
        </div>
    </section>

    <div class="auth-wrapper">
        <h2 class="auth-title">Create a new password</h2>

        <?php if ($alert['type']): ?>
            <div class="alert <?= $alert['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= htmlspecialchars($alert['msg']) ?>
            </div>
        <?php endif; ?>

        <?php if ($alert['type'] !== 'success'): ?>
            <form method="post" action="forgot-password.php" autocomplete="off">
                <div class="form-row">
                    <label for="email">Account email</label>
                    <input type="email" id="email" name="email" required />
                </div>

                <div class="form-row">
                    <label for="password">New password</label>
                    <input type="password" id="password" name="password" minlength="8" required />
                    <div class="hint">Use at least 8 characters. Mix letters, numbers, and symbols for strength.</div>
                </div>

                <div class="form-row">
                    <label for="confirm_password">Confirm new password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" required />
                </div>

                <button type="submit" class="btn primary-btn">Update password</button>
            </form>
        <?php endif; ?>

        <div class="links">
            <p class="muted"><a href="login.php">Back to login</a></p>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>

</html>