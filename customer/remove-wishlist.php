<?php
require_once '../core/DB_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$wishlistId = intval($_GET['id']);

$query = "DELETE FROM wishlist WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $wishlistId, $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: wishlist.php");
exit;
?>