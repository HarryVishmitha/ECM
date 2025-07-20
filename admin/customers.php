<?php

require '../env.php';
require_once '../core/DB_conn.php';
session_start();
$userName = $_SESSION['user_name'];

$page = 'customers';

$query = "
    SELECT 
        u.id, u.name, u.email, u.phone, u.gender, u.created_at,
        (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS total_orders
    FROM users u
    WHERE u.role = 'customer'
    ORDER BY u.created_at DESC
";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – <?php echo $name; ?></title>
    <link rel="stylesheet" href="../inc/css/admin-stylesheet.css">
    <script src="../inc/js/admin-script.js" defer></script>
    <link rel="icon" href="../inc/assets/site-images/logo.png" type="image/x-icon">
</head>

<body>
    <?php include '../components/user_topnav.php' ?>

    <div class="admin-dashboard">
        <?php include '../components/admin-sidebar.php'; ?>

        <main class="admin-main">
            <div class="dashboard-wrapper">

                <h1 class="admin-title">Registered Customers</h1>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Joined On</th>
                                <th>Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                    <td><span class="gender-badge <?= $row['gender'] ?>"><?= ucfirst($row['gender']) ?></span></td>
                                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                    <td><?= $row['total_orders'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>
</body>

</html>