<?php

require '../env.php';
require_once '../core/DB_conn.php';
$page = 'Products - Admin';
session_start();
$userName = $_SESSION['user_name'];

$alert = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $status = $_POST['status'];
    $id = $_POST['id'] ?? null;
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $filename = basename($_FILES['image']['name']);
        $target = "../uploads/categories_" . time() . '_' . $filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $imagePath = $target;
    }

    if ($id) {
        // UPDATE
        $query = "UPDATE categories SET name=?, status=?";
        $params = [$name, $status];
        if ($imagePath) {
            $query .= ", image=?";
            $params[] = $imagePath;
        }
        $query .= " WHERE id=?";
        $params[] = $id;

        $stmt = mysqli_prepare($conn, $query);
        $types = str_repeat("s", count($params) - 1) . "i";
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $alert = "Category updated successfully!";
    } else {
        // INSERT
        $stmt = mysqli_prepare($conn, "INSERT INTO categories (name, image, status) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $name, $imagePath, $status);
        mysqli_stmt_execute($stmt);
        $alert = "Category added successfully!";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
    header("Location: categories.php");
    exit;
}

// Toggle Status
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    mysqli_query($conn, "UPDATE categories SET status = IF(status = 'active', 'inactive', 'active') WHERE id = $id");
    header("Location: categories.php");
    exit;
}

// Fetch Categories
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY created_at DESC");
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
                <h1 class="admin-title">Manage Categories</h1>
                <p>Manage categories here. You can add, edit, deletea and simply change the status by clicking on the status of the category.</p>

                <?php if ($alert): ?>
                    <p class="alert"><?= $alert ?></p>
                <?php endif; ?>

                <button class="btn" onclick="openModal()">+ Add Category</button>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                <tr>
                                    <td><?= $cat['id'] ?></td>
                                    <td><?= htmlspecialchars($cat['name']) ?></td>
                                    <td>
                                        <?php if ($cat['image']): ?>
                                            <img src="<?= $cat['image'] ?>" class="category-img" />
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?toggle=<?= $cat['id'] ?>" class="btn btn-sm <?= $cat['status'] === 'active' ? 'btn-success' : 'btn-danger' ?>">
                                            <?= ucfirst($cat['status']) ?>
                                        </a>

                                    </td>
                                    <td>
                                        <button class="btn btn-sm" onclick="openModal(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name']) ?>', '<?= $cat['status'] ?>')">Edit</button>
                                        <a href="?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Form -->
            <div class="modal" id="categoryModal">
                <div class="modal-content">
                    <h3 id="modalTitle">Add Category</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="catId">
                        <label>Name:</label>
                        <input type="text" name="name" id="catName" required>
                        <label>Status:</label>
                        <select name="status" id="catStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <label>Image:</label>
                        <input type="file" name="image">
                        <br><br>
                        <button type="submit" class="btn">Save</button>
                        <button type="button" class="btn" onclick="closeModal()" style="background:#aaa;">Cancel</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>