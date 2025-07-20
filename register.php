<?php
// index.php
require 'env.php';
require_once 'core/DB_conn.php';
$page = 'register';

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

$alert = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = trim($_POST['gender']);
    $password = $_POST['password'];
    $confirm_pw = $_POST['confirm_password'];
    $role = 'customer';

    // Field validation
    if (empty($name)) {
        $errors['name'] = 'Full name is required.';
    }

    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^\+?\d{10,15}$/', $phone)) {
        $errors['phone'] = 'Invalid phone number format.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }

    if (empty($confirm_pw)) {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_pw) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // If no errors, proceed to DB check
    if (empty($errors)) {
        $checkEmailQuery = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $checkEmailQuery);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $alert = ['type' => 'error', 'msg' => 'Email is already registered.'];
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $regUserQuery = "INSERT INTO users (name, email, phone, gender, password, role) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $regUserQuery);
            mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $phone, $gender, $hashed_password, $role);

            if (mysqli_stmt_execute($stmt)) {
                $alert = ['type' => 'success', 'msg' => 'Registration successful! You will redirect to login page in a second.'];
            } else {
                $alert = ['type' => 'error', 'msg' => 'Something went wrong. Please try again.'];
            }
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – <?php echo $name; ?></title>
    <link rel="stylesheet" href="inc/css/stylesheet.css">
    <script src="inc/js/script.js" defer></script>
    <link rel="icon" href="inc/assets/site-images/logo.png" type="image/x-icon">
</head>

<body class="vv-register-body">
    <div class="vv-register-wrapper">
        <div class="vv-register-card">
            <div class="vv-register-header">
                <img src="inc/assets/site-images/logo.png" alt="Velvet Vogue" class="vv-logo">
                <h2>Create Your Account</h2>
                <p>Join Velvet Vogue and express your style</p>
            </div>

            <?php if ($alert) : ?>
                <div class="alert alert-<?php echo $alert['type']; ?>">
                    <div class="alert-text"><?php echo $alert['msg']; ?></div>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="vv-register-form" onsubmit="return validateRegisterForm()">
                <div class="vv-form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="vv-name" required placeholder="John Doe" />
                    <?php if (!empty($errors['name'])): ?>
                        <div class="input-error"><?php echo $errors['name']; ?></div>
                    <?php endif; ?>

                </div>

                <div class="vv-form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="vv-email" required placeholder="example@mail.com" />
                    <?php if (!empty($errors['email'])): ?>
                        <div class="input-error"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>

                </div>

                <div class="vv-form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" name="phone" id="vv-phone" placeholder="+94 77 123 4567" />
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="input-error"><?php echo $errors['phone']; ?></div>
                    <?php endif; ?>

                </div>

                <div class="vv-form-group">
                    <label for="gender">Gender</label>
                    <select name="gender" id="vv-gender">
                        <option value="">Select...</option>
                        <option value="female">Female</option>
                        <option value="male">Male</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="vv-form-group vv-password-wrap">
                    <label for="password">Password</label>
                    <div class="vv-password-field">
                        <input type="password" name="password" id="vv-password" required placeholder="••••••••" />
                        <span class="vv-toggle-password" onclick="togglePassword(this, 'vv-password')">👁</span>

                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <div class="input-error"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>

                </div>

                <div class="vv-form-group vv-password-wrap">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="vv-password-field">
                        <input type="password" name="confirm_password" id="vv-confirm-password" required placeholder="••••••••" />
                        <span class="vv-toggle-password" onclick="togglePassword(this, 'vv-confirm-password')">👁</span>
                    </div>
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <div class="input-error"><?php echo $errors['confirm_password']; ?></div>
                    <?php endif; ?>

                </div>

                <div class="vv-terms">
                    <label for="vv-terms" class="vv-terms-label">
                        <input type="checkbox" id="vv-terms" required />
                        <span>I agree to the <a href="#">terms & conditions</a></span>
                    </label>
                </div>


                <button type="submit" class="vv-register-btn">Register</button>

                <p class="vv-register-footer">Already have an account? <a href="login.php">Login</a></p>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(el, targetId) {
            const input = document.getElementById(targetId);
            const isVisible = input.type === "text";
            input.type = isVisible ? "password" : "text";
            el.textContent = isVisible ? "👁" : "🙈";
        }

        function validateRegisterForm() {
            const pass = document.getElementById("vv-password").value;
            const confirm = document.getElementById("vv-confirm-password").value;
            if (pass !== confirm) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }
    </script>

    <?php if ($alert && $alert['type'] === 'success') : ?>
        <script>
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 5000); // Redirect after 5 seconds
        </script>
    <?php endif; ?>

</body>

</html>