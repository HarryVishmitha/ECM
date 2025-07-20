<?php
session_start();
require_once '../core/DB_conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$page = 'edit-profile';
$success = null;
$error = null;

$stmt = mysqli_prepare($conn, "SELECT name, email, phone, gender FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $name, $email, $phone, $gender);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name']);
    $new_phone = trim($_POST['phone']);
    $new_gender = $_POST['gender'];
    $new_password = $_POST['password'];

    if (empty($new_name) || empty($new_phone)) {
        $error = "Name and phone are required.";
    } else {
        if (!empty($new_password)) {
            $hashed_pw = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET name=?, phone=?, gender=?, password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssi", $new_name, $new_phone, $new_gender, $hashed_pw, $user_id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET name=?, phone=?, gender=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssi", $new_name, $new_phone, $new_gender, $user_id);
        }

        if (mysqli_stmt_execute($stmt)) {
            $success = "Profile updated successfully!";
            $_SESSION['user_name'] = $new_name;

            echo "<script>
                        setTimeout(function() {
                            window.location.href = '../{$user_role}/dashboard.php';
                        }, 500);
                    </script>";
        } else {
            $error = "Something went wrong. Try again.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Edit Profile – Velvet Vogue</title>
    <link rel="stylesheet" href="../inc/css/admin-stylesheet.css">
    <link rel="stylesheet" href="../inc/css/stylesheet.css">
    <script src="../inc/js/script.js"></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(to right, #5d3fd3, #a18cd1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .profile-form-container {
            background: #fff;
            padding: 35px 30px;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }

        .profile-form-container h2 {
            margin-bottom: 25px;
            font-size: 24px;
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            color: #444;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }

        input:focus,
        select:focus {
            border-color: #5d3fd3;
            box-shadow: 0 0 0 3px rgba(93, 63, 211, 0.15);
            outline: none;
        }

        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-weight: 500;
            font-size: 14px;
            text-align: center;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .alert-error {
            background: #fbe9e7;
            color: #c62828;
        }

        .show-password {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            font-size: 13px;
            color: #555;
        }

        .btn {
            background: #5d3fd3;
            color: white;
            padding: 12px 20px;
            border: none;
            font-size: 15px;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #4527a0;
        }
    </style>
</head>

<body>
    <div class="profile-form-container">
        <h2>Edit Your <?= ucfirst($user_role) ?> Profile</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div style="text-align:center; margin-bottom: 20px;">
            <a href="../<?= $user_role ?>/dashboard.php" class="btn" style="background:#aaa; width:auto;">← Back to Dashboard</a>
        </div>


        <form method="POST">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" value="<?= htmlspecialchars($email) ?>" readonly>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
            </div>

            <div class="form-group">
                <label for="gender">Gender:</label>
                <select name="gender" required>
                    <option value="male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
                    <option value="other" <?= $gender === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="vv-form-group vv-password-wrap">
                <label for="password">New Password: <small>(leave blank to keep current)</small></label>
                <div class="vv-password-field">
                    <input type="password" name="password" id="pwField" />
                    <span class="vv-toggle-password" onclick="togglePassword(this)" title="Show Password">👁</span>
                </div>
            </div>

            <button type="submit" class="btn">Save Changes</button>
        </form>
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