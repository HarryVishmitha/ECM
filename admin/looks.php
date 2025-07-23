<?php
require '../env.php';
require_once '../core/DB_conn.php';
session_start();
$page = 'Manage Looks';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$alert = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM looks WHERE id = $id");
    header("Location: looks.php?deleted=1");
    exit;
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_products'])) {
        $lookId = (int) $_POST['look_product_id'];
        $productIds = $_POST['products'] ?? [];

        // Clear old links
        mysqli_query($conn, "DELETE FROM look_product WHERE look_id = $lookId");

        // Insert new
        foreach ($productIds as $pid) {
            $pid = (int) $pid;
            mysqli_query($conn, "INSERT INTO look_product (look_id, product_id) VALUES ($lookId, $pid)");
        }

        $alert = "Products updated for the look!";
    } else {
        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $imagePath = null;

        if (!empty($_FILES['image']['name'])) {
            $filename = basename($_FILES['image']['name']);
            $imagePath = "../uploads/looks_" . time() . '_' . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        }

        if ($id) {
            // Update
            $query = "UPDATE looks SET title=?, description=?";
            $params = [$title, $description];
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
            $alert = "Look updated successfully!";
        } else {
            // Insert
            $stmt = mysqli_prepare($conn, "INSERT INTO looks (title, description, image) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $title, $description, $imagePath);
            mysqli_stmt_execute($stmt);
            $alert = "New look added!";
        }

        if (isset($_POST['save_products'])) {
            $lookId = (int) $_POST['look_product_id'];
            $productIds = $_POST['products'] ?? [];

            // Clear old links
            mysqli_query($conn, "DELETE FROM look_product WHERE look_id = $lookId");

            // Insert new
            foreach ($productIds as $pid) {
                $pid = (int) $pid;
                mysqli_query($conn, "INSERT INTO look_product (look_id, product_id) VALUES ($lookId, $pid)");
            }

            $alert = "Products updated for the look!";
        }
    }
}

// Fetch all looks
$looks = mysqli_query($conn, "SELECT * FROM looks ORDER BY created_at DESC");
$assigned = [];
if (isset($_POST['look_product_id'])) {
    $res = mysqli_query($conn, "SELECT product_id FROM look_product WHERE look_id = " . (int)$_POST['look_product_id']);
    while ($row = mysqli_fetch_assoc($res)) {
        $assigned[] = $row['product_id'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Looks – Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../inc/css/admin-stylesheet.css">
    <!-- <script src="../inc/js/admin-script.js" defer></script> -->
    <link rel="icon" href="../inc/assets/site-images/logo.png" type="image/x-icon">
    <script>
        function openModal(id = '', title = '', description = '') {
            document.getElementById('modalTitle').textContent = id ? 'Edit Look' : 'Add Look';
            document.getElementById('lookId').value = id;
            document.getElementById('lookTitle').value = title;
            document.getElementById('lookDesc').value = description;
            document.getElementById('lookModal').classList.add("show");
            const imageInput = document.getElementById('lookImage');
            if (id) {
                imageInput.removeAttribute('required'); // not required for edit
            } else {
                imageInput.setAttribute('required', 'required'); // required for new
            }
        }

        function closeModal() {
            document.getElementById('lookModal').classList.remove("show");
            document.getElementById('lookId').value = '';
            document.getElementById('lookTitle').value = '';
            document.getElementById('lookDesc').value = '';
        }

        function openProductModal(lookId) {
            document.getElementById('productModalTitle').textContent = "Assign Products to Look #" + lookId;
            document.getElementById('lookProductId').value = lookId;

            // Clear selection first
            const options = document.querySelectorAll('#productSelect option');
            options.forEach(opt => opt.selected = false);

            // Fetch existing product ids via AJAX (optional), or preload using PHP later
            document.getElementById('productModal').classList.add("show");
        }

        function closeProductModal() {
            document.getElementById('productModal').classList.remove("show");
        }
    </script>
</head>

<body>
    <?php include '../components/user_topnav.php'; ?>
    <div class="admin-dashboard">
        <?php include '../components/admin-sidebar.php'; ?>

        <main class="admin-main">
            <div class="dashboard-wrapper">
                <h1 class="admin-title">Manage Shop By Looks</h1>
                <p>You can add, edit, or delete shop by look entries here.</p>

                <?php if ($alert): ?>
                    <p class="alert"><?= $alert ?></p>
                <?php elseif (isset($_GET['deleted'])): ?>
                    <p class="alert alert-success">Look deleted successfully!</p>
                <?php endif; ?>

                <button class="btn" onclick="openModal()">+ Add Look</button>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Preview</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($look = mysqli_fetch_assoc($looks)): ?>
                                <tr>
                                    <td><?= $look['id'] ?></td>
                                    <td><img src="<?= $look['image'] ?>" width="60"></td>
                                    <td><?= htmlspecialchars($look['title']) ?></td>
                                    <td><?= htmlspecialchars(substr($look['description'], 0, 50)) ?>...</td>
                                    <td><?= date('d M Y', strtotime($look['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm" onclick="openModal('<?= $look['id'] ?>', '<?= htmlspecialchars(addslashes($look['title'])) ?>', `<?= htmlspecialchars(addslashes($look['description'])) ?>`)">Edit</button>
                                        <button class="btn btn-sm" onclick="openProductModal(<?= $look['id'] ?>)">Manage Products</button>

                                        <a href="?delete=<?= $look['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this look?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Look Modal -->
            <div class="modal" id="lookModal">
                <div class="modal-content">
                    <h3 id="modalTitle">Add Look</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="lookId">
                        <label>Title:</label>
                        <input type="text" name="title" id="lookTitle" required>
                        <label>Description:</label>
                        <textarea name="description" id="lookDesc" rows="1"></textarea>
                        <label>Image:</label>
                        <input type="file" name="image" accept="image/*" id="lookImage">
                        <button type="submit" class="btn">Save</button>
                        <button type="button" class="btn" onclick="closeModal()" style="background:#aaa;">Cancel</button>
                    </form>
                </div>
            </div>

            <!-- Product Modal -->
            <div class="modal" id="productModal">
                <div class="modal-content">
                    <h3 id="productModalTitle">Assign Products to Look</h3>
                    <form method="POST">
                        <input type="hidden" name="look_product_id" id="lookProductId">
                        <label>Select Products:</label>
                        <select name="products[]" multiple size="6" id="productSelect">
                            <?php
                            $products = mysqli_query($conn, "SELECT id, name FROM products WHERE status = 'active'");
                            $selected = in_array($product['id'], $assigned) ? 'selected' : '';
                            while ($product = mysqli_fetch_assoc($products)) {
                                echo "<option value='{$product['id']}' $selected>" . htmlspecialchars($product['name']) . "</option>";
                            }
                            ?>
                        </select>
                        <br><br>
                        <button type="submit" name="save_products" class="btn">Save</button>
                        <button type="button" class="btn" onclick="closeProductModal()">Cancel</button>
                    </form>
                </div>
            </div>


        </main>
    </div>
</body>

</html>