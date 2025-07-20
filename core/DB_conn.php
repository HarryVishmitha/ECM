<?php
require_once __DIR__ . '/../env.php';


// Create a connection
$conn = mysqli_connect($DB_host, $DB_user, $DB_password, $DB_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// Set charset to UTF-8 for security and proper encoding
mysqli_set_charset($conn, "utf8");

// Optional: function to run queries safely
function runQuery($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo "Query Error: " . mysqli_error($conn);
        return false;
    }
    
    return $result;
}
?>
